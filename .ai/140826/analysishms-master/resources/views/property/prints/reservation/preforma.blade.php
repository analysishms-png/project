<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PreForma Invoice Res No {{ $data->BookNo }} Arrival {{ date('d-M-Y', strtotime($curdate)) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-size: 13px;
            padding: 0;
            margin: 0;
        }

        p {
            margin: 0;
            padding: 0;
        }

        .invoice-container {
            padding: 12px;
            margin: 0 auto;
            width: 100%;
            max-width: 800px;
            background: #fff;
        }

        .table-borderless td {
            padding: 2px 0 !important;
        }

        .header-title {
            font-weight: 700;
            font-size: 20px;
        }

        .sub-title {
            font-weight: 600;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 9pt;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 3px;
            text-align: left;
        }

        table th {
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

        .logo {
            max-height: 80px;
            width: auto;
        }

        .header-content {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 10px;
        }

        .header-logo {
            flex-shrink: 0;
        }

        .header-info {
            flex-grow: 1;
            text-align: center;
        }

        /* PRINT OPTIMIZATION */
        @media print {
            body {
                margin: 0 !important;
                padding: 0 !important;
            }

            .invoice-container {
                padding: 10px !important;
                margin: 0 !important;
                width: 100% !important;
            }

            @page {
                size: A4;
                margin: 6mm;
            }
        }
    </style>
</head>

<body>

    <div class="invoice-container">
        <!-- HEADER -->
        <div class="header-content">
            <div class="header-logo">
                <img src="storage/admin/property_logo/{{ companydata()->logo }}" alt="{{ companydata()->comp_name }}" class="logo">
            </div>
            <div class="header-info">
                <div class="header-title">Estimated Invoice</div>
                <div class="sub-title">{{ companydata()->comp_name }}</div>

                <div class="mt-1">
                    <p> {{ companydata()->address1 }} {{ companydata()->address2 != '' ? ',' . companydata()->address2 : '' }},
                        {{ companydata()->city }} - {{ companydata()->pin }} {{ companydata()->state }}
                    </p>
                    <p>Phone: {{ companydata()->mobile }}</p>
                    <p>E-mail: {{ companydata()->email }} Website: {{ companydata()->website }}</p>
                </div>

                <div class="fw-bold mt-2">GSTIN No : {{ companydata()->gstin }}</div>
            </div>
        </div>

        <hr class="my-2">

        <!-- TWO COLUMN TABLE -->
        <table class="table table-borderless w-100">
            <tbody>
                <tr>
                    <td width="50%">
                        <b>ResNo</b> : {{ $data->BookNo }}/{{ date('d-M-Y', strtotime($curdate)) }}
                    </td>
                    <td width="50%">
                        <b>G.R. Card No</b> :
                    </td>
                </tr>

                <tr>
                    <td>
                        <b>Guest Name</b> : {{ $data->GuestName }}
                    </td>
                    <td>
                        <b>Date of Invoice</b> : {{ date('d-M-Y h:i:s A') }}
                    </td>
                </tr>

                <tr>
                    <td>
                        <b>Bill To</b> : {{ $data->bill_to }}
                    </td>
                    <td>
                        <b>Room</b> : {{ $roomcatsnameswithcomma }}
                    </td>
                </tr>

                <tr>
                    <td>
                        <b>Bill To Address</b> : {{ $data->guestadd }}
                    </td>
                    <td>
                        <b>No of Person</b> : {{ $data->total_adults }} (A) / {{ $data->total_childs }} (C)
                    </td>
                </tr>

                <tr>
                    <td>
                        <b>State</b> : {{ $data->state_name }}
                    </td>
                    <td>
                        <b>Rate Type</b> : {{ $data->planname }}
                    </td>
                </tr>

                <tr>
                    <td>
                        <b>Bill To GSTIN No</b> : {{ $data->Company != '' ? subgroup($data->Company)->gstin : '' }}
                    </td>
                    <td>
                        <b>No of Nights</b> : {{ $data->NoDays }}
                    </td>
                </tr>

                <tr>
                    <td><b>Date of Arrival</b> : {{ date('d-M-Y', strtotime($data->ArrDate)) }}</td>
                    <td>
                        <b>Date of Departure</b> : {{ date('d-M-Y', strtotime($data->DepDate)) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="main">
            <thead>
                <tr>
                    <th>Sr No.</th>
                    <th>Particular</th>
                    <th>HSN</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Total</th>
                    <th>CGST</th>
                    <th>SGST</th>
                </tr>
            </thead>
            @php
                $sn = 1;
                $sne = 1;
                $total = 0;
                $totalroomcharge = 0.0;
                $totalcgst = 0.0;
                $totalsgst = 0.0;
                $totalextras = 0.0;
                $roomGroups = $rooms->groupBy(function ($room) {
                    return $room->RoomCat . '|' . $room->Tarrif . '|' . $room->IncTax;
                });
            @endphp

            <tbody>
                @foreach ($roomGroups as $group)
                    @php
                        $item = $group->first();
                        $taxinc = $item->IncTax == 'Y' ? true : false;
                        $totalNights = $group->sum('NoDays');
                        $totalRows = $group->count();

                        if ($taxinc) {
                            $tarrifamt = $item->Tarrif / (1 + ($item->cgst_rate + $item->sgst_rate) / 100);
                        } else {
                            $tarrifamt = $item->Tarrif;
                        }

                        $total = $tarrifamt * $totalNights;
                        $cgst = ($total * $item->cgst_rate) / 100;
                        $sgst = ($total * $item->sgst_rate) / 100;
                        $totalroomcharge += $total;
                        $totalcgst += $cgst;
                        $totalsgst += $sgst;
                    @endphp

                    <tr>
                        <td>{{ $sn++ }}</td>
                        <td>Room Charges ({{ $item->roomcatname }})</td>
                        <td>996311</td>
                        <td>{{ $totalRows }}</td>
                        <td>{{ number_format($tarrifamt, 2) }}</td>
                        <td>{{ number_format($total, 2) }}</td>
                        <td>{{ number_format($cgst, 2) }}</td>
                        <td>{{ number_format($sgst, 2) }}</td>
                    </tr>
                @endforeach
            <tfoot>
                <tr>
                    <td colspan="5">Total Room Charges</td>
                    <td>{{ number_format($totalroomcharge, 2) }}</td>
                    <td>{{ number_format($totalcgst, 2) }}</td>
                    <td>{{ number_format($totalsgst, 2) }}</td>
                </tr>
            </tfoot>
            </tbody>
        </table>

        @if (count($roominclusive) > 0)
            <p class="text-center">Extra Charges</p>
            <table>
                <tr>
                    <th>Sr No.</th>
                    <th>Description</th>
                    <th>HSN</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Total</th>
                    <th>CGST</th>
                    <th>SGST</th>
                </tr>
                @php
                    $totalExtraCgst = 0;
                    $totalExtraSgst = 0;
                @endphp
                @foreach ($roominclusive as $item)
                    @php
                        $extraTaxinc = $item->IncTax == 'Y' ? true : false;
                        $extraRate = $item->amount;
                        $extraCgst = 0;
                        $extraSgst = 0;
                        $roomqty = $item->total_roomdet;

                        if ($item->chargepost == 'Daily') {
                            $baseQty = $item->total_nights;
                        } elseif ($item->chargepost == 'Once') {
                            $baseQty = $item->total_roomdet;
                        } elseif ($item->chargepost == 'Group') {
                            $baseQty = 1;
                        } else {
                            $baseQty = 0;
                        }

                        $inclusiveAmount = $item->amount * $baseQty;
                        $roomqty = $baseQty;

                        if ($extraTaxinc) {
                            $extraRate = $item->amount / (1 + ($item->cgst_rate + $item->sgst_rate) / 100);
                            $inclusiveAmount = $extraRate * $baseQty;
                            $extraRate = $baseQty > 0 ? $inclusiveAmount / $baseQty : 0;
                            $extraCgst = ($inclusiveAmount * $item->cgst_rate) / 100;
                            $extraSgst = ($inclusiveAmount * $item->sgst_rate) / 100;
                            $totalExtraCgst += $extraCgst;
                            $totalExtraSgst += $extraSgst;
                        } else {
                            $extraCgst = ($inclusiveAmount * $item->cgst_rate) / 100;
                            $extraSgst = ($inclusiveAmount * $item->sgst_rate) / 100;
                            $totalExtraCgst += $extraCgst;
                            $totalExtraSgst += $extraSgst;
                        }

                        $totalextras += $inclusiveAmount;

                    @endphp
                    <tr>
                        <td>{{ $sne++ }}</td>
                        <td>{{ $item->revmastname }}</td>
                        <td>{{ $item->hsn_code }}</td>
                        <td>{{ $roomqty }}</td>
                        <td><b>{{ number_format($extraRate, 2) }}</b></td>
                        <td><b>{{ number_format($inclusiveAmount, 2) }}</b></td>
                        <td><b>{{ number_format($extraCgst, 2) }}</b></td>
                        <td><b>{{ number_format($extraSgst, 2) }}</b></td>
                    </tr>
                @endforeach
                <tfoot>
                    <tr>
                        <td colspan="5">Total Extra Charges</td>
                        <td>{{ number_format($totalextras, 2) }}</td>
                        <td>{{ number_format($totalExtraCgst, 2) }}</td>
                        <td>{{ number_format($totalExtraSgst, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <div class="print-area p-2">

            @php
                $advancesum = 0.0;
                foreach ($advance as $payment) {
                    $advancesum += $payment->amtcr;
                }

                if (!isset($totalExtraCgst)) {
                    $totalExtraCgst = 0;
                }
                if (!isset($totalExtraSgst)) {
                    $totalExtraSgst = 0;
                }

                $taxableRoomCharge = 0.0;
                $totalTaxCgst = 0.0;
                $totalTaxSgst = 0.0;

                $totalcgsttotal = $totalcgst + $totalExtraCgst;
                $totalsgsttotal = $totalsgst + $totalExtraSgst;

                foreach ($roomGroups as $group) {
                    $room = $group->first();
                    if ($room->IncTax == 'Y') {
                        $roomTariffAmt = $room->Tarrif / (1 + ($room->cgst_rate + $room->sgst_rate) / 100);
                    } else {
                        $roomTariffAmt = $room->Tarrif;
                    }
                    $roomTotal = $roomTariffAmt * $group->sum('NoDays');
                    $taxableRoomCharge += $roomTotal;
                    $totalTaxCgst += ($roomTotal * $room->cgst_rate) / 100;
                    $totalTaxSgst += ($roomTotal * $room->sgst_rate) / 100;
                }

                foreach ($roominclusive as $item) {
                    $extraTariffAmt = $item->amount / (1 + ($item->cgst_rate + $item->sgst_rate) / 100);
                    if ($item->IncTax != 'Y') {
                        $extraTariffAmt = $item->amount;
                    }
                    if ($item->chargepost == 'Daily') {
                        $extraQty = $item->total_nights;
                    } elseif ($item->chargepost == 'Once') {
                        $extraQty = $item->total_roomdet;
                    } elseif ($item->chargepost == 'Group') {
                        $extraQty = 1;
                    } else {
                        $extraQty = 0;
                    }

                    $extraTotal = $extraTariffAmt * $extraQty;
                    if ($item->chargepost == 'Daily') {
                        $extraTotal = $extraTariffAmt * $item->total_nights;
                    }
                    $taxableRoomCharge += $extraTotal;
                    $totalTaxCgst += ($extraTotal * $item->cgst_rate) / 100;
                    $totalTaxSgst += ($extraTotal * $item->sgst_rate) / 100;
                }

                $totalTaxes = $totalcgsttotal + $totalsgsttotal;
                $totalCharges = $totalroomcharge + $totalextras;
                $grandTotal = $totalCharges + $totalTaxes;
                $balance = $grandTotal - $advancesum;
            @endphp

            <!-- LEFT BOX -->
            <div class="row g-2">
                <div class="col-8">
                    <div class="box-border p-2">
                        <h6 class="fw-bold mb-1">Total Payable Amount</h6>
                        <p class="mb-2">{{ amountToWords($balance) }}</p>

                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($advance as $payment)
                                    <tr>
                                        <td>{{ date('d-M-Y', strtotime($payment->vdate)) }}</td>
                                        <td>{{ $payment->comments }}</td>
                                        <td>{{ number_format($payment->amtcr, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Tax Vifergation Details --}}
                    <div class="box-border mt-2 p-2">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tax Name</th>
                                    <th class="text-end">Taxable Amount</th>
                                    <th class="text-end">Tax Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $txnames[0]['name'] ?? '' }} @ {{ $txnames[0]['rate'] ?? '' }} Rate</td>
                                    <td class="text-end">{{ number_format($taxableRoomCharge, 2) }}</td>
                                    <td class="text-end">{{ number_format($totalcgsttotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ $txnames[1]['name'] ?? '' }} @ {{ $txnames[1]['rate'] ?? '' }} Rate</td>
                                    <td class="text-end">{{ number_format($taxableRoomCharge, 2) }}</td>
                                    <td class="text-end">{{ number_format($totalsgsttotal, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- RIGHT BOX -->
                <div class="col-4 mt-3">
                    <div class="box-border p-2">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <td>Total Charges (Rs)</td>
                                    <td class="text-end fw-bold">{{ number_format($totalCharges, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Total CGST (Rs)</td>
                                    <td class="text-end">{{ number_format($totalcgsttotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Total SGST (Rs)</td>
                                    <td class="text-end">{{ number_format($totalsgsttotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Grand Total (Rs)</td>
                                    <td class="text-end fw-bold">{{ number_format($grandTotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Advance (Rs)</td>
                                    <td class="text-end">{{ number_format($advancesum, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Balance Due (Rs)</td>
                                    <td class="text-end fw-bold">{{ number_format($balance, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <p class="mt-4">__________________________</p>
                <p>Guest Signature</p>
            </div>
            <div class="col-md-6 position-relative text-end">
                <p class="mt-4">__________________________</p>
                <p>Authorized Signature</p>
            </div>
        </div>
    </div>

    <div class="footer">
        @php
            $chkintime = new DateTime(fomparameter()->checkintime);
            $deptime = new DateTime(fomparameter()->checkout);
        @endphp
        <p>A Government notification requires Indian / Foreign residents to carry proof of identity at the time of check
            in. The proof of identity can either be the guests driving license, passport or voters card.</p>
        </br>
        <p>Check in Time :- <b>{{ $chkintime->format('g:i A') }}</b> Check out time :
            <b>{{ $deptime->format('g:i A') }}</b>
        </p>
        <p style="text-decoration: underline;"><b>Cancellation Policy : </b></p>
        @if (fomparameter()->resinstruction1 != '' && fomparameter()->resinstruction1 != null)
            <p>{{ fomparameter()->resinstruction1 }}</p>
        @endif
        @if (fomparameter()->resinstruction2 != '' && fomparameter()->resinstruction2 != null)
            <p>{{ fomparameter()->resinstruction2 }}</p>
        @endif
        @if (fomparameter()->resinstruction3 != '' && fomparameter()->resinstruction3 != null)
            <p>{{ fomparameter()->resinstruction3 }}</p>
        @endif
        @if (fomparameter()->resinstruction4 != '' && fomparameter()->resinstruction4 != null)
            <p>{{ fomparameter()->resinstruction4 }}</p>
        @endif
        @if (fomparameter()->resinstruction5 != '' && fomparameter()->resinstruction5 != null)
            <p>{{ fomparameter()->resinstruction5 }}</p>
        @endif
        @if (fomparameter()->resinstruction6 != '' && fomparameter()->resinstruction6 != null)
            <p>{{ fomparameter()->resinstruction6 }}</p>
        @endif
        <br><br><br>
    </div>
</body>

</html>
<script>
    window.onload = function() {
        window.print();
    };
</script>
