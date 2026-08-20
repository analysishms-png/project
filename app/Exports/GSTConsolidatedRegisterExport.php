<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GSTConsolidatedRegisterExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected int $propertyid;
    protected string $companyName;
    protected string $fromdate;
    protected string $todate;
    protected array $data;
    protected array $summary;
    protected array $grand;

    public function __construct(
        int $propertyid,
        string $companyName,
        string $fromdate,
        string $todate,
        array $data,
        array $summary,
        array $grand
    ) {
        $this->propertyid = $propertyid;
        $this->companyName = $companyName;
        $this->fromdate    = $fromdate;
        $this->todate      = $todate;
        $this->data        = $data;
        $this->summary     = $summary;
        $this->grand       = $grand;
    }

    public function collection()
    {
        $rows = collect();

        // Detail rows
        foreach ($this->data as $row) {
            $rows->push([
                'detail',
                $row['Source'],
                $row['BillNo'],
                $row['VDate'],
                $row['GSTIN'],
                $row['PartyName'],
                $row['BaseValue'],
                $row['TaxPer'] . '%',
                $row['CGSTAmt'],
                $row['SGSTAmt'],
                $row['IGSTAmt'],
                $row['TotalTax'],
                $row['NetAmt'],
            ]);
        }

        // Blank separator
        $rows->push(array_fill(0, 13, ''));

        // Summary header
        $rows->push(['SUMMARY BY GSTIN + RATE', '', '', '', '', '', '', '', '', '', '', '', '']);
        $rows->push(['GSTIN', 'Rate %', 'Base Value', 'CGST', 'SGST', 'IGST', 'Total Tax', 'Bills', '', '', '', '', '']);

        foreach ($this->summary as $s) {
            $rows->push([
                $s['GSTIN'],
                $s['TaxPer'] . '%',
                $s['BaseValue'],
                $s['CGSTAmt'],
                $s['SGSTAmt'],
                $s['IGSTAmt'],
                $s['TotalTax'],
                $s['BillCount'],
                '', '', '', '', '',
            ]);
        }

        // Grand total
        $rows->push([
            'GRAND TOTAL', '',
            $this->grand['BaseValue'],
            $this->grand['CGSTAmt'],
            $this->grand['SGSTAmt'],
            $this->grand['IGSTAmt'],
            $this->grand['TotalTax'],
            '', '', '', '', '', '',
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'GST Consolidated Register',
            $this->companyName . ' | Period: ' . $this->fromdate . ' to ' . $this->todate,
            '',
            'Source', 'Bill No', 'Date', 'GSTIN', 'Party',
            'Taxable (₹)', 'Rate %', 'CGST (₹)', 'SGST (₹)', 'IGST (₹)', 'Total Tax (₹)', 'Net Amount (₹)',
        ];
    }

    public function map($row): array
    {
        return $row;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 10]],
            4 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '4472C4']], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}
