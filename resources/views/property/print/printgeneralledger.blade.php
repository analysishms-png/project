@include('property.print._header', [
    'company'  => $company,
    'title'    => 'General Ledger Report',
    'fromDate' => $fromDate,
    'toDate'   => $toDate,
])

<table>
    <thead>
        <tr>
            <th style="width:20%;">A/C Name</th>
            <th style="width:9%;">Date</th>
            <th style="width:16%;">Doc ID</th>
            <th style="width:28%;">Narration</th>
            <th style="width:11%;">Contra</th>
            <th style="width:8%;" class="text-right">Debit</th>
            <th style="width:8%;" class="text-right">Credit</th>
        </tr>
    </thead>
    <tbody>
        @if (count($accounts) === 0)
            <tr>
                <td colspan="7" style="text-align:center; padding:10px;">No records found for the selected date range.</td>
            </tr>
        @else
            @php
                $grandDr = 0;
                $grandCr = 0;
            @endphp

            @foreach ($accounts as $acc)
                @php
                    $grandDr += $acc['total_dr'];
                    $grandCr += $acc['total_cr'];
                @endphp
                <tr style="background:#e9ecef; font-weight:700;">
                    <td colspan="5" style="padding:6px;">
                        {{ $acc['name'] }}
                        <span style="font-weight:400; color:#6c757d;">[{{ $acc['group_name'] ?? 'Other' }}]</span>
                    </td>
                    <td class="text-right" style="padding:6px;">
                        Opening {{ number_format($acc['opening_balance'], 2) }}
                    </td>
                    <td></td>
                </tr>

                @if (count($acc['transactions']) === 0)
                    <tr>
                        <td colspan="7" style="padding:4px; color:#6c757d; font-style:italic;">No transactions in period.</td>
                    </tr>
                @else
                    @foreach ($acc['transactions'] as $tx)
                        <tr>
                            <td></td>
                            <td>{{ date('d-m-Y', strtotime($tx['vdate'])) }}</td>
                            <td>{{ $tx['docid'] }}</td>
                            <td>{{ $tx['narration'] }}</td>
                            <td>{{ $tx['contrasub'] }}</td>
                            <td class="text-right">{{ $tx['amtdr'] > 0 ? number_format($tx['amtdr'], 2) : '' }}</td>
                            <td class="text-right">{{ $tx['amtcr'] > 0 ? number_format($tx['amtcr'], 2) : '' }}</td>
                        </tr>
                    @endforeach
                @endif

                <tr style="background:#f8f9fa; font-weight:700;">
                    <td colspan="4" style="padding:5px; text-align:right;">Sub Total</td>
                    <td style="text-align:right; font-weight:400; font-size:11px;">
                        Closing {{ number_format($acc['closing_balance'], 2) }}
                    </td>
                    <td class="text-right">{{ number_format($acc['total_dr'], 2) }}</td>
                    <td class="text-right">{{ number_format($acc['total_cr'], 2) }}</td>
                </tr>
            @endforeach

            <tr style="background:#d9e1f2; font-weight:700;">
                <td colspan="5" style="padding:6px;">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($grandDr, 2) }}</td>
                <td class="text-right">{{ number_format($grandCr, 2) }}</td>
            </tr>
        @endif
    </tbody>
</table>
