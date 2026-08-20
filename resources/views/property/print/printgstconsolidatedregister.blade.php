<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GST Consolidated Register</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 11px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 4px 6px; font-size: 10px; }
        th { background: #4472C4; color: #fff; text-align: center; }
        .text-right { text-align: right; }
        .grand-total td { font-weight: bold; background: #f0f0f0; }
        .summary-title { font-size: 13px; font-weight: bold; margin: 15px 0 8px; }
        .page-break { page-break-before: always; }
        @media print { body { margin: 10px; } }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company->comp_name ?? '' }}</h2>
        <p>GST Consolidated Register</p>
        <p>Period: {{ $fromdate }} to {{ $todate }} | Source: {{ ucfirst($source) }} | Generated: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Source</th>
                <th>Bill No</th>
                <th>Date</th>
                <th>GSTIN</th>
                <th>Party</th>
                <th class="text-right">Taxable (₹)</th>
                <th class="text-right">Rate %</th>
                <th class="text-right">CGST (₹)</th>
                <th class="text-right">SGST (₹)</th>
                <th class="text-right">IGST (₹)</th>
                <th class="text-right">Total Tax (₹)</th>
            </tr>
        </thead>
        <tbody>
            @php $n = 1; @endphp
            @foreach($data as $row)
                <tr>
                    <td>{{ $n++ }}</td>
                    <td>{{ $row['Source'] ?? '' }}</td>
                    <td>{{ $row['BillNo'] ?? '' }}</td>
                    <td>{{ \Carbon\Carbon::parse($row['VDate'] ?? '')->format('d/m/Y') }}</td>
                    <td>{{ $row['GSTIN'] ?? '' }}</td>
                    <td>{{ $row['PartyName'] ?? '' }}</td>
                    <td class="text-right">{{ number_format($row['BaseValue'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ $row['TaxPer'] ?? 0 }}%</td>
                    <td class="text-right">{{ number_format($row['CGSTAmt'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($row['SGSTAmt'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($row['IGSTAmt'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($row['TotalTax'] ?? 0, 2) }}</td>
                </tr>
            @endforeach
            @if(count($data) === 0)
                <tr><td colspan="12" style="text-align:center">No data for the selected period.</td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td colspan="6">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($grand['BaseValue'] ?? 0, 2) }}</td>
                <td></td>
                <td class="text-right">{{ number_format($grand['CGSTAmt'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($grand['SGSTAmt'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($grand['IGSTAmt'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($grand['TotalTax'] ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="summary-title">Summary by GSTIN + Rate</div>
    <table>
        <thead>
            <tr>
                <th>GSTIN</th>
                <th class="text-right">Rate %</th>
                <th class="text-right">Base Value (₹)</th>
                <th class="text-right">CGST (₹)</th>
                <th class="text-right">SGST (₹)</th>
                <th class="text-right">IGST (₹)</th>
                <th class="text-right">Total Tax (₹)</th>
                <th class="text-right">Bills</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary as $s)
                <tr>
                    <td>{{ $s['GSTIN'] ?? '' }}</td>
                    <td class="text-right">{{ $s['TaxPer'] ?? 0 }}%</td>
                    <td class="text-right">{{ number_format($s['BaseValue'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($s['CGSTAmt'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($s['SGSTAmt'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($s['IGSTAmt'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($s['TotalTax'] ?? 0, 2) }}</td>
                    <td class="text-right">{{ $s['BillCount'] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td colspan="2">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($grand['BaseValue'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($grand['CGSTAmt'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($grand['SGSTAmt'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($grand['IGSTAmt'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($grand['TotalTax'] ?? 0, 2) }}</td>
                <td class="text-right">{{ count($summary) }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="text-align:center;margin-top:30px;color:#999;font-size:10px">
        Generated by {{ $company->comp_name ?? 'HMS' }} | {{ now()->format('d/m/Y H:i:s') }}
    </p>
</body>
</html>
