<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CashBankBookExport
{
    protected $book;
    protected $fromdate;
    protected $todate;
    protected $propertyid;
    protected $companyName;
    protected $subcodes;

    public function __construct($book, $fromdate, $todate, $propertyid, $companyName, $subcodes = null)
    {
        $this->book         = $book;
        $this->fromdate     = $fromdate;
        $this->todate       = $todate;
        $this->propertyid   = $propertyid;
        $this->companyName  = $companyName;
        $this->subcodes     = $subcodes ? (is_array($subcodes) ? $subcodes : explode(',', $subcodes)) : null;
    }

    public function getData()
    {
        $book = $this->book;
        $fromdate = $this->fromdate;
        $todate = $this->todate;
        $propertyid = $this->propertyid;
        $subcodes = $this->subcodes;

        $base = function () use ($book, $propertyid, $fromdate, $subcodes) {
            $q = DB::table('ledger as l')
                // LEFT join for legacy VIEWLEDGER parity (BUG-QA-010): rows without a
                // subgroup must not silently disappear from the export.
                ->leftJoin('subgroup as s', 's.sub_code', '=', 'l.subcode')
                ->leftJoin('acgroup as a', function ($join) {
                    // group_code is NOT globally unique (shared across properties) — scope to the
                    // property's own acgroup row or the join multiplies rows (BUG-044).
                    $join->on('s.group_code', '=', 'a.group_code')
                        ->on('a.propertyid', '=', 'l.propertyid');
                })
                ->where('l.propertyid', $propertyid)
                ->where('a.nature', $book)
                ->where(function ($q) {
                    $q->whereNull('l.delflag')->orWhere('l.delflag', '!=', 'Y');
                });

            if (! empty($subcodes) && is_array($subcodes)) {
                $q->whereIn('l.subcode', $subcodes);
            }

            return $q;
        };

        $openings = $base()
            ->where('l.vdate', '<', $fromdate)
            ->select('s.sub_code', 's.name', 'a.group_name')
            ->selectRaw('SUM(l.amtdr) AS opening_dr')
            ->selectRaw('SUM(l.amtcr) AS opening_cr')
            ->groupBy('s.sub_code', 's.name', 'a.group_name')
            ->get()
            ->keyBy('sub_code');

        $txns = $base()
            ->whereBetween('l.vdate', [$fromdate, $todate])
            ->select(
                'l.subcode',
                's.name',
                'a.group_name',
                'l.vdate',
                'l.docid',
                'l.vsno',
                'l.vtype',
                'l.vno',
                'l.vprefix',
                'l.narration',
                'l.contrasub',
                'l.chqno',
                'l.chqdate',
                'l.amtdr',
                'l.amtcr'
            )
            ->orderBy('s.name')
            ->orderBy('l.vdate')
            ->orderBy('l.docid')
            ->orderBy('l.vsno')
            ->get();

        $accounts = [];
        foreach ($txns as $t) {
            $code = $t->subcode;
            if (!isset($accounts[$code])) {
                $op = $openings->get($code);
                $openingDr = (float) ($op->opening_dr ?? 0);
                $openingCr = (float) ($op->opening_cr ?? 0);
                $accounts[$code] = [
                    'sub_code' => $code,
                    'name' => $t->name ?? '',
                    'group_name' => $t->group_name ?? '',
                    'opening_dr' => $openingDr,
                    'opening_cr' => $openingCr,
                    'opening_balance' => $openingDr - $openingCr,
                    'transactions' => [],
                ];
            }
            $accounts[$code]['transactions'][] = [
                'vdate' => $t->vdate,
                'docid' => $t->docid,
                'vsno' => $t->vsno,
                'vtype' => $t->vtype,
                'vno' => $t->vno,
                'vprefix' => $t->vprefix,
                'narration' => $t->narration ?? '',
                'contrasub' => $t->contrasub ?? '',
                'chqno' => $t->chqno ?? '',
                'chqdate' => $t->chqdate ?? '',
                'amtdr' => (float) ($t->amtdr ?? 0),
                'amtcr' => (float) ($t->amtcr ?? 0),
            ];
        }

        $accounts = array_values($accounts);
        usort($accounts, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        foreach ($accounts as &$acc) {
            $running = $acc['opening_balance'];
            foreach ($acc['transactions'] as &$tx) {
                $running += $tx['amtdr'] - $tx['amtcr'];
                $tx['running_balance'] = $running;
            }
            unset($tx);
            $acc['closing_balance'] = $running;
            $acc['total_dr'] = array_sum(array_column($acc['transactions'], 'amtdr'));
            $acc['total_cr'] = array_sum(array_column($acc['transactions'], 'amtcr'));
        }
        unset($acc);

        return $accounts;
    }

    public function download()
    {
        $data = $this->getData();
        $bookLabel = $this->book === 'Cash' ? 'Cash Book' : 'Bank Book';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($bookLabel, 0, 31));

        // ── Row 1: Company Name ──────────────────────────────────────
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', $this->companyName);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Row 2: Report Title ──────────────────────────────────────
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', $bookLabel . ' (' . date('d-m-Y', strtotime($this->fromdate)) . ' To ' . date('d-m-Y', strtotime($this->todate)) . ')');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Header ───────────────────────────────────────────────────
        $headers = ['A/C Name', 'Date', 'Doc ID', 'Narration', 'Contra', 'Debit', 'Credit', 'Balance'];
        $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        foreach ($headers as $i => $h) {
            $cell = $colLetters[$i] . '4';
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['bold' => true],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }

        // ── Data ─────────────────────────────────────────────────────
        $row = 5;
        $grandDr = 0;
        $grandCr = 0;

        foreach ($data as $acc) {
            // Account header row
            $sheet->mergeCells("A{$row}:H{$row}");
            $sheet->setCellValue("A{$row}", $acc['name'] . '  [' . ($acc['group_name'] ?? '') . ']');
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']],
            ]);
            $row++;

            // Opening balance row
            $sheet->setCellValue("A{$row}", 'Opening Balance');
            $sheet->setCellValue("F{$row}", $acc['opening_dr'] > 0 ? $acc['opening_dr'] : '');
            $sheet->setCellValue("G{$row}", $acc['opening_cr'] > 0 ? $acc['opening_cr'] : '');
            $sheet->setCellValue("H{$row}", $acc['opening_balance'] != 0 ? $acc['opening_balance'] : '');
            $sheet->getStyle("A{$row}:H{$row}")->getFont()->setItalic(true);
            $row++;

            foreach ($acc['transactions'] as $tx) {
                $sheet->setCellValue("A{$row}", '');
                $sheet->setCellValue("B{$row}", date('d-m-Y', strtotime($tx['vdate'])));
                $sheet->setCellValue("C{$row}", $tx['docid']);
                $sheet->setCellValue("D{$row}", $tx['narration']);
                $sheet->setCellValue("E{$row}", $tx['contrasub']);
                $sheet->setCellValue("F{$row}", $tx['amtdr'] > 0 ? $tx['amtdr'] : '');
                $sheet->setCellValue("G{$row}", $tx['amtcr'] > 0 ? $tx['amtcr'] : '');
                $sheet->setCellValue("H{$row}", $tx['running_balance'] != 0 ? $tx['running_balance'] : '');
                $row++;
            }

            // Account totals row
            $sheet->setCellValue("A{$row}", 'Sub Total');
            $sheet->setCellValue("F{$row}", $acc['total_dr']);
            $sheet->setCellValue("G{$row}", $acc['total_cr']);
            $sheet->setCellValue("H{$row}", $acc['closing_balance']);
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'borders' => ['top' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $row++;

            $grandDr += $acc['total_dr'];
            $grandCr += $acc['total_cr'];
        }

        // ── Grand Total ──────────────────────────────────────────────
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL');
        $sheet->setCellValue("F{$row}", $grandDr);
        $sheet->setCellValue("G{$row}", $grandCr);
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // ── Column widths ────────────────────────────────────────────
        $widths = [30, 12, 18, 45, 18, 14, 14, 14];
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimension($colLetters[$i])->setWidth($w);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = strtolower($this->book) . '-book-' . date('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
