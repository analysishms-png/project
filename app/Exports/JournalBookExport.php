<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

class JournalBookExport
{
    protected $fromdate;
    protected $todate;
    protected $propertyid;
    protected $companyName;
    protected $vtype;

    public function __construct($fromdate, $todate, $propertyid, $companyName, $vtype = 'JV')
    {
        $this->fromdate    = $fromdate;
        $this->todate      = $todate;
        $this->propertyid  = $propertyid;
        $this->companyName = $companyName;
        $this->vtype       = $vtype;
    }

    public function getData()
    {
        $query = DB::table('ledger as l')
            // LEFT join for legacy VIEWLEDGER parity (BUG-QA-010): postings with an
            // empty/missing subcode must not be silently dropped from the export.
            ->leftJoin('subgroup as s', 's.sub_code', '=', 'l.subcode')
            ->leftJoin('acgroup as a', function ($join) {
                // group_code is NOT globally unique (shared across properties) — scope to the
                // property's own acgroup row or the join multiplies rows (BUG-044).
                $join->on('s.group_code', '=', 'a.group_code')
                    ->on('a.propertyid', '=', 'l.propertyid');
            })
            ->where('l.propertyid', $this->propertyid)
            ->whereBetween('l.vdate', [$this->fromdate, $this->todate])
            ->where(function ($q) {
                $q->whereNull('l.delflag')->orWhere('l.delflag', '!=', 'Y');
            });

        if (! empty($this->vtype)) {
            $query->where('l.vtype', $this->vtype);
        }

        $rows = $query
            ->select(
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
                'l.amtcr',
                'l.subcode',
                's.name as account_name',
                'a.group_name'
            )
            ->orderBy('l.vdate')
            ->orderBy('l.docid')
            ->orderBy('l.vsno')
            ->get();

        $totalDr = 0;
        $totalCr = 0;
        foreach ($rows as $r) {
            $totalDr += (float) $r->amtdr;
            $totalCr += (float) $r->amtcr;
        }

        return [
            'rows' => $rows,
            'total_dr' => $totalDr,
            'total_cr' => $totalCr,
        ];
    }

    public function download()
    {
        $data = $this->getData();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Journal Book');

        // ── Row 1: Company Name ──────────────────────────────────────
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', $this->companyName);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Row 2: Report Title ──────────────────────────────────────
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Journal Book (' . date('d-m-Y', strtotime($this->fromdate)) . ' To ' . date('d-m-Y', strtotime($this->todate)) . ')' . ($this->vtype ? ' — Vch Type: ' . $this->vtype : ''));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Header ───────────────────────────────────────────────────
        $headers = ['Date', 'Vch Type', 'Vch No', 'Doc ID', 'A/C Name', 'Narration', 'Debit', 'Credit'];
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
        foreach ($data['rows'] as $r) {
            $sheet->setCellValue("A{$row}", date('d-m-Y', strtotime($r->vdate)));
            $sheet->setCellValue("B{$row}", $r->vtype);
            $sheet->setCellValue("C{$row}", trim(($r->vprefix ?? '') . ' ' . ($r->vno ?? '')));
            $sheet->setCellValue("D{$row}", $r->docid);
            $sheet->setCellValue("E{$row}", $r->account_name ?? $r->subcode);
            $sheet->setCellValue("F{$row}", $r->narration);
            $sheet->setCellValue("G{$row}", $r->amtdr > 0 ? $r->amtdr : '');
            $sheet->setCellValue("H{$row}", $r->amtcr > 0 ? $r->amtcr : '');
            $row++;
        }

        // ── Grand Total ──────────────────────────────────────────────
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL');
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("G{$row}", $data['total_dr']);
        $sheet->setCellValue("H{$row}", $data['total_cr']);
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // ── Column widths ────────────────────────────────────────────
        $widths = [12, 10, 12, 20, 25, 45, 14, 14];
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimension($colLetters[$i])->setWidth($w);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'journal-book-' . date('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
