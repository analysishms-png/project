@extends('property.layouts.main')
@section('main-container')
    <link href="https://unpkg.com/tabulator-tables@6.3.0/dist/css/tabulator.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://oss.sheetjs.com/sheetjs/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
    <script src="https://unpkg.com/tabulator-tables@6.3.0/dist/js/tabulator.min.js"></script>
    <style>
        .diag-tab {
            padding: 8px 16px;
            border: 2px solid #667eea;
            background: #fff;
            color: #667eea;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            margin-right: 8px;
            margin-bottom: 8px;
        }
        .diag-tab.active,
        .diag-tab:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .badge-flag {
            background: #dc3545;
            color: #fff;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
        }
    </style>
    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center titlep">
                                <h3>{{ $company->comp_name }}</h3>
                                <p style="margin-top:-10px; font-size:16px;">{{ $company->address1 }}</p>
                                <p style="margin-top:-10px; font-size:16px;">
                                    {{ $statename . ' - ' . $company->city . ' - ' . $company->pin }}</p>
                                <p style="margin-top:-10px; font-size:16px;">Front Office Mismatch Diagnostics
                                    ({{ date('d-m-Y', strtotime($ncurdate)) }})</p>
                            </div>
                            <div class="d-flex flex-wrap mt-3">
                                <button class="diag-tab active" data-tab="noshow">No-Show Candidates</button>
                                <button class="diag-tab" data-tab="orphanrooms">Orphan Rooms</button>
                                <button class="diag-tab" data-tab="folionoroom">Folios w/o Room</button>
                                <button class="diag-tab" data-tab="cancelledfolio">Folio on Cancelled Booking</button>
                                <button class="diag-tab" data-tab="resvfolio">Reservation vs Folio</button>
                                <button class="diag-tab" data-tab="settlement">Settlement Balance</button>
                            </div>
                            <div class="mt-3">
                                <button id="download-xlsx" class="btn btn-success">Excel <i
                                        class="fa fa-file-excel-o"></i></button>
                                <button id="print-table" class="btn btn-primary">Print <i
                                        class="fa-solid fa-print"></i></button>
                                <span class="text-muted ms-2"><i class="fa-solid fa-circle-info"></i> Read-only
                                    diagnostic — no data is modified.</span>
                            </div>
                            <div class="mt-3" id="diag-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let table = null;
            let currentTab = 'noshow';

            const columnSets = {
                noshow: [
                    { title: "Res No", field: "ResNo", sorter: "number", width: 90 },
                    { title: "Res Date", field: "ResDate", sorter: "string", width: 100 },
                    { title: "Guest", field: "GuestName", sorter: "string", width: 180 },
                    { title: "Status", field: "ResStatus", sorter: "string", width: 100 },
                    { title: "Arrival", field: "ArrDate", sorter: "string", width: 100 },
                    { title: "Departure", field: "DepDate", sorter: "string", width: 100 },
                    { title: "Booked Room", field: "BookedRoom", sorter: "string", width: 90 },
                    { title: "Rate", field: "BookedRate", sorter: "number", width: 90 },
                    { title: "Advance", field: "Advance", sorter: "number", width: 110,
                        formatter: "money", formatterParams: { precision: 2 },
                        bottomCalc: "sum", bottomCalcFormatter: "money", bottomCalcFormatterParams: { precision: 2 } },
                ],
                orphanrooms: [
                    { title: "DocId", field: "docid", sorter: "string", width: 200 },
                    { title: "Room", field: "roomno", sorter: "string", width: 80 },
                    { title: "Category", field: "roomcat", sorter: "string", width: 90 },
                    { title: "GuestProf", field: "guestprof", sorter: "string", width: 90 },
                    { title: "Check-In", field: "chkindate", sorter: "string", width: 100 },
                    { title: "Departure", field: "depdate", sorter: "string", width: 100 },
                    { title: "Type", field: "type", sorter: "string", width: 70 },
                    { title: "Entry By", field: "u_name", sorter: "string", width: 100 },
                    { title: "Entry Date", field: "u_entdt", sorter: "string", width: 150 },
                ],
                folionoroom: [
                    { title: "Folio No", field: "folio_no", sorter: "number", width: 90 },
                    { title: "Folio DocId", field: "docid", sorter: "string", width: 200 },
                    { title: "Name", field: "name", sorter: "string", width: 180 },
                    { title: "Folio Date", field: "vdate", sorter: "string", width: 100 },
                    { title: "BookingDocid", field: "bookingdocid", sorter: "string", width: 180 },
                    { title: "Company", field: "Company", sorter: "string", width: 120 },
                ],
                cancelledfolio: [
                    { title: "Res No", field: "ResNo", sorter: "number", width: 90 },
                    { title: "Folio No", field: "folio_no", sorter: "number", width: 90 },
                    { title: "Name", field: "name", sorter: "string", width: 180 },
                    { title: "Folio Date", field: "FolioDate", sorter: "string", width: 100 },
                    { title: "Cancel Date", field: "CancelDate", sorter: "string", width: 100 },
                    { title: "Cancelled By", field: "CancelUName", sorter: "string", width: 120 },
                ],
                resvfolio: [
                    { title: "Res No", field: "ResNo", sorter: "number", width: 80 },
                    { title: "Guest", field: "GuestName", sorter: "string", width: 150 },
                    { title: "Folio", field: "FolioNo", sorter: "number", width: 70 },
                    { title: "Occ Room", field: "OccRoom", sorter: "string", width: 80 },
                    { title: "Booked Room", field: "BookedRoom", sorter: "string", width: 90 },
                    { title: "Room?", field: "RoomMismatch", sorter: "string", width: 60,
                        formatter: "lookup", formatterParams: { "Y": "<span class='badge-flag'>ROOM</span>" } },
                    { title: "Occ Cat", field: "OccCat", sorter: "string", width: 80 },
                    { title: "Booked Cat", field: "BookedCat", sorter: "string", width: 90 },
                    { title: "Cat?", field: "CatMismatch", sorter: "string", width: 55,
                        formatter: "lookup", formatterParams: { "Y": "<span class='badge-flag'>CAT</span>" } },
                    { title: "Occ Rate", field: "OccRate", sorter: "number", width: 90 },
                    { title: "Booked Rate", field: "BookedRate", sorter: "number", width: 95 },
                    { title: "Rate?", field: "RateMismatch", sorter: "string", width: 60,
                        formatter: "lookup", formatterParams: { "Y": "<span class='badge-flag'>RATE</span>" } },
                    { title: "Occ Plan", field: "OccPlan", sorter: "string", width: 85 },
                    { title: "Booked Plan", field: "BookedPlan", sorter: "string", width: 95 },
                    { title: "Plan?", field: "PlanMismatch", sorter: "string", width: 55,
                        formatter: "lookup", formatterParams: { "Y": "<span class='badge-flag'>PLAN</span>" } },
                    { title: "Occ Dep", field: "OccOutDate", sorter: "string", width: 100 },
                    { title: "Booked Dep", field: "DepDate", sorter: "string", width: 100 },
                    { title: "Dep?", field: "DepMismatch", sorter: "string", width: 55,
                        formatter: "lookup", formatterParams: { "Y": "<span class='badge-flag'>DEP</span>" } },
                    { title: "Carry?", field: "CarryMismatch", sorter: "string", width: 60,
                        formatter: "lookup", formatterParams: { "Y": "<span class='badge-flag'>CARRY</span>" } },
                ],
                settlement: [
                    { title: "Folio No", field: "FolioNo", sorter: "number", width: 90 },
                    { title: "Name", field: "name", sorter: "string", width: 180 },
                    { title: "Room", field: "roomno", sorter: "string", width: 80 },
                    { title: "Check-Out", field: "chkoutdate", sorter: "string", width: 100 },
                    { title: "Open Balance", field: "OpenBalance", sorter: "number", width: 130,
                        formatter: "money", formatterParams: { precision: 2 },
                        bottomCalc: "sum", bottomCalcFormatter: "money", bottomCalcFormatterParams: { precision: 2 } },
                ],
            };

            function loadTab(tab) {
                currentTab = tab;
                $('.diag-tab').removeClass('active');
                $('.diag-tab[data-tab="' + tab + '"]').addClass('active');
                $.ajax({
                    url: '/fodiagnosticsfetch',
                    method: 'POST',
                    data: { tab: tab, _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (!res.status) return;
                        let data = res.data || [];
                        if (table) table.destroy();
                        table = new Tabulator("#diag-table", {
                            data: data,
                            columns: columnSets[tab] || [],
                            layout: "fitColumns",
                            pagination: "local",
                            paginationSize: 100,
                            tooltips: true,
                        });
                    },
                    error: function() {
                        if (table) table.destroy();
                        table = new Tabulator("#diag-table", { data: [], columns: [], layout: "fitColumns" });
                    }
                });
            }

            $(document).on('click', '.diag-tab', function() {
                loadTab($(this).data('tab'));
            });

            $("#download-xlsx").on("click", function() {
                if (table) table.download("xlsx", "fo_diagnostics_" + currentTab + ".xlsx", {
                    sheetName: "Diagnostics"
                });
            });

            $("#print-table").on("click", function() {
                if (table) table.print(false, true);
            });

            loadTab('noshow');
        });
    </script>
@endsection
