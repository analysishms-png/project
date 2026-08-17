@include('property.print._header', [
    'company'  => $company,
    'title'    => 'Journal Book Report',
    'fromDate' => $fromDate,
    'toDate'   => $toDate,
])

@if (!empty($vtype))
    <p style="font-size:11px; margin-top:4px;">
        Voucher Type: <strong>{{ $vtype }}</strong>
    </p>
@endif

<table>
    <thead>
        <tr>
            <th style="width:9%;">Date</th>
            <th style="width:8%;">Vch Type</th>
            <th style="width:11%;">Vch No</th>
            <th style="width:17%;">Doc ID</th>
            <th style="width:18%;">A/C Name</th>
            <th style="width:21%;">Narration</th>
            <th style="width:8%;" class="text-right">Debit</th>
            <th style="width:8%;" class="text-right">Credit</th>
        </tr>
    </thead>
    <tbody>
        @if (count($rows) === 0)
            <tr>
                <td colspan="8" style="text-align:center; padding:10px;">No records found for the selected date range.</td>
            </tr>
        @else
            @foreach ($rows as $r)
                <tr>
                    <td>{{ date('d-m-Y', strtotime($r->vdate)) }}</td>
                    <td>{{ $r->vtype }}</td>
                    <td>{{ trim(($r->vprefix ?? '') . ' ' . ($r->vno ?? '')) }}</td>
                    <td>{{ $r->docid }}</td>
                    <td>{{ $r->account_name ?? $r->subcode }}</td>
                    <td>{{ $r->narration }}</td>
                    <td class="text-right">{{ $r->amtdr > 0 ? number_format($r->amtdr, 2) : '' }}</td>
                    <td class="text-right">{{ $r->amtcr > 0 ? number_format($r->amtcr, 2) : '' }}</td>
                </tr>
            @endforeach

            <tr style="background:#d9e1f2; font-weight:700;">
                <td colspan="6" style="padding:6px;">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($totalDr, 2) }}</td>
                <td class="text-right">{{ number_format($totalCr, 2) }}</td>
            </tr>
        @endif
    </tbody>
</table>
