<?php
namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class OccupancyForecastExport
{
    protected $propertyid;
    protected $companyName;
    protected $fromdate;
    protected $todate;

    public function __construct($propertyid, $companyName, $fromdate, $todate)
    {
        $this->propertyid  = $propertyid;
        $this->companyName = $companyName;
        $this->fromdate    = $fromdate;
        $this->todate      = $todate;
    }

    /**
     * Same query logic as Reporting@fetchoccupancyforecast.
     * Returns ['rows' => [...], 'totals' => [...]].
     */
    public function getData(): array
    {
        $pid = $this->propertyid;

        // Total rooms (fixed for the property)
        $totalRooms = DB::table('room_mast')
            ->where('propertyid', $pid)
            ->where('type', 'RO')
            ->where('inclcount', 'Y')
            ->count();

        // Build date range day by day
        $start = Carbon::parse($this->fromdate);
        $end   = Carbon::parse($this->todate);
        $rows  = [];

        $grandTotalRooms  = 0;
        $grandArrival     = 0;
        $grandDeparture   = 0;
        $grandStayOver    = 0;
        $grandOccupied    = 0;
        $grandPax         = 0;
        $grandRevenue     = 0.0;
        $grandAvailable   = 0;

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateStr = $date->toDateString();   // Y-m-d

            // Expected Arrival: bookings arriving on this date (not cancelled, not contra)
            $expectedArrival = DB::table('grpbookingdetails')
                ->where('Property_ID', $pid)
                ->where('ArrDate', $dateStr)
                ->where('Cancel', 'N')
                ->where('ContraDocId', '')
                ->count();

            // Expected Departure: in-house guests whose departure date is this date & not yet checked out
            $expectedDeparture = DB::table('roomocc')
                ->where('propertyid', $pid)
                ->where('DepDate', $dateStr)
                ->whereNull('chkoutdate')
                ->whereNull('type')
                ->count();

            // Stay Over: guests who checked in on or before this date and depart after this date
            $stayOver = DB::table('roomocc')
                ->where('propertyid', $pid)
                ->where('DepDate', '>', $dateStr)
                ->where('chkindate', '<=', $dateStr)
                ->whereNull('type')
                ->count();

            // Occupied Rooms: all current in-house rooms (no checkout)
            $occupiedRooms = DB::table('roomocc')
                ->where('propertyid', $pid)
                ->whereNull('type')
                ->whereNull('chkoutdate')
                ->count();

            // Total Pax: sum of adults for stay-overs on this date
            $totalPax = (int) DB::table('roomocc')
                ->where('propertyid', $pid)
                ->where('DepDate', '>', $dateStr)
                ->where('chkindate', '<=', $dateStr)
                ->whereNull('type')
                ->sum('Adult');

            // Total Revenue: sum of charges posted on this date
            $totalRevenue = (float) DB::table('paycharge')
                ->where('propertyid', $pid)
                ->where('vdate', $dateStr)
                ->sum('amtdr');

            // Derived metrics
            $availableRooms = $totalRooms - $occupiedRooms;
            $arr            = $occupiedRooms > 0 ? round($totalRevenue / $occupiedRooms, 2) : 0;
            $revpar         = $totalRooms > 0     ? round($totalRevenue / $totalRooms, 2)    : 0;

            $rows[] = [
                'date'               => $date->format('l, F j, Y'),   // e.g. "Thursday, November 21, 2024"
                'date_raw'           => $dateStr,
                'total_rooms'        => $totalRooms,
                'expected_arrival'   => $expectedArrival,
                'expected_departure' => $expectedDeparture,
                'stay_over'          => $stayOver,
                'occupied_rooms'     => $occupiedRooms,
                'total_pax'          => $totalPax,
                'available_rooms'    => $availableRooms < 0 ? 0 : $availableRooms,
                'total_revenue'      => $totalRevenue,
                'arr'                => $arr,
                'revpar'             => $revpar,
            ];

            // Grand totals
            $grandTotalRooms  += $totalRooms;
            $grandArrival     += $expectedArrival;
            $grandDeparture   += $expectedDeparture;
            $grandStayOver    += $stayOver;
            $grandOccupied    += $occupiedRooms;
            $grandPax         += $totalPax;
            $grandRevenue     += $totalRevenue;
            $grandAvailable   += ($availableRooms < 0 ? 0 : $availableRooms);
        }

        $grandArr    = $grandOccupied > 0 ? round($grandRevenue / $grandOccupied, 3) : 0;
        $grandRevpar = $grandTotalRooms > 0 ? round($grandRevenue / $grandTotalRooms, 3) : 0;

        return [
            'rows'   => $rows,
            'totals' => [
                'total_rooms'        => $grandTotalRooms,
                'expected_arrival'   => $grandArrival,
                'expected_departure' => $grandDeparture,
                'stay_over'          => $grandStayOver,
                'occupied_rooms'     => number_format($grandOccupied, 3),
                'total_pax'          => number_format($grandPax, 3),
                'available_rooms'    => $grandAvailable,
                'total_revenue'      => number_format($grandRevenue, 3),
                'arr'                => number_format($grandArr, 3),
                'revpar'             => number_format($grandRevpar, 3),
            ],
        ];
    }

    public function download()
    {
        $data   = $this->getData();
        $rows   = $data['rows'];
        $totals = $data['totals'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Occupancy Forecast');

        // Row 1: Company name (merged A1:K1)
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', $this->companyName);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Row 2: Report title (merged)
        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'Occupancy Forecast Report Day Wise');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Row 3: Date range (merged)
        $sheet->mergeCells('A3:K3');
        $sheet->setCellValue(
            'A3',
            'From: ' . Carbon::parse($this->fromdate)->format('d-m-Y') .
            '  To: '   . Carbon::parse($this->todate)->format('d-m-Y')
        );
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Row 5: Headers
        $headers = [
            'Date', 'Total Room', 'Expected Arrival', 'Expected Departure',
            'Stay Over', 'Occupied Rooms', 'Total Pax', 'Available Room',
            'Total Revenue', 'ARR', 'RevPAR',
        ];
        $headerRow = 5;
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(65 + $i) . $headerRow, $h);
        }
        $sheet->getStyle('A' . $headerRow . ':K' . $headerRow)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Data rows
        $numberCols = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
        $currentRow = $headerRow + 1;
        foreach ($rows as $i => $r) {
            $sheet->setCellValue('A' . $currentRow, $r['date']);
            $sheet->setCellValue('B' . $currentRow, $r['total_rooms']);
            $sheet->setCellValue('C' . $currentRow, $r['expected_arrival']);
            $sheet->setCellValue('D' . $currentRow, $r['expected_departure']);
            $sheet->setCellValue('E' . $currentRow, $r['stay_over']);
            $sheet->setCellValue('F' . $currentRow, $r['occupied_rooms']);
            $sheet->setCellValue('G' . $currentRow, $r['total_pax']);
            $sheet->setCellValue('H' . $currentRow, $r['available_rooms']);
            $sheet->setCellValue('I' . $currentRow, $r['total_revenue']);
            $sheet->setCellValue('J' . $currentRow, $r['arr']);
            $sheet->setCellValue('K' . $currentRow, $r['revpar']);

            $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Alternating light fill
            if ($i % 2 == 1) {
                $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
            }

            // Date left-aligned, numbers right-aligned
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            foreach ($numberCols as $col) {
                $sheet->getStyle($col . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
            $currentRow++;
        }

        // Grand totals row (bold, light grey background)
        $sheet->setCellValue('A' . $currentRow, 'Grand Total');
        $sheet->setCellValue('B' . $currentRow, $totals['total_rooms']);
        $sheet->setCellValue('C' . $currentRow, $totals['expected_arrival']);
        $sheet->setCellValue('D' . $currentRow, $totals['expected_departure']);
        $sheet->setCellValue('E' . $currentRow, $totals['stay_over']);
        $sheet->setCellValue('F' . $currentRow, (float) str_replace(',', '', $totals['occupied_rooms']));
        $sheet->setCellValue('G' . $currentRow, (float) str_replace(',', '', $totals['total_pax']));
        $sheet->setCellValue('H' . $currentRow, $totals['available_rooms']);
        $sheet->setCellValue('I' . $currentRow, (float) str_replace(',', '', $totals['total_revenue']));
        $sheet->setCellValue('J' . $currentRow, (float) str_replace(',', '', $totals['arr']));
        $sheet->setCellValue('K' . $currentRow, (float) str_replace(',', '', $totals['revpar']));

        $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->applyFromArray([
            'font'    => ['bold' => true],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Number format for revenue columns (I: Total Revenue, J: ARR, K: RevPAR)
        $lastRow = $currentRow;
        $sheet->getStyle('I' . $headerRow . ':K' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(30);
        foreach ($numberCols as $col) {
            $sheet->getColumnDimension($col)->setWidth(14);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="OccupancyForecast_' . $this->fromdate . '_' . $this->todate . '.xlsx"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}
