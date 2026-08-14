<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Letter Folio {{ $data->foliono }} Name {{ $data->GuestName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.2;
            margin: 0;
            padding: 10px;
            font-size: 10pt;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            max-width: calc(1rem + 7.5rem);
            height: auto;
        }

        .header-text {
            flex-grow: 1;
            padding: 0 10px;
        }

        .contact-info,
        .hotel-name,
        .hotel-subname {
            margin: 0;
        }

        .hotel-name {
            font-size: 14pt;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .hotel-subname {
            font-style: italic;
        }

        .ref-no {
            margin: 5px 0;
        }

        .details-panel {
            display: flex;
            justify-content: space-between;
            border: 1px solid #ddd;
            padding: 5px;
            margin: 5px 0;
        }

        .details-column {
            flex: 1;
            font-size: 9pt;
        }

        .details-column h3 {
            margin: 0 0 3px 0;
            font-size: 10pt;
            border-bottom: 1px solid #ddd;
        }

        .details-column p {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 9pt;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 3px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 5px;
            font-size: 10pt;
        }

        .footer p {
            margin: 0;
        }

        tfoot {
            font-weight: bold;
        }

        @media print {
            body {
                width: 100%;
                height: 50vh;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="storage/admin/property_logo/{{ $company->logo }}" alt="{{ $company->comp_name }}" class="logo">
        <div class="header-text">
            <p class="hotel-name">{{ $company->comp_name }}</p>
            <p class="contact-info">{{ $company->address1 }}, {{ $company->city }} ({{ $company->state }})</p>
            {{-- <p class="contact-info">Website : {{ $company->website }}</p>
            <p class="contact-info">Cont Us.: {{ $company->mobile }}, {{ $company->email }}</p>
            <p class="contact-info">GSTIN No : {{ $company->gstin }}</p> --}}
            {{-- Website --}}
            @if (!empty($company->website))
                <p class="contact-info">Website : {{ $company->website }}</p>
            @endif

            {{-- Mobile always first --}}
            @if (!empty($company->mobile))
                <p class="contact-info">Contact No.: {{ $company->mobile }}</p>
            @endif

            {{-- Email: handle single + multiple --}}
            @if (!empty($company->email))
                @php
                    // Convert to array if multiple emails are provided, separated by comma
                    $emails = explode(',', $company->email);
                @endphp

                @foreach ($emails as $email)
                    <p class="contact-info">Email : {{ trim($email) }}</p>
                @endforeach
            @endif

            {{-- GST --}}
            @if (!empty($company->gstin))
                <p class="contact-info">GSTIN No : {{ $company->gstin }}</p>
            @endif
        </div>
    </div>

    <div class="ref-no">Ref. No: {{ $data->BookNo }}/{{ date('d-M-Y', strtotime($curdate)) }}</div>

    <div class="details-panel">
        <div class="details-column">
            <h3>Guest Details</h3>
            <p><b>Name:</b> {{ $data->GuestName }}</p>
            <p><b>Address:</b> {{ $data->guestadd }}</p>
            <p><b>State:</b> {{ $data->state_name }}</p>
            <p><b>Mobile:</b> {{ $data->mobile_no }}</p>
            <p><b>Email:</b> {{ $data->email_id }}</p>
        </div>
        <div class="details-column">
            <h3>Company Details</h3>
            <p><b>Name:</b> {{ $data->companyname }}</p>
            <p><b>GSTIN:</b> {{ $data->companygstin }}</p>
        </div>
        <div class="details-column">
            <h3>Travel Agency Details</h3>
            <p><b>Name:</b> {{ $data->travelname }}</p>
            <p><b>GSTIN:</b> {{ $data->travelgstin }}</p>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <th style="width: 25%; text-align: left; padding: 8px;">Arrival Date</th>
            <td style="width: 25%; padding: 8px;">{{ date('d-M-Y', strtotime($data->ArrDate)) }}</td>
            <th style="width: 25%; text-align: left; padding: 8px;">Status</th>
            <td style="width: 25%; padding: 8px;">{{ $data->ResStatus }}</td>
        </tr>
        <tr>
            <th style="text-align: left; padding: 8px;">Departure Date</th>
            <td style="padding: 8px;">{{ date('d-M-Y', strtotime($data->DepDate)) }}</td>
            <th style="text-align: left; padding: 8px;">Plan Name</th>
            <td style="padding: 8px;">{{ $data->planname }}</td>
        </tr>
        <tr>
            <th style="text-align: left; padding: 8px;">No of Days</th>
            <td style="padding: 8px;">{{ $data->NoDays }}</td>
            <th style="text-align: left; padding: 8px;">Booked By</th>
            <td style="padding: 8px;">{{ $data->BookedBy }}</td>
        </tr>
    </table>
    @if ($data->remarks != '')
        <p><strong>Remarks :</strong> {{ $data->remarks }}</p>
    @endif
    <p><b>Sub: Confirmation Letter for Ref. No.:</b> {{ $data->BookNo }}/{{ date('d-m-Y', strtotime($curdate)) }}</p>

    <p>Dear Guest,</br> We are pleased to confirm your reservation as per the following details:</p>

    <table>
        <tr>
            <th>Room Type</th>
            <th>Occupancy</th>
            <th>No of Rooms</th>
            <th>Adults</th>
            <th>Childs</th>
            <th>Room Tarrif</th>
            <th>Amount</th>
            <th>Incl. Tax</th>
        </tr>
        @php
            $totalroom = 0;
            $totaladult = 0;
            $totalchild = 0;
            $totaltarrif = 0;
            $totalamount = 0;
            $totalextras = 0;
            $roomtotalamount = 0;
        @endphp
        @foreach ($rooms as $item)
            @php
                $roomAmount = $item->total_roomdet * $item->NoDays * $item->Tarrif;

                $totalroom += $item->total_roomdet;
                $totaladult += $item->total_adults;
                $totalchild += $item->total_childs;
                $totaltarrif += $item->Tarrif;
                $roomtotalamount += $roomAmount;
            @endphp
            <tr>
                <td>{{ $item->roomcatname }}</td>
                <td>
                    @php
                        $types = [
                            1 => 'Single',
                            2 => 'Double',
                            3 => 'Triple',
                            4 => 'Four',
                            5 => 'Five',
                        ];
                    @endphp

                    {{ $types[$item->total_adults] ?? '' }}
                </td>
                <td>{{ $item->total_roomdet }}</td>
                <td>{{ $item->total_adults }}</td>
                <td>{{ $item->total_childs }}</td>
                <td><b>{{ number_format($item->Tarrif, 2) }}</b></td>
                <td><b>{{ number_format($roomAmount, 2) }}</b></td>
                <td>{{ $item->IncTax == 'Y' ? 'Yes' : 'No' }}</td>
            </tr>
        @endforeach
        <tfoot>
            <tr>
                <td>Total</td>
                <td></td>
                <td>{{ $totalroom }}</td>
                <td>{{ $totaladult }}</td>
                <td>{{ $totalchild }}</td>
                <td></td>
                <td>{{ number_format($roomtotalamount, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    @if (count($roominclusive) > 0)
        <p class="text-center">Extra Charges</p>
        <table>
            <tr>
                <th>Description</th>
                <th>No of Rooms</th>
                <th>Rate</th>
                <th>Amount</th>
            </tr>
            @foreach ($roominclusive as $item)
                @php
                    if ($item->chargepost == 'Daily') {
                        $inclusiveAmount = $item->total_roomdet * $item->NoDays * $item->amount;
                    } elseif ($item->chargepost == 'Once') {
                        $inclusiveAmount = $item->total_roomdet * $item->amount;
                    } elseif ($item->chargepost == 'Group') {
                        $inclusiveAmount = $item->amount;
                    } else {
                        $inclusiveAmount = 0;
                    }
                    $totalextras += $inclusiveAmount;
                @endphp
                <tr>
                    <td>{{ $item->revmastname }}</td>
                    <td>{{ $item->total_roomdet }}</td>
                    <td><b>{{ number_format($item->amount, 2) }}</b></td>
                    <td><b>{{ number_format($inclusiveAmount, 2) }}</b></td>
                </tr>
            @endforeach
            <tfoot>
                <tr>
                    <td>Total Extra Charges</td>
                    <td></td>
                    <td></td>
                    <td>{{ number_format($totalextras, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    @php
        $totalamount = $roomtotalamount + $totalextras;
    @endphp

    <p class="text-center">Advance Details</p>
    <table>
        <tr>
            <th>Receipt Number</th>
            <th>Date</th>
            <th>Type</th>
            <th>Amount</th>
        </tr>
        @php
            $totalamtcr = 0.0;
        @endphp
        @foreach ($advance as $item)
            @php
                $totalamtcr += $item->amtcr;
            @endphp
            <tr>
                <td>{{ $item->vno }}</td>
                <td>{{ date('d-M-Y', strtotime($item->vdate)) }}</td>
                <td>{{ $item->paytype }}</td>
                <td><b>{{ number_format($item->amtcr, 2) }}</b></td>
            </tr>
        @endforeach

        <tfoot>
            <tr>
                <td>Total</td>
                <td></td>
                <td></td>
                <td>{{ number_format($totalamtcr, 2) }}</td>
            </tr>
            <tr>
                <td>Balance</td>
                <td></td>
                <td></td>
                <td>{{ number_format($totalamount - $totalamtcr, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        @php
            $chkintime = new DateTime($enviro->checkintime);
            $deptime = new DateTime($enviro->checkout);
        @endphp
        <p>A Government notification requires Indian / Foreign residents to carry proof of identity at the time of check
            in. The proof of identity can either be the guests driving license, passport or voters card.</p>
        </br>
        <p>Check in Time :- <b>{{ $chkintime->format('g:i A') }}</b> Check out time :
            <b>{{ $deptime->format('g:i A') }}</b>
        </p>
        <p style="text-decoration: underline;"><b>Cancellation Policy : </b></p>
        @if ($enviro->resinstruction1 != '' && $enviro->resinstruction1 != null)
            <p>{{ $enviro->resinstruction1 }}</p>
        @endif
        @if ($enviro->resinstruction2 != '' && $enviro->resinstruction2 != null)
            <p>{{ $enviro->resinstruction2 }}</p>
        @endif
        @if ($enviro->resinstruction3 != '' && $enviro->resinstruction3 != null)
            <p>{{ $enviro->resinstruction3 }}</p>
        @endif
        @if ($enviro->resinstruction4 != '' && $enviro->resinstruction4 != null)
            <p>{{ $enviro->resinstruction4 }}</p>
        @endif
        @if ($enviro->resinstruction5 != '' && $enviro->resinstruction5 != null)
            <p>{{ $enviro->resinstruction5 }}</p>
        @endif
        @if ($enviro->resinstruction6 != '' && $enviro->resinstruction6 != null)
            <p>{{ $enviro->resinstruction6 }}</p>
        @endif
        <br><br><br>
    </div>
</body>

</html>

<script>
    setTimeout(() => {
        window.print();
    }, 1000);
</script>
