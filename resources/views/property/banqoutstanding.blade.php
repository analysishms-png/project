@extends('property.layouts.main')
@section('main-container')
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
    <link href="https://unpkg.com/tabulator-tables@6.3.0/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://oss.sheetjs.com/sheetjs/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
    <script src="https://unpkg.com/tabulator-tables@6.3.0/dist/js/tabulator.min.js"></script>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body box animate__animated animate__bounceIn">

                            <form action="">
                                <div class="row">
                                    <input type="hidden" value="{{ $comp->start_dt }}" name="start_dt" id="start_dt">
                                    <input type="hidden" value="{{ $comp->end_dt }}" name="end_dt" id="end_dt">
                                    <input type="hidden" value="{{ $comp->comp_name }}" name="compname" id="compname">
                                    <input class="none" type="date" value="{{ $fromdate }}" name="ncurdatef" id="ncurdatef">
                                    <div class="">
                                        <div class="form-group">
                                            <label for="fromdate" class="col-form-label">From Date <i
                                                    class="fa-regular fa-calendar mb-1"></i></label>
                                            <input type="date" value="{{ $fromdate }}" class="form-control" name="fromdate"
                                                id="fromdate">
                                        </div>
                                    </div>
                                    <div class="">
                                        <div class="form-group">
                                            <label for="todate" class="col-form-label">To Date <i
                                                    class="fa-regular fa-calendar mb-1"></i></label>
                                            <input type="date" value="{{ $todate }}" class="form-control" name="todate" id="todate">
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="form-group">
                                            <label for="onlyoutstanding" class="col-form-label">Only Outstanding <i
                                                    class="fa-regular fa-square-check mb-1"></i></label>
                                            <select class="form-control" name="onlyoutstanding" id="onlyoutstanding">
                                                <option value="no">All Bills</option>
                                                <option value="yes">Outstanding Only</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div style="margin-top: 30px;" class="ml-5">
                                        <button id="fetchbutton" name="fetchbutton" type="button" class="btn btn-success">Refresh <i class="fa-solid fa-arrows-rotate"></i></button>
                                    </div>
                                </div>
                            </form>

                            <div class="mt-3">
                                <button id="print-table" class="btn btn-primary">Print <i
                                        class="fa-solid fa-print"></i></button>
                                <button id="download-xlsx" class="btn btn-success">Excel <i
                                        class="fa fa-file-excel-o"></i></button>
                            </div>

                            <div id="printSection">
                                <div class="text-center titlep">
                                    <h3>{{ $comp->comp_name }}</h3>
                                    <p style="margin-top:-10px; font-size:16px;">{{ $comp->address1 }}</p>
                                    <p style="margin-top:-10px; font-size:16px;">{{ $statename . ' - ' . $comp->city . ' - ' . $comp->pin }}</p>
                                    <p style="margin-top:-10px; font-size:16px;">Banquet Outstanding Report</p>
                                    <p style="text-align:left;margin-top:-10px; font-size:16px;">From Date: <span id="fromdatep"></span> To Date:
                                        <span id="todatep"></span>
                                    </p>
                                </div>
                                <div class="mt-3" id="banqoutstanding-table"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let table;

            $(document).on('change', '#fromdate', function() {
                validateFinancialYear('#fromdate');
            });
            $(document).on('change', '#todate', function() {
                validateFinancialYear('#todate');
            });

            $(document).on('click', '#fetchbutton', function() {
                let fromdate = $('#fromdate').val();
                let todate = $('#todate').val();
                let onlyoutstanding = $('#onlyoutstanding').val();
                if (fromdate == '') {
                    pushNotify('error', 'Banquet Outstanding', 'Please Select From Date', 'fade', 300, '', '', true, true, true, 2000, 20, 20, 'outline', 'right top');
                    $('#fromdate').addClass('invalid');
                }
                if (todate == '') {
                    pushNotify('error', 'Banquet Outstanding', 'Please Select To Date', 'fade', 300, '', '', true, true, true, 2000, 20, 20, 'outline', 'right top');
                    $('#todate').addClass('invalid');
                }

                if (fromdate != '' && todate != '') {
                    showLoader();
                    let fdata = new XMLHttpRequest();
                    fdata.open('POST', '/banqoutstandingfetch', true);
                    fdata.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    fdata.onreadystatechange = function() {
                        if (fdata.status === 200 && fdata.readyState === 4) {
                            $('#myloader').addClass('none');
                            let results = JSON.parse(fdata.responseText);
                            let tableData = processData(results.report);

                            $('#fromdatep').text(dmy(fromdate));
                            $('#todatep').text(dmy(todate));

                            let t = results.totals;
                            let columns = [{
                                    title: "Bill No",
                                    field: "vno",
                                    headerWordWrap: true,
                                    minWidth: 80
                                },
                                {
                                    title: "Date",
                                    field: "vdate",
                                    headerWordWrap: true,
                                    minWidth: 100
                                },
                                {
                                    title: "Party",
                                    field: "party",
                                    headerWordWrap: true,
                                    minWidth: 200
                                },
                                {
                                    title: "Function",
                                    field: "funcname",
                                    headerWordWrap: true,
                                    minWidth: 150
                                },
                                {
                                    title: "Bill Amount",
                                    field: "billamt",
                                    hozAlign: "right",
                                    bottomCalc: "sum",
                                    bottomCalcFormatter: "money",
                                    headerWordWrap: true,
                                    minWidth: 110
                                },
                                {
                                    title: "Advance",
                                    field: "advance",
                                    hozAlign: "right",
                                    bottomCalc: "sum",
                                    bottomCalcFormatter: "money",
                                    headerWordWrap: true,
                                    minWidth: 110
                                },
                                {
                                    title: "Settled",
                                    field: "settled",
                                    hozAlign: "right",
                                    bottomCalc: "sum",
                                    bottomCalcFormatter: "money",
                                    headerWordWrap: true,
                                    minWidth: 110
                                },
                                {
                                    title: "Total Paid",
                                    field: "paid",
                                    hozAlign: "right",
                                    bottomCalc: "sum",
                                    bottomCalcFormatter: "money",
                                    headerWordWrap: true,
                                    minWidth: 110
                                },
                                {
                                    title: "Outstanding",
                                    field: "outstanding",
                                    hozAlign: "right",
                                    bottomCalc: "sum",
                                    bottomCalcFormatter: "money",
                                    formatter: function(cell) {
                                        let v = parseFloat(cell.getValue());
                                        if (v > 0.005) {
                                            return '<span style="color:#dc3545;font-weight:600;">' + moneyFormat(v) + '</span>';
                                        }
                                        return moneyFormat(v);
                                    },
                                    headerWordWrap: true,
                                    minWidth: 110
                                }
                            ];

                            pushNotify('success', 'Banquet Outstanding', t.bills + ' Bills | Outstanding: ' + moneyFormat(t.outstanding), 'fade', 300, '', '', true, true, true, 500, 20, 20, 'outline', 'right top');

                            if (table) {
                                table.setData(tableData);
                            } else {
                                table = new Tabulator("#banqoutstanding-table", {
                                    data: tableData,
                                    layout: "fitColumns",
                                    printHeader: $('.titlep').html(),
                                    printFooter: "<h2>Copyright @Analysis</h2>",
                                    columns: columns,
                                    columnCalcs: "both",
                                });
                            }
                            setTimeout(hideLoader, 1000);
                        } else {
                            setTimeout(hideLoader, 1000);
                        }
                    }
                    fdata.send(`fromdate=${fromdate}&todate=${todate}&onlyoutstanding=${onlyoutstanding}&_token={{ csrf_token() }}`);
                }
            });

            $("#print-table").on("click", function() {
                table.print(false, true);
            });

            $("#download-xlsx").on("click", function() {
                table.download("xlsx", "banquetoutstanding.xlsx", {
                    sheetName: "Banquet Outstanding"
                });
            });

            function processData(rows) {
                let tableData = [];
                rows.forEach(r => {
                    tableData.push({
                        vno: r.vno,
                        vdate: dmy(r.vdate),
                        party: r.party || '',
                        funcname: r.funcname || '',
                        billamt: parseFloat(r.billamt).toFixed(2),
                        advance: parseFloat(r.advance).toFixed(2),
                        settled: parseFloat(r.settled).toFixed(2),
                        paid: parseFloat(r.paid).toFixed(2),
                        outstanding: parseFloat(r.outstanding).toFixed(2)
                    });
                });
                return tableData;
            }

            setTimeout(() => {
                $('#fetchbutton').trigger('click');
            }, 500);

        });
    </script>
@endsection
