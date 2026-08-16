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
                                <p style="margin-top:-10px; font-size:16px;">Room Management Reconciliation
                                    ({{ date('d-m-Y', strtotime($ncurdate)) }})</p>
                            </div>
                            <div class="d-flex flex-wrap mt-3">
                                <button class="diag-tab active" data-tab="orphanocc">Orphan Occupancy</button>
                                <button class="diag-tab" data-tab="noroominmast">Room w/o Master</button>
                                <button class="diag-tab" data-tab="folionoroom">Folio w/o Room</button>
                                <button class="diag-tab" data-tab="nopaycharge">Occupied w/o Charges</button>
                                <button class="diag-tab" data-tab="occstat">Occupied Status</button>
                                <button class="diag-tab" data-tab="blockedoccupied">Blocked + Occupied</button>
                                <button class="diag-tab" data-tab="staleblock">Stale Blocks</button>
                                <button class="diag-tab" data-tab="extrabed">Extra Bed</button>
                            </div>
                            <div class="mt-3">
                                <button id="download-xlsx" class="btn btn-success">Excel <i
                                        class="fa fa-file-excel-o"></i></button>
                                <button id="print-table" class="btn btn-primary">Print <i
                                        class="fa-solid fa-print"></i></button>
                                <span class="text-muted ms-2"><i class="fa-solid fa-circle-info"></i> Read-only
                                    diagnostic — no data is modified.</span>
                            </div>
                            <div class="mt-3" id="recon-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let table = null;
            let currentTab = 'orphanocc';

            const columnSets = {
                orphanocc: [
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
                noroominmast: [
                    { title: "DocId", field: "docid", sorter: "string", width: 200 },
                    { title: "Room", field: "roomno", sorter: "string", width: 90 },
                    { title: "Category", field: "roomcat", sorter: "string", width: 90 },
                    { title: "Type", field: "type", sorter: "string", width: 70 },
                    { title: "Check-In", field: "chkindate", sorter: "string", width: 100 },
                    { title: "Departure", field: "depdate", sorter: "string", width: 100 },
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
                nopaycharge: [
                    { title: "DocId", field: "docid", sorter: "string", width: 200 },
                    { title: "Room", field: "roomno", sorter: "string", width: 90 },
                    { title: "GuestProf", field: "guestprof", sorter: "string", width: 90 },
                    { title: "Check-In", field: "chkindate", sorter: "string", width: 100 },
                    { title: "Departure", field: "depdate", sorter: "string", width: 100 },
                ],
                occstat: [
                    { title: "Room No", field: "RoomNo", sorter: "string", width: 90 },
                    { title: "Room Name", field: "RoomName", sorter: "string", width: 160 },
                    { title: "Master Status", field: "MastStat", sorter: "string", width: 100 },
                    { title: "DocId", field: "docid", sorter: "string", width: 200 },
                    { title: "Check-In", field: "chkindate", sorter: "string", width: 100 },
                    { title: "Departure", field: "depdate", sorter: "string", width: 100 },
                ],
                blockedoccupied: [
                    { title: "Room No", field: "RoomNo", sorter: "string", width: 90 },
                    { title: "Block", field: "block", sorter: "string", width: 120 },
                    { title: "Reason", field: "reasons", sorter: "string", width: 180 },
                    { title: "From", field: "fromdate", sorter: "string", width: 100 },
                    { title: "To", field: "todate", sorter: "string", width: 100 },
                    { title: "DocId", field: "docid", sorter: "string", width: 200 },
                    { title: "Check-In", field: "chkindate", sorter: "string", width: 100 },
                    { title: "Departure", field: "depdate", sorter: "string", width: 100 },
                ],
                staleblock: [
                    { title: "Room No", field: "RoomNo", sorter: "string", width: 90 },
                    { title: "Block", field: "block", sorter: "string", width: 120 },
                    { title: "Reason", field: "reasons", sorter: "string", width: 180 },
                    { title: "Type", field: "type", sorter: "string", width: 70 },
                    { title: "From", field: "fromdate", sorter: "string", width: 100 },
                    { title: "To", field: "todate", sorter: "string", width: 100 },
                    { title: "Entry By", field: "u_name", sorter: "string", width: 100 },
                    { title: "Entry Date", field: "u_entdt", sorter: "string", width: 150 },
                ],
                extrabed: [
                    { title: "DocId", field: "docid", sorter: "string", width: 200 },
                    { title: "Room", field: "roomno", sorter: "string", width: 90 },
                    { title: "Extra Bed", field: "extrabed", sorter: "number", width: 90 },
                    { title: "Adult", field: "adult", sorter: "number", width: 80 },
                    { title: "Children", field: "children", sorter: "number", width: 90 },
                    { title: "Check-In", field: "chkindate", sorter: "string", width: 100 },
                    { title: "Departure", field: "depdate", sorter: "string", width: 100 },
                    { title: "Entry Date", field: "u_entdt", sorter: "string", width: 150 },
                ],
            };

            function loadTab(tab) {
                currentTab = tab;
                $('.diag-tab').removeClass('active');
                $('.diag-tab[data-tab="' + tab + '"]').addClass('active');
                $.ajax({
                    url: '/roomreconfetch',
                    method: 'POST',
                    data: { tab: tab, _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (!res.status) return;
                        let data = res.data || [];
                        if (table) table.destroy();
                        table = new Tabulator("#recon-table", {
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
                        table = new Tabulator("#recon-table", { data: [], columns: [], layout: "fitColumns" });
                    }
                });
            }

            $(document).on('click', '.diag-tab', function() {
                loadTab($(this).data('tab'));
            });

            $("#download-xlsx").on("click", function() {
                if (table) table.download("xlsx", "room_recon_" + currentTab + ".xlsx", { sheetName: "Recon" });
            });

            $("#print-table").on("click", function() {
                if (table) table.print(false, true);
            });

            loadTab('orphanocc');
        });
    </script>
@endsection
