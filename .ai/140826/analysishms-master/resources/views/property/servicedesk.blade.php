@extends('property.layouts.main')

@section('main-container')
    <style>
        .pos-wrapper {
            width: 100%;
            overflow-x: auto;
            background-color: #dbe2ef;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .pos-container {
            font-size: 0.88rem;
            min-width: 1150px;
        }

        .pos-card {
            background: #ffffff;
            border: 1px solid #b8c4d1;
            border-radius: 4px;
        }

        /* Exact Navy Blue Header */
        .pos-header-navy {
            background-color: #031b4e;
            color: #ffffff;
            padding: 6px 12px;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .form-control-sm,
        .form-select-sm {
            font-size: 0.85rem !important;
            padding: 0.2rem 0.4rem !important;
            border: 1px solid #a0aec0;
            border-radius: 3px;
            height: 28px;
        }

        .pos-label {
            font-weight: 700;
            color: #1a202c;
            font-size: 0.83rem;
            white-space: nowrap;
            margin-bottom: 0;
        }

        .category-container-wrapper {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .category-scroll-body {
            max-height: 430px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .category-scroll-body::-webkit-scrollbar {
            width: 4px;
        }

        .category-scroll-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
        }

        .category-arrow-btn {
            background-color: #f1f5f9;
            border: none;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            color: #334155;
            padding: 4px;
            text-align: center;
            cursor: pointer;
        }

        .category-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            border-radius: 4px;
            color: #1e293b;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            margin-bottom: 5px;
            transition: all 0.15s ease;
        }

        .category-btn.active,
        .category-btn:hover {
            background-color: #baebff;
            border-color: #38bdf8;
            color: #0369a1;
        }

        /* Menu Item Card Styling */
        .item-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #ffffff;
            padding: 6px;
            text-align: center;
            position: relative;
        }

        .item-card img {
            height: 75px;
            object-fit: cover;
            border-radius: 4px;
            width: 100%;
        }

        .item-card .item-title {
            font-weight: 700;
            font-size: 0.83rem;
            margin-top: 4px;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: left;
        }

        .item-card .item-price {
            color: #0044cc;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .item-card .add-btn {
            background-color: #16a34a;
            color: white;
            border: none;
            padding: 0px 8px;
            border-radius: 3px;
            font-size: 0.9rem;
            font-weight: bold;
            line-height: 1.4;
        }

        .item-card .add-btn:hover {
            background-color: #15803d;
        }

        /* Items grid scroll (matches group/category height) */
        .items-scroll-body {
            max-height: 430px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .items-scroll-body::-webkit-scrollbar {
            width: 4px;
        }

        .items-scroll-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
        }

        /* Order Table */
        .order-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            font-size: 0.82rem;
            border-color: #cbd5e1;
            padding: 5px;
        }

        .order-table td {
            padding: 5px;
            font-size: 0.85rem;
            border-color: #e2e8f0;
        }

        .description-cell {
            color: #0d6efd;
            font-size: 0.78rem;
        }

        /* Current order table scroll (matches group/category height) */
        .order-scroll-body {
            max-height: 430px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .order-scroll-body::-webkit-scrollbar {
            width: 4px;
        }

        .order-scroll-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
        }

        .pos-bottom-btn {
            border-radius: 3px;
            font-weight: 700;
            padding: 6px 2px;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            border: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .pos-bottom-btn small {
            font-size: 0.7rem;
            font-weight: normal;
            margin-top: 1px;
        }

        /* Exact Button Colors */
        .btn-pos-new {
            background-color: #0284c7;
        }

        .btn-pos-save {
            background-color: #16a34a;
        }

        .btn-pos-print {
            background-color: #ea580c;
        }

        .btn-pos-hold {
            background-color: #eab308;
            color: #000;
        }

        .btn-pos-recall {
            background-color: #0284c7;
        }

        .btn-pos-merge {
            background-color: #6b21a8;
        }

        .btn-pos-split {
            background-color: #1e3a8a;
        }

        .btn-pos-transfer {
            background-color: #0891b2;
        }

        .btn-pos-bill {
            background-color: #16a34a;
        }

        .btn-pos-cancel {
            background-color: #dc2626;
        }

        .btn-pos-exit {
            background-color: #475569;
        }

        /* Totals Panel */
        .totals-panel {
            font-size: 0.85rem;
            background-color: #ffffff;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }
    </style>


    <div class="pos-wrapper p-2">
        <div class="content-body pos-container">

            <form id="servicedeskform" name="servicedeskform" method="POST">
                @csrf
                <input type="hidden" name="fixrestcode" id="fixrestcode" value="{{ $depart->dcode ?? '' }}">
                <input type="hidden" name="restcode" id="restcode" value="{{ $depart->dcode ?? '' }}">
                <input type="hidden" name="shortname" id="shortname" value="{{ $shortname }}">
                <input type="hidden" name="totalitems" id="totalitems" value="0">
                <input type="hidden" name="olddocidpendingkot" id="olddocidpendingkot" value="">
                <input type="hidden" name="nckotreason" id="nckotreason" value="">
                <input type="hidden" name="editingreasons" id="editingreasons" value="">
                <input type="hidden" name="ncurdate" id="ncurdate" value="{{ $ncurdate ?? now()->format('Y-m-d') }}">
                <input type="hidden" name="phoneno" id="phoneno">
                <input type="hidden" name="customername" id="customername">
                <input type="hidden" name="address" id="address">
                <input type="hidden" name="customercity" id="customercity">
                <input type="hidden" name="like" id="like">
                <input type="hidden" name="dislike" id="dislike">
                <input type="hidden" name="birthdate" id="birthdate">
                <input type="hidden" name="anniversary" id="anniversary">

                <div class="modal fade" id="customerModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Customer Information</h5>
                                <button type="button" class="close modalclosebtn" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group row">
                                    <label for="modal_phoneno" class="col-sm-4 col-form-label">Phone No</label>
                                    <div class="col-sm-8">
                                        <input type="text" autocomplete="off" class="form-control" id="modal_phoneno"
                                            placeholder="Enter phone number">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="modal_customername" class="col-sm-4 col-form-label">Customer Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" autocomplete="off" class="form-control"
                                            id="modal_customername" placeholder="Enter customer name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="modal_address" class="col-sm-4 col-form-label">Address</label>
                                    <div class="col-sm-8">
                                        <input type="text" autocomplete="off" class="form-control" id="modal_address"
                                            placeholder="Enter address">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="modal_customercity" class="col-sm-4 col-form-label">City</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" id="modal_customercity">
                                            <option value="">Select</option>
                                            @foreach ($citydata ?? [] as $item)
                                                <option value="{{ $item->city_code }}">{{ $item->cityname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="modal_like" class="col-sm-4 col-form-label">Like</label>
                                    <div class="col-sm-8">
                                        <input type="text" autocomplete="off" class="form-control" id="modal_like"
                                            placeholder="Enter like">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="modal_dislike" class="col-sm-4 col-form-label">Dislike</label>
                                    <div class="col-sm-8">
                                        <input type="text" autocomplete="off" class="form-control" id="modal_dislike"
                                            placeholder="Enter dislike">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="modal_birthdate" class="col-sm-4 col-form-label">Birth Date</label>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control" id="modal_birthdate">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="modal_anniversary" class="col-sm-4 col-form-label">Anniversary</label>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control" id="modal_anniversary">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="customerdetailsave" class="btn btn-success">Save</button>
                                <button type="button" class="btn btn-secondary modalclosebtn"
                                    data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Merge Table Modal -->
                <div class="modal fade" id="mergeTableModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Merge Table</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p class="small text-muted">Select table(s) with pending KOT to merge into the
                                    current bill for <strong id="mergeCurrentTable"></strong>.</p>
                                <div id="mergeTableList" class="list-group" style="max-height: 300px; overflow-y: auto;">
                                    <div class="text-muted small p-2">No other pending tables found.</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="mergeTableConfirm" class="btn btn-success">Merge
                                    Selected</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Split Bill Modal -->
                <div class="modal fade" id="splitBillModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Split Bill</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p class="small text-muted">Select items to move into a separate bill. Remaining
                                    items stay in the current order.</p>
                                <div id="splitItemList" class="list-group" style="max-height: 300px; overflow-y: auto;">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="splitBillConfirm" class="btn btn-warning">Create Split
                                    Bill</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KOT Print Preview Modal -->
                <div class="modal fade" id="kotPreviewModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">KOT Print Preview — KOT No: <span id="previewKotNo"></span>
                                </h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-2"><strong>Table:</strong> <span id="previewTable"></span>
                                    &nbsp;&nbsp; <strong>Waiter:</strong> <span id="previewWaiter"></span></p>
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Qty</th>
                                            <th>Note</th>
                                            <th>Void</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewItemsBody"></tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="kotPreviewConfirm" class="btn btn-success">Confirm &amp;
                                    Print</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recall Held Orders Modal -->
                <div class="modal fade" id="recallOrderModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Recall Held Orders</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div id="recallOrderList" class="list-group" style="max-height:320px; overflow-y:auto;">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Details Form Section -->
                <div class="pos-card p-2 mb-2">
                    <div class="row g-2 align-items-center mb-1">
                        <div class="col-md-3 d-flex align-items-center gap-2">
                            <label class="pos-label">Outlet</label>
                            <select class="form-select form-select-sm fw-bold" id="outlet_id" name="outlet_id">
                                @foreach ($outlets as $outlet)
                                    <option value="{{ $outlet->dcode }}" {{ $dcode == $outlet->dcode ? 'selected' : '' }}>
                                        {{ $outlet->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @php
                            $isRoomServiceOutlet = strtolower($depart->nature ?? '') == 'room service';
                        @endphp
                        <div class="col-md-5 d-flex align-items-center gap-3">
                            <span class="pos-label">Order Type</span>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="orderType" id="dineIn"
                                    value="Dine In" {{ $isRoomServiceOutlet ? 'disabled' : 'checked' }}>
                                <label class="form-check-label fw-bold" for="dineIn">Dine In</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="orderType" id="takeAway"
                                    value="Take Away" {{ $isRoomServiceOutlet ? 'disabled' : '' }}>
                                <label class="form-check-label fw-bold" for="takeAway">Take Away</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="orderType" id="delivery"
                                    value="Delivery" {{ $isRoomServiceOutlet ? 'disabled' : '' }}>
                                <label class="form-check-label fw-bold" for="delivery">Delivery</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="orderType" id="roomService"
                                    value="Room Service" {{ $isRoomServiceOutlet ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="roomService">Room Service</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center border p-1 rounded bg-light"
                                style="white-space: nowrap;">
                                <span class="fw-bold text-dark me-2">KOT No.</span>
                                <span class="fw-bold text-primary fs-6" id="krsno">{{ $kot_no ?? '' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center">
                        <div class="col-md-2 d-flex align-items-center gap-2">
                            <label class="pos-label" id="tablelabel">{{ $label }}</label>
                            <select class="form-select form-select-sm fw-bold" name="roomno" id="roomno" required>
                                <option value="">{{ $label }}</option>
                                @foreach ($tables as $table)
                                    <option value="{{ $table->table_number }}">{{ $table->table_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-center gap-2">
                            <label class="pos-label">Pax</label>
                            <select class="form-select form-select-sm fw-bold" name="pax" id="pax" required>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>
                                        {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-center gap-2">
                            <label class="pos-label">Steward</label>
                            <select class="form-select form-select-sm" name="waiter" id="waiter" required>
                                <option value="">Select Waiter</option>
                                @foreach ($servermast as $item)
                                    <option value="{{ $item->scode }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-center gap-1">
                            <label class="pos-label mb-0" style="cursor:pointer; white-space: nowrap;">
                                <input type="checkbox" name="nctypecheckbox" id="showNcSelect"> NC
                            </label>
                            <select class="form-select form-select-sm" name="nctype" id="nctype" disabled
                                style="display:none;">
                                <option value="">NC Type</option>
                                @foreach ($nctype ?? [] as $item)
                                    <option value="{{ $item->ncode }}">{{ $item->nctype }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center border p-1 rounded bg-light"
                                style="white-space: nowrap;">
                                <span class="fw-bold text-dark me-2">Date / Time</span>
                                <span class="fw-bold text-dark" id="curtime">{{ date('d-m-Y  h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-1">
                        <div class="col-md-1">
                            <span id="guestdt" class="text-primary small fw-bold"></span>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-1">
                        <div class="col-md-4 d-flex align-items-center gap-2">
                            <button type="button" id="customerbutton" class="btn btn-sm btn-primary"
                                data-toggle="modal" data-target="#customerModal">
                                Customer
                            </button>
                            <span id="customerSummary" class="text-muted small"></span>
                        </div>
                        <div class="col-md-4 d-flex align-items-center gap-2">
                            <label class="pos-label">Company</label>
                            <select class="form-select form-select-sm" name="company" id="company">
                                <option value="">Company</option>
                                @foreach ($company ?? [] as $item)
                                    <option value="{{ $item->sub_code }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            <span id="compgst" class="text-muted small"></span>
                        </div>
                        <div class="col-md-4 d-flex align-items-center justify-content-end gap-2">
                            <span class="pos-label">Session:</span>
                            <span id="sessionmast" class="fw-bold text-dark"></span>
                        </div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-2">
                        <div class="pos-card category-container-wrapper">
                            <div class="pos-header-navy text-center text-uppercase">GROUP</div>
                            <div class="category-scroll-body p-2" id="categoryContainer">
                                <div class="category-btn active menugrpitem" data-value="favourite" id="favourite">⭐
                                    Favourite</div>
                                @foreach ($categories as $cat)
                                    <div class="category-btn menugrpitem" data-value="{{ $cat->code }}">
                                        {{ $cat->name }}</div>
                                @endforeach
                            </div>
                            <button type="button" class="category-arrow-btn" onclick="scrollCategory(80)">
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Menu Items Grid -->
                    <div class="col-md-5">
                        <div class="pos-card p-2 h-100">
                            <div class="d-flex gap-2 mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i
                                            class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                    <input type="text" class="form-control" id="searchname"
                                        placeholder="Enter Item Name">
                                </div>
                                <input type="text" class="form-control form-control-sm" id="searchbar"
                                    placeholder="Scan Barcode" style="max-width:150px;">
                            </div>
                            <div class="items-scroll-body">
                                <div class="row g-2" id="itemsGrid">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Order -->
                    <div class="col-md-5">
                        <div class="pos-card h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div
                                    class="pos-header-navy text-center text-uppercase d-flex justify-content-between align-items-center px-2">
                                    <span id="currentOrderTitle">CURRENT ORDER</span>
                                    <span id="kottype" class="text-warning" style="font-size:0.75rem;">Standard
                                        KOT</span>
                                </div>
                                <div id="billSuccessPanel" class="alert alert-success d-none mb-0 py-2 px-2 rounded-0"
                                    role="alert">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span id="billSuccessMsg" class="small fw-bold"></span>
                                        <div class="d-flex gap-1">
                                            <button type="button" id="btnBillPrintNow"
                                                class="btn btn-sm btn-success py-0">🖨 Bill Print</button>
                                            <button type="button" id="btnBillNewOrder"
                                                class="btn btn-sm btn-secondary py-0">New Order</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-scroll-body">
                                    <table class="table table-bordered table-sm align-middle text-center mb-0 order-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 8%;">✕</th>
                                                <th class="text-start" style="width: 28%;">Item Name</th>
                                                <th style="width: 14%;">Qty</th>
                                                <th style="width: 10%;">Rate</th>
                                                <th style="width: 10%;">Amount</th>
                                                <th style="width: 18%;">Note</th>
                                                <th style="width: 12%;">Void</th>
                                            </tr>
                                        </thead>
                                        <tbody id="orderBody">

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="p-2 border-top totals-panel">
                                <div class="totals-row border-bottom pb-1">
                                    <span class="fw-bold text-dark">Total Qty</span>
                                    <span class="fw-bold text-primary fs-6" id="totalQty">0.00</span>
                                </div>
                                <div class="totals-row py-1">
                                    <span class="fw-bold text-dark">Gross Amount</span>
                                    <span class="fw-bold text-dark" id="grossAmount">0.00</span>
                                </div>
                                <div class="totals-row pt-2 align-items-center">
                                    <span class="fw-bold text-success fs-5">NET AMOUNT</span>
                                    <span class="fw-bold text-success fs-4" id="netAmount">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Remarks -->
                <div class="pos-card p-2 my-2">
                    <div class="row g-2">
                        <div class="col-md-6 d-flex align-items-center gap-2">
                            <label class="pos-label">Kot Remark</label>
                            <input type="text" class="form-control form-control-sm" name="kotremark" id="kotremark">
                        </div>
                    </div>
                </div>

                <div class="row g-1 text-center">
                    <div class="col"><button type="button" id="btnNew"
                            class="btn btn-pos-new w-100 pos-bottom-btn"><i class="fa-solid fa-file-lines mb-1"></i>
                            New</button></div>
                    <div class="col"><button type="button" id="btnSave"
                            class="btn btn-pos-save w-100 pos-bottom-btn"><i class="fa-solid fa-floppy-disk mb-1"></i>
                            Save KOT<small>F6</small></button></div>
                    <div class="col"><button type="button" id="btnPrint"
                            class="btn btn-pos-print w-100 pos-bottom-btn"><i class="fa-solid fa-print mb-1"></i> Print
                            KOT<small>F7</small></button></div>
                    <div class="col"><button type="button" id="btnHold"
                            class="btn btn-pos-hold w-100 pos-bottom-btn"><i class="fa-solid fa-pause mb-1"></i>
                            Hold<small>F9</small></button>
                    </div>
                    <div class="col"><button type="button" id="btnRecall"
                            class="btn btn-pos-recall w-100 pos-bottom-btn position-relative"><i
                                class="fa-solid fa-rotate mb-1"></i> Recall<span id="recallCount"
                                class="badge bg-danger position-absolute"
                                style="top:-5px; right:-5px; display:none;">0</span></button></div>
                    <div class="col"><button type="button" id="btnMerge"
                            class="btn btn-pos-merge w-100 pos-bottom-btn"><i class="fa-solid fa-code-merge mb-1"></i>
                            Merge Table</button></div>
                    <div class="col"><button type="button" id="btnSplit"
                            class="btn btn-pos-split w-100 pos-bottom-btn"><i class="fa-solid fa-border-all mb-1"></i>
                            Split Bill</button></div>
                    <div class="col"><a href="{{ url('kottransfer') }}?dcode={{ $dcode }}" target="_blank"
                            rel="noopener" class="btn btn-pos-transfer w-100 pos-bottom-btn"><i
                                class="fa-solid fa-arrow-right-arrow-left mb-1"></i> Transfer</a></div>
                    <div class="col"><button type="button" id="btnBill"
                            class="btn btn-pos-bill w-100 pos-bottom-btn"><i class="fa-solid fa-receipt mb-1"></i>
                            Bill<small>F8</small></button></div>
                    <div class="col"><button type="button" id="btnCancel"
                            class="btn btn-pos-cancel w-100 pos-bottom-btn"><i class="fa-solid fa-xmark mb-1"></i>
                            Cancel</button></div>
                    <div class="col"><a href="{{ url('/company') }}" class="btn btn-pos-exit w-100 pos-bottom-btn"><i
                                class="fa-solid fa-door-open mb-1"></i> Exit<small>Esc</small></a></div>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mt-2 px-3 py-1 bg-dark text-white rounded">
                <div class="fw-semibold small">
                    <span class="me-3">F6 - Save</span>
                    <span class="me-3">F7 - Print</span>
                    <span class="me-3">F8 - Bill</span>
                    <span>Esc - Exit</span>
                </div>
                <div class="fw-bold small text-light">Powered By : Analysis Software Services</div>
            </div>
        </div>
    </div>

    <script>
        function scrollCategory(amount) {
            document.getElementById('categoryContainer').scrollBy({
                top: amount,
                behavior: 'smooth'
            });
        }

        $(document).ready(function() {
            let totaladditems = 0;
            let mergedKotDocids = [];
            let lastBillPrintData = null;
            const token = '{{ csrf_token() }}';

            // ---------- Pax + Guest detail auto-fill on room/table selection ----------
            $(document).on('change', '#roomno', function() {
                let roomno = $(this).val();
                if (!roomno) {
                    $('#guestdt').text('');
                    return;
                }
                $.ajax({
                    url: '/guestdtfetchkot',
                    method: 'POST',
                    data: {
                        roomno: roomno,
                        _token: token
                    },
                    success: function(results) {
                        if (results && results.pax) {
                            $('#pax').val(results.pax);
                        }
                        $('#guestdt').text(results && results.concat ? results.concat : '');
                    },
                    error: function() {
                        $('#guestdt').text('');
                    }
                });
            });

            // ---------- Company -> fetch GST ----------
            $(document).on('change', '#company', function() {
                let sub_code = $(this).val();
                if (!sub_code) {
                    $('#compgst').text('');
                    return;
                }
                $.ajax({
                    url: '/fetchcompdetail',
                    method: 'POST',
                    data: {
                        sub_code: sub_code,
                        _token: token
                    },
                    success: function(results) {
                        $('#compgst').text(results ? results : '');
                    }
                });
            });

            // ---------- Session + live clock ----------
            function updateSessionAndClock() {
                let options = {
                    timeZone: 'Asia/Kolkata',
                    hour12: false,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                let currentTime = new Date().toLocaleString('en-US', options);
                $.ajax({
                    url: '/getsessionmast',
                    method: 'POST',
                    data: {
                        curtime: currentTime,
                        _token: token
                    },
                    success: function(data) {
                        $('#sessionmast').text(data || '');
                    }
                });
            }
            updateSessionAndClock();
            setInterval(updateSessionAndClock, 60000);

            // ---------- NC checkbox toggle ----------
            $('#showNcSelect').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#nctype').show().prop('disabled', false).attr('required', true);
                    $('#kottype').text('NC KOT');
                } else {
                    $('#nctype').hide().prop('disabled', true).removeAttr('required').val('');
                    $('#kottype').text('Standard KOT');
                }
            });

            // ---------- Customer modal: phone lookup ----------
            function toISODate(dmy) {
                if (!dmy) return '';
                let parts = dmy.split('-');
                if (parts.length !== 3) return '';
                return `${parts[2]}-${parts[1]}-${parts[0]}`;
            }

            let customerPhoneTimer;
            $(document).on('input', '#modal_phoneno', function() {
                let phoneno = $(this).val().trim();
                clearTimeout(customerPhoneTimer);
                if (phoneno.length !== 10) return;
                customerPhoneTimer = setTimeout(() => {
                    $.ajax({
                        url: '/phonefindxhr',
                        method: 'POST',
                        data: {
                            phoneno: phoneno,
                            _token: token
                        },
                        success: function(result) {
                            if (result !== 'Not Found' && result.data && result.data
                                .length > 0) {
                                let tdata = result.data[0];
                                $('#modal_customername').val(tdata.customername || '');
                                $('#modal_address').val(tdata.add1 || '');
                                $('#modal_customercity').val(tdata.city || '');
                                $('#modal_like').val(tdata.likes || '');
                                $('#modal_dislike').val(tdata.dislikes || '');
                                $('#modal_birthdate').val(toISODate(tdata.dob));
                                $('#modal_anniversary').val(toISODate(tdata
                                    .anniversary));
                            }
                        }
                    });
                }, 400);
            });

            // ---------- Customer modal: save ----------
            $('#customerdetailsave').on('click', function() {
                $('#phoneno').val($('#modal_phoneno').val());
                $('#customername').val($('#modal_customername').val());
                $('#address').val($('#modal_address').val());
                $('#customercity').val($('#modal_customercity').val());
                $('#like').val($('#modal_like').val());
                $('#dislike').val($('#modal_dislike').val());
                $('#birthdate').val($('#modal_birthdate').val());
                $('#anniversary').val($('#modal_anniversary').val());

                let name = $('#modal_customername').val();
                let phone = $('#modal_phoneno').val();
                let summary = [name, phone ? `(${phone})` : ''].filter(Boolean).join(' ');
                $('#customerSummary').text(summary);

                $('#customerModal').modal('hide');
            });

            // ---------- Item image helpers ----------
            const itemPlaceholderImage = "{{ asset('admin/icons/custom/60x60.svg') }}";
            const itemStorageBase = "{{ asset('storage/property/itempicture') }}";
            const itemFallbackImage = "{{ url('assets/img/100_90.svg') }}";

            function getItemImageSrc(item) {
                if (!item.iempic) return itemPlaceholderImage;
                return `${itemStorageBase}/${item.iempic}`;
            }

            // ---------- If outlet is Room Service, fetch correct room list on load ----------
            if ($('#roomService').is(':checked')) {
                $('input[name="orderType"]:checked').trigger('change');
            }

            // ---------- Outlet switch -> fetch new outlet data via AJAX (no page reload) ----------
            $('#outlet_id').on('change', function() {
                let dcode = $(this).val();

                $.ajax({
                    url: '/switchoutletxhr',
                    method: 'POST',
                    data: {
                        dcode: dcode,
                        _token: token
                    },
                    success: function(res) {
                        // update hidden fields
                        $('#fixrestcode, #restcode').val(res.dcode);
                        $('#shortname').val(res.shortname);
                        $('#krsno').text(res.kot_no);
                        $('#tablelabel').text(res.label);

                        // reset & lock order type radios based on outlet nature
                        $('input[name="orderType"]').prop('disabled', false);
                        if (res.isRoomService) {
                            $('#roomService').prop('checked', true);
                            $('#dineIn, #takeAway, #delivery').prop('disabled', true);
                        } else {
                            $('#dineIn').prop('checked', true);
                        }

                        // update table/room dropdown
                        let sel = $('#roomno');
                        sel.empty().append(`<option value="">${res.label}</option>`);
                        res.tables.forEach(function(item) {
                            let guestTxt = item.guest ? ` (${item.guest})` : '';
                            sel.append(
                                `<option value="${item.table_number}">${item.table_number}${guestTxt}</option>`
                            );
                        });

                        // update group/category list
                        let catContainer = $('#categoryContainer');
                        catContainer.empty();
                        catContainer.append(
                            `<div class="category-btn active menugrpitem" data-value="favourite" id="favourite">⭐ Favourite</div>`
                        );
                        res.categories.forEach(function(cat) {
                            catContainer.append(
                                `<div class="category-btn menugrpitem" data-value="${cat.code}">${cat.name}</div>`
                            );
                        });

                        // clear current order & search fields
                        resetOrderForm();
                        $('#searchname, #searchbar').val('');
                        $('#itemsGrid').empty();

                        // load items for the new outlet's favourite group
                        $('#favourite').trigger('click');
                    },
                    error: function() {
                        pushNotify('error', 'Service Desk', 'Unable to switch outlet', 'fade',
                            300, '',
                            '', true, true, true, 3000, 20, 20, 'outline', 'right top');
                    }
                });
            });

            // ---------- Order Type -> re-fetch table/room list for the new order type ----------
            $('input[name="orderType"]').on('change', function() {
                let ordertype = $(this).val();
                let dcode = $('#restcode').val();
                $('#tablelabel').text(ordertype === 'Room Service' ? 'Room No.' : 'Table No.');

                $.ajax({
                    url: '/fetchtablesservicedesk',
                    method: 'POST',
                    data: {
                        dcode: dcode,
                        ordertype: ordertype,
                        _token: token
                    },
                    success: function(res) {
                        let sel = $('#roomno');
                        sel.empty().append(`<option value="">${res.label}</option>`);
                        res.data.forEach(function(item) {
                            let guestTxt = item.guest ? ` (${item.guest})` : '';
                            sel.append(
                                `<option value="${item.table_number}">${item.table_number}${guestTxt}</option>`
                            );
                        });
                    }
                });
            });

            // ---------- Category click -> items fetch ----------
            $(document).on('click', '.menugrpitem', function() {
                $('.menugrpitem').removeClass('active');
                $(this).addClass('active');
                let grpid = $(this).data('value');
                let dcode = $('#restcode').val();
                $('#searchname, #searchbar').val('');
                fetchItems(`grpid=${grpid}&dcode=${dcode}&_token=${token}`);
            });

            // ---------- Search (debounced) ----------
            let searchTimer;
            $('#searchname').on('input', function() {
                let val = $(this).val().trim();
                $('#searchbar').val('');
                clearTimeout(searchTimer);
                if (!val) return;
                searchTimer = setTimeout(() => {
                    fetchItems(
                        `name=${encodeURIComponent(val)}&dcode=${$('#restcode').val()}&_token=${token}`
                    );
                }, 350);
            });
            $('#searchbar').on('input', function() {
                let val = $(this).val().trim();
                $('#searchname').val('');
                clearTimeout(searchTimer);
                if (!val) return;
                searchTimer = setTimeout(() => {
                    fetchItems(
                        `barcodeinput=${encodeURIComponent(val)}&dcode=${$('#restcode').val()}&_token=${token}`
                    );
                }, 350);
            });


            function fetchItems(data) {
                $.ajax({
                    url: '/fetchitemnames',
                    method: 'POST',
                    data: data,
                    success: function(results) {
                        let grid = $('#itemsGrid');
                        grid.empty();
                        results.forEach(function(item) {
                            let imgSrc = getItemImageSrc(item);
                            grid.append(`
                                <div class="col-4">
                                    <div class="item-card tditemname" data-value="${item.Code}" data-id="${item.rateofitem}" data-itemrestcode="${item.RestCode}">
                                        <img src="${imgSrc}" alt="${item.Name}" onerror="this.onerror=null;this.src='${itemFallbackImage}';">
                                        <div class="item-title">${item.Name}</div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="item-price">${parseFloat(item.rateofitem || 0).toFixed(2)}</span>
                                            <button type="button" class="add-btn">+</button>
                                        </div>
                                    </div>
                                </div>`);
                        });
                    }
                });
            }

            function addOrderItem(itemcode, itemrate, itemrestcode, itemname, qty = 1, description = '') {
                itemcode = String(itemcode);
                itemrate = parseFloat(itemrate) || 0;
                qty = parseInt(qty) || 1;

                let existing = $('#orderBody tr').filter(function() {
                    return String($(this).find('input[name^="itemcode"]').val()) === itemcode;
                });

                if (existing.length) {
                    let qtyInput = existing.find('.qtyitem');
                    qtyInput.val(parseInt(qtyInput.val()) + qty).trigger('change');
                } else {
                    totaladditems++;
                    let idx = totaladditems;
                    let row = `<tr>
                        <td><button type="button" class="btn btn-sm btn-outline-danger removeItem">✕</button></td>
                        <td class="text-start fw-bold">${itemname}</td>
                        <td>
                            <input type="hidden" name="itemcode${idx}" value="${itemcode}">
                            <input type="hidden" name="itemrestcode${idx}" value="${itemrestcode}">
                            <input type="hidden" name="itemname${idx}" value="${itemname}">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary decrementRow px-2 py-0">-</button>
                                <input type="text" class="form-control form-control-sm qtyitem text-center" name="quantity${idx}" value="${qty}" style="width:44px;">
                                <button type="button" class="btn btn-sm btn-outline-secondary incrementRow px-2 py-0">+</button>
                            </div>
                        </td>
                        <td><input type="text" class="form-control form-control-sm rateclass text-end" name="rate${idx}" value="${itemrate.toFixed(2)}" readonly></td>
                        <td class="fw-bold amount-cell">${(itemrate * qty).toFixed(2)}</td>
                        <td>
                            <input type="hidden" name="description${idx}" id="description${idx}" value="${description}">
                            <span class="text-primary small description-cell" style="cursor:pointer;text-decoration:underline;" data-idx="${idx}">${description ? description : '+ Note'}</span>
                        </td>
                        <td>
                            <input type="text" name="voidyn${idx}" id="voidyn${idx}" value="No" class="form-control form-control-sm voidyn text-center" readonly style="cursor:pointer;">
                        </td>
                    </tr>`;
                    $('#orderBody').append(row);
                    $('#totalitems').val(totaladditems);
                }
                updateTotals();
            }

            $(document).on('click', '.tditemname', function() {
                let itemcode = String($(this).data('value'));
                let itemrate = parseFloat($(this).data('id')) || 0;
                let itemrestcode = $(this).data('itemrestcode');
                let itemname = $(this).find('.item-title').text().trim();
                addOrderItem(itemcode, itemrate, itemrestcode, itemname, 1);
            });

            // ---------- Row-level qty increment/decrement ----------
            $(document).on('click', '.incrementRow', function() {
                let qtyInput = $(this).siblings('.qtyitem');
                let value = parseInt(qtyInput.val()) || 0;
                qtyInput.val(value + 1);
                updateTotals();
            });
            $(document).on('click', '.decrementRow', function() {
                let qtyInput = $(this).siblings('.qtyitem');
                let value = parseInt(qtyInput.val()) || 0;
                if (value > 1) {
                    qtyInput.val(value - 1);
                    updateTotals();
                }
            });

            // ---------- Void toggle ----------
            $(document).on('click', '.voidyn', function() {
                let input = $(this);
                input.val(input.val() === 'No' ? 'Yes' : 'No');
                updateTotals();
            });

            // ---------- Merge Table ----------
            $('#btnMerge').on('click', function() {
                let currentTable = $('#roomno').val();
                if (!currentTable) {
                    pushNotify('error', 'Merge Table', 'Please select the current table first', 'fade', 300,
                        '',
                        '', true, true, true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }
                $('#mergeCurrentTable').text(currentTable);
                let listDiv = $('#mergeTableList');
                listDiv.html('<div class="text-muted small p-2">Loading...</div>');

                $.ajax({
                    url: '/fetchpendingtables',
                    method: 'POST',
                    data: {
                        dcode: $('#restcode').val(),
                        excluderoomno: currentTable,
                        _token: token
                    },
                    success: function(tables) {
                        listDiv.empty();
                        if (!tables || tables.length === 0) {
                            listDiv.html(
                                '<div class="text-muted small p-2">No other pending tables found.</div>'
                            );
                            return;
                        }
                        tables.forEach(function(t) {
                            listDiv.append(`
                                <label class="list-group-item d-flex align-items-center gap-2" style="cursor:pointer;">
                                    <input type="checkbox" class="merge-table-checkbox" value="${t.roomno}">
                                    <span>Table ${t.roomno} — ${t.itemcount} item(s), KOT #${t.vno}</span>
                                </label>
                            `);
                        });
                    },
                    error: function() {
                        listDiv.html(
                            '<div class="text-danger small p-2">Unable to load pending tables.</div>'
                        );
                    }
                });

                $('#mergeTableModal').modal('show');
            });

            $('#mergeTableConfirm').on('click', function() {
                let selectedTables = [];
                $('.merge-table-checkbox:checked').each(function() {
                    selectedTables.push($(this).val());
                });
                if (selectedTables.length === 0) {
                    pushNotify('warning', 'Merge Table', 'Select at least one table to merge', 'fade', 300,
                        '',
                        '', true, true, true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }

                let requests = selectedTables.map(function(roomno) {
                    return $.ajax({
                        url: '/mergetableitems',
                        method: 'POST',
                        data: {
                            dcode: $('#restcode').val(),
                            roomno: roomno,
                            _token: token
                        }
                    });
                });

                $.when.apply($, requests).always(function() {
                    let responses = requests.length === 1 ? [arguments[0]] : arguments;
                    let mergedCount = 0;
                    Array.from(responses).forEach(function(res) {
                        let items = Array.isArray(res) ? res[0] : res;
                        if (!items || !items.length) return;
                        items.forEach(function(item) {
                            addOrderItem(item.itemcode, item.rate, item
                                .itemrestcode, item.itemname,
                                item.quantity, item.description || '');
                            if (!mergedKotDocids.includes(item.docid)) {
                                mergedKotDocids.push(item.docid);
                            }
                        });
                        mergedCount++;
                    });
                    $('#mergeTableModal').modal('hide');
                    pushNotify('success', 'Merge Table', mergedCount +
                        ' table(s) merged into current bill',
                        'fade', 300, '', '', true, true, true, 3000, 20, 20, 'outline',
                        'right top');
                });
            });

            // ---------- Split Bill ----------
            $('#btnSplit').on('click', function() {
                if ($('#orderBody tr').length === 0) {
                    pushNotify('error', 'Split Bill', 'No items to split', 'fade', 300, '', '', true, true,
                        true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }
                let listDiv = $('#splitItemList');
                listDiv.empty();
                $('#orderBody tr').each(function() {
                    let itemname = $(this).find('input[name^="itemname"]').val();
                    let qty = $(this).find('.qtyitem').val();
                    let idx = $(this).find('input[name^="itemcode"]').attr('name').replace(
                        'itemcode', '');
                    listDiv.append(`
                        <label class="list-group-item d-flex align-items-center gap-2" style="cursor:pointer;">
                            <input type="checkbox" class="split-item-checkbox" data-row-idx="${idx}">
                            <span>${itemname} (Qty: ${qty})</span>
                        </label>
                    `);
                });
                $('#splitBillModal').modal('show');
            });

            $('#splitBillConfirm').on('click', function() {
                let selectedIdxs = [];
                $('.split-item-checkbox:checked').each(function() {
                    selectedIdxs.push($(this).data('row-idx').toString());
                });
                if (selectedIdxs.length === 0) {
                    pushNotify('warning', 'Split Bill', 'Select at least one item to split', 'fade', 300,
                        '',
                        '', true, true, true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }
                if (!$('#roomno').val() || !$('#waiter').val()) {
                    pushNotify('error', 'Split Bill', 'Please select Table/Room & Waiter first', 'fade',
                        300,
                        '', '', true, true, true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }

                $('#splitBillConfirm').prop('disabled', true).text('Processing...');

                let splitItems = [];
                let rowsToRemove = [];
                $('#orderBody tr').each(function() {
                    let idx = $(this).find('input[name^="itemcode"]').attr('name').replace(
                        'itemcode', '');
                    if (selectedIdxs.includes(idx)) {
                        splitItems.push({
                            itemcode: $(this).find('input[name^="itemcode"]').val(),
                            itemrestcode: $(this).find('input[name^="itemrestcode"]').val(),
                            itemname: $(this).find('input[name^="itemname"]').val(),
                            description: $(this).find('input[name^="description"]').val(),
                            quantity: $(this).find('.qtyitem').val(),
                            rate: $(this).find('.rateclass').val(),
                            amount: $(this).find('.amount-cell').text()
                        });
                        rowsToRemove.push($(this));
                    }
                });

                let restcode = $('#restcode').val();
                let splitFormData = {
                    _token: token,
                    fixrestcode: restcode,
                    restcode: restcode,
                    shortname: $('#shortname').val(),
                    olddocidpendingkot: '',
                    nckotreason: '',
                    editingreasons: '',
                    ncurdate: $('#ncurdate').val(),
                    roomno: $('#roomno').val(),
                    pax: $('#pax').val(),
                    waiter: $('#waiter').val(),
                    kotremark: $('#kotremark').val() + ' (Split Bill)',
                    totalitems: splitItems.length
                };
                splitItems.forEach(function(item, i) {
                    let idx = i + 1;
                    splitFormData['itemcode' + idx] = item.itemcode;
                    splitFormData['itemrestcode' + idx] = item.itemrestcode;
                    splitFormData['itemname' + idx] = item.itemname;
                    splitFormData['description' + idx] = item.description;
                    splitFormData['quantity' + idx] = item.quantity;
                    splitFormData['rate' + idx] = item.rate;
                    splitFormData['voidyn' + idx] = 'No';
                });

                $.ajax({
                    type: 'POST',
                    url: '/kotstore',
                    data: splitFormData,
                    success: function(kotResponse) {
                        if (kotResponse.status !== 'success') {
                            $('#splitBillConfirm').prop('disabled', false).text(
                                'Create Split Bill');
                            pushNotify('error', 'Split Bill', kotResponse.message, 'fade', 300,
                                '', '', true,
                                true, true, 3000, 20, 20, 'outline', 'right top');
                            return;
                        }
                        let kotdocid = Array.isArray(kotResponse.docid) ? kotResponse.docid
                            .join(',') :
                            kotResponse.docid;
                        let grossAmount = splitItems.reduce((sum, it) => sum + (parseFloat(it
                            .amount) || 0), 0);

                        let billData = {
                            _token: token,
                            fixrestcode: restcode,
                            restcode: restcode,
                            roomno: $('#roomno').val(),
                            pax: $('#pax').val(),
                            waiter: $('#waiter').val(),
                            waitersname: $('#waiter option:selected').text(),
                            kotdocid: kotdocid,
                            kotdocidfix: kotdocid,
                            kotno: kotResponse.docid,
                            vtype: 'B{{ $shortname }}',
                            vdatesale1: $('#ncurdate').val(),
                            totalitems: splitItems.length,
                            oldroomyn: 'N',
                            totalitemsum: grossAmount.toFixed(2)
                        };
                        splitItems.forEach(function(item, i) {
                            let idx = i + 1;
                            billData['itemcode' + idx] = item.itemcode;
                            billData['itemrestcode' + idx] = item.itemrestcode;
                            billData['itemname' + idx] = item.itemname;
                            billData['description' + idx] = item.description;
                            billData['quantity' + idx] = item.quantity;
                            billData['rate' + idx] = item.rate;
                            billData['amount' + idx] = item.amount;
                            billData['voidyn' + idx] = 'No';
                        });

                        $.ajax({
                            type: 'POST',
                            url: '{{ route('salebillsubmit') }}',
                            data: billData,
                            success: function() {
                                rowsToRemove.forEach(function($row) {
                                    $row.remove();
                                });
                                updateTotals();
                                $('#splitBillConfirm').prop('disabled', false).text(
                                    'Create Split Bill');
                                $('#splitBillModal').modal('hide');
                                pushNotify('success', 'Split Bill',
                                    'Split bill created successfully!',
                                    'fade', 300, '', '', true, true, true, 3000,
                                    20, 20, 'outline',
                                    'right top');
                            },
                            error: function() {
                                $('#splitBillConfirm').prop('disabled', false).text(
                                    'Create Split Bill');
                                pushNotify('error', 'Split Bill',
                                    'Split bill submit failed', 'fade', 300,
                                    '', '', true, true, true, 3000, 20, 20,
                                    'outline', 'right top');
                            }
                        });
                    },
                    error: function() {
                        $('#splitBillConfirm').prop('disabled', false).text(
                            'Create Split Bill');
                        pushNotify('error', 'Split Bill', 'Unable to save split KOT', 'fade',
                            300, '', '',
                            true, true, true, 3000, 20, 20, 'outline', 'right top');
                    }
                });
            });

            // ---------- Note / Description prompt per item ----------
            $(document).on('click', '.description-cell', function() {
                let idx = $(this).data('idx');
                let input = $(`#description${idx}`);
                let currentVal = input.val();
                let cell = $(this);

                Swal.fire({
                    title: 'Enter Description',
                    input: 'text',
                    inputValue: currentVal,
                    inputPlaceholder: 'Enter your value here',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: 'OK',
                    denyButtonText: 'Clear',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        input.val(result.value);
                        cell.text(result.value ? result.value : '+ Note');
                    } else if (result.isDenied) {
                        input.val('');
                        cell.text('+ Note');
                    }
                });
            });

            $(document).on('change input', '.qtyitem', updateTotals);

            $(document).on('click', '.removeItem', function() {
                $(this).closest('tr').remove();
                reindexRows();
                updateTotals();
            });

            function reindexRows() {
                let idx = 0;
                $('#orderBody tr').each(function() {
                    idx++;
                    $(this).find('.description-cell').data('idx', idx);
                    $(this).find('input, select').each(function() {
                        let name = $(this).attr('name');
                        let id = $(this).attr('id');
                        if (name) $(this).attr('name', name.replace(/\d+$/, idx));
                        if (id) $(this).attr('id', id.replace(/\d+$/, idx));
                    });
                });
                totaladditems = idx;
                $('#totalitems').val(totaladditems);
            }

            function updateTotals() {
                let totalQty = 0,
                    gross = 0;
                $('#orderBody tr').each(function() {
                    let qty = parseFloat($(this).find('.qtyitem').val()) || 0;
                    let rate = parseFloat($(this).find('.rateclass').val()) || 0;
                    let isVoid = $(this).find('.voidyn').val() === 'Yes';
                    let amt = qty * rate;
                    $(this).find('.amount-cell').text(amt.toFixed(2));
                    if (!isVoid) {
                        totalQty += qty;
                        gross += amt;
                    }
                });
                $('#totalQty').text(totalQty.toFixed(2));
                $('#grossAmount').text(gross.toFixed(2));
                $('#netAmount').text(gross.toFixed(
                    2
                ));
            }

            function resetOrderForm() {
                $('#orderBody').empty();
                totaladditems = 0;
                mergedKotDocids = [];
                lastBillPrintData = null;
                $('#totalitems').val(0);
                $('#olddocidpendingkot').val('');
                $('#phoneno, #customername, #address, #customercity, #like, #dislike, #birthdate, #anniversary')
                    .val('');
                $('#modal_phoneno, #modal_customername, #modal_address, #modal_customercity, #modal_like, #modal_dislike, #modal_birthdate, #modal_anniversary')
                    .val('');
                $('#customerSummary').text('');
                $('#company').val('');
                $('#compgst').text('');
                $('#guestdt').text('');
                $('#showNcSelect').prop('checked', false).trigger('change');
                $('#kotremark').val('');
                $('#pax').val(1);
                $('#waiter').val('');
                $('#roomno').val('');
                $('#billSuccessPanel').addClass('d-none');
                $('#currentOrderTitle').text('CURRENT ORDER');
                $('#orderBody :input').prop('disabled', false);
                updateTotals();
            }

            // ---------- Hold / Recall (stored locally per browser) ----------
            const HOLD_STORAGE_KEY = 'posHeldOrders';

            function getHeldOrders() {
                try {
                    return JSON.parse(localStorage.getItem(HOLD_STORAGE_KEY)) || [];
                } catch (e) {
                    return [];
                }
            }

            function saveHeldOrders(orders) {
                localStorage.setItem(HOLD_STORAGE_KEY, JSON.stringify(orders));
            }

            function updateHoldRecallBadge() {
                let held = getHeldOrders();
                if (held.length > 0) {
                    $('#recallCount').text(held.length).show();
                } else {
                    $('#recallCount').hide();
                }
            }
            updateHoldRecallBadge();

            $('#btnHold').on('click', function() {
                if ($('#orderBody tr').length === 0) {
                    pushNotify('error', 'Hold Order', 'No items to hold', 'fade', 300, '', '', true, true,
                        true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }
                if (!$('#roomno').val()) {
                    pushNotify('error', 'Hold Order', 'Please select Table/Room first', 'fade', 300, '', '',
                        true, true, true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }

                let items = [];
                $('#orderBody tr').each(function() {
                    items.push({
                        itemcode: $(this).find('input[name^="itemcode"]').val(),
                        itemrestcode: $(this).find('input[name^="itemrestcode"]').val(),
                        itemname: $(this).find('input[name^="itemname"]').val(),
                        description: $(this).find('input[name^="description"]').val(),
                        quantity: $(this).find('.qtyitem').val(),
                        rate: $(this).find('.rateclass').val()
                    });
                });

                let heldOrder = {
                    id: Date.now(),
                    dcode: $('#restcode').val(),
                    outletName: $('#outlet_id option:selected').text(),
                    roomno: $('#roomno').val(),
                    tableLabel: $('#tablelabel').text(),
                    pax: $('#pax').val(),
                    waiter: $('#waiter').val(),
                    kotremark: $('#kotremark').val(),
                    orderType: $('input[name="orderType"]:checked').val(),
                    phoneno: $('#phoneno').val(),
                    customername: $('#customername').val(),
                    address: $('#address').val(),
                    customercity: $('#customercity').val(),
                    like: $('#like').val(),
                    dislike: $('#dislike').val(),
                    birthdate: $('#birthdate').val(),
                    anniversary: $('#anniversary').val(),
                    customerSummary: $('#customerSummary').text(),
                    company: $('#company').val(),
                    nctypecheckbox: $('#showNcSelect').is(':checked'),
                    nctype: $('#nctype').val(),
                    items: items,
                    heldAt: new Date().toLocaleString()
                };

                let held = getHeldOrders();
                held.push(heldOrder);
                saveHeldOrders(held);
                updateHoldRecallBadge();

                resetOrderForm();
                pushNotify('success', 'Hold Order', 'Order held for Table ' + heldOrder.roomno, 'fade', 300,
                    '', '', true, true, true, 3000, 20, 20, 'outline', 'right top');
            });

            $('#btnRecall').on('click', function() {
                let held = getHeldOrders();
                let listDiv = $('#recallOrderList');
                listDiv.empty();
                if (held.length === 0) {
                    listDiv.html('<div class="text-muted small p-2">No held orders.</div>');
                } else {
                    held.forEach(function(order) {
                        listDiv.append(`
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${order.outletName}</strong> — ${order.tableLabel} ${order.roomno}<br>
                                    <span class="small text-muted">${order.items.length} item(s), held at ${order.heldAt}</span>
                                </div>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-success recall-order-btn" data-id="${order.id}">Recall</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-held-btn" data-id="${order.id}">✕</button>
                                </div>
                            </div>
                        `);
                    });
                }
                $('#recallOrderModal').modal('show');
            });

            $(document).on('click', '.delete-held-btn', function() {
                let id = parseInt($(this).data('id'));
                let held = getHeldOrders().filter(o => o.id !== id);
                saveHeldOrders(held);
                updateHoldRecallBadge();
                $(this).closest('.list-group-item').remove();
            });

            $(document).on('click', '.recall-order-btn', function() {
                let id = parseInt($(this).data('id'));
                let held = getHeldOrders();
                let order = held.find(o => o.id === id);
                if (!order) return;

                function restoreOrder() {
                    resetOrderForm();
                    $('#roomno').val(order.roomno);
                    $('#pax').val(order.pax);
                    $('#waiter').val(order.waiter);
                    $('#kotremark').val(order.kotremark);
                    $(`input[name="orderType"][value="${order.orderType}"]`).prop('checked', true);
                    $('#phoneno').val(order.phoneno);
                    $('#customername').val(order.customername);
                    $('#address').val(order.address);
                    $('#customercity').val(order.customercity);
                    $('#like').val(order.like);
                    $('#dislike').val(order.dislike);
                    $('#birthdate').val(order.birthdate);
                    $('#anniversary').val(order.anniversary);
                    $('#customerSummary').text(order.customerSummary);
                    $('#company').val(order.company);
                    if (order.nctypecheckbox) {
                        $('#showNcSelect').prop('checked', true).trigger('change');
                        $('#nctype').val(order.nctype);
                    }
                    order.items.forEach(function(item) {
                        addOrderItem(item.itemcode, item.rate, item.itemrestcode, item.itemname, item
                            .quantity, item.description);
                    });

                    let remaining = getHeldOrders().filter(o => o.id !== id);
                    saveHeldOrders(remaining);
                    updateHoldRecallBadge();
                    $('#recallOrderModal').modal('hide');
                    pushNotify('success', 'Recall Order', 'Order recalled for Table ' + order.roomno,
                        'fade', 300, '', '', true, true, true, 3000, 20, 20, 'outline', 'right top');
                }

                if (order.dcode !== $('#restcode').val()) {
                    $('#outlet_id').val(order.dcode).trigger('change');
                    setTimeout(restoreOrder, 900);
                } else {
                    restoreOrder();
                }
            });

            $('#btnSave').on('click', function(e) {
                e.preventDefault();
                if ($('#orderBody tr').length === 0) {
                    pushNotify('error', 'Service Desk', 'Please Add At Least 1 Item', 'fade', 300, '', '',
                        true, true, true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }
                if (!$('#roomno').val() || !$('#waiter').val() || !$('#pax').val()) {
                    pushNotify('error', 'Service Desk', 'Please Fill Table/Waiter/Pax', 'fade', 300, '', '',
                        true, true, true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }
                let formData = $('#servicedeskform').serialize();
                $.ajax({
                    type: 'POST',
                    url: '/kotstore',
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#olddocidpendingkot').val(response.docid);
                            pushNotify('success', 'Service Desk', response.message, 'fade', 300,
                                '', '', true, true, true, 3000, 20, 20, 'outline',
                                'right top');
                        } else {
                            pushNotify('error', 'Service Desk', response.message, 'fade', 300,
                                '', '', true, true, true, 3000, 20, 20, 'outline',
                                'right top');
                        }
                    },
                    error: function(xhr) {
                        pushNotify('error', 'Service Desk', 'Something went wrong', 'fade', 300,
                            '', '', true, true, true, 3000, 20, 20, 'outline', 'right top');
                    }
                });
            });

            $('#btnPrint').on('click', function() {
                let docid = $('#olddocidpendingkot').val();
                if (!docid) {
                    pushNotify('warning', 'Service Desk', 'Please Save KOT First', 'fade', 300, '', '',
                        true, true, true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }
                $('#previewKotNo').text($('#krsno').text());
                $('#previewTable').text($('#roomno').val());
                $('#previewWaiter').text($('#waiter option:selected').text());
                let body = $('#previewItemsBody');
                body.empty();
                $('#orderBody tr').each(function() {
                    let itemname = $(this).find('input[name^="itemname"]').val();
                    let qty = $(this).find('.qtyitem').val();
                    let note = $(this).find('input[name^="description"]').val();
                    let voidval = $(this).find('.voidyn').val();
                    body.append(`<tr>
                        <td class="text-start">${itemname}</td>
                        <td>${qty}</td>
                        <td>${note || ''}</td>
                        <td>${voidval}</td>
                    </tr>`);
                });
                $('#kotPreviewModal').modal('show');
            });

            $('#kotPreviewConfirm').on('click', function() {
                let docid = $('#olddocidpendingkot').val();
                $('#kotPreviewModal').modal('hide');
                fetch('/sendprintdata', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            docid: docid,
                            printedit: 'N'
                        })
                    })
                    .then(r => r.json())
                    .then(data => pushNotify('success', 'Print', data.message, 'fade', 300, '', '', true,
                        true, true, 3000, 20, 20, 'outline', 'right top'));
            });

            $('#btnBill').on('click', function(e) {
                e.preventDefault();

                if ($('#orderBody tr').length === 0) {
                    pushNotify('error', 'Service Desk', 'Please Add At Least 1 Item', 'fade', 300, '', '',
                        true, true, true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }
                if (!$('#roomno').val() || !$('#waiter').val()) {
                    pushNotify('error', 'Service Desk', 'Please Select Table/Room & Waiter', 'fade', 300,
                        '', '', true, true, true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }

                $('#btnBill').prop('disabled', true).text('Processing...');


                let kotFormData = $('#servicedeskform').serialize();
                $.ajax({
                    type: 'POST',
                    url: '/kotstore',
                    data: kotFormData,
                    success: function(kotResponse) {
                        if (kotResponse.status !== 'success') {
                            $('#btnBill').prop('disabled', false).text('Bill');
                            pushNotify('error', 'Service Desk', kotResponse.message, 'fade',
                                300, '', '', true, true, true, 3000, 20, 20, 'outline',
                                'right top');
                            return;
                        }

                        let kotdocid = Array.isArray(kotResponse.docid) ? kotResponse.docid
                            .join(',') : kotResponse.docid;

                        if (mergedKotDocids.length > 0) {
                            kotdocid = [kotdocid, ...mergedKotDocids].join(',');
                        }

                        // Step 2: Build sale bill payload
                        let grossAmount = parseFloat($('#grossAmount').text()) || 0;
                        let restcode = $('#restcode').val();

                        let billData = {
                            _token: token,
                            fixrestcode: restcode,
                            restcode: restcode,
                            roomno: $('#roomno').val(),
                            pax: $('#pax').val(),
                            waiter: $('#waiter').val(),
                            waitersname: $('#waiter option:selected').text(),
                            kotdocid: kotdocid,
                            kotdocidfix: kotdocid,
                            kotno: kotResponse.docid,
                            vtype: 'B{{ $shortname }}',
                            vdatesale1: $('#ncurdate').val(),
                            totalitems: $('#totalitems').val(),
                            oldroomyn: 'N',
                            company: $('#company').val(),
                            phoneno: $('#phoneno').val(),
                            customername: $('#customername').val(),
                            address: $('#address').val(),
                            customercity: $('#customercity').val(),
                            like: $('#like').val(),
                            dislike: $('#dislike').val(),
                            birthdate: $('#birthdate').val(),
                            anniversary: $('#anniversary').val(),
                            totalitemsum: grossAmount.toFixed(2),


                        };

                        let idx = 0;
                        $('#orderBody tr').each(function() {
                            idx++;
                            billData['itemcode' + idx] = $(this).find(
                                'input[name^="itemcode"]').val();
                            billData['itemrestcode' + idx] = $(this).find(
                                'input[name^="itemrestcode"]').val();
                            billData['itemname' + idx] = $(this).find(
                                'input[name^="itemname"]').val();
                            billData['description' + idx] = $(this).find(
                                'input[name^="description"]').val();
                            billData['quantity' + idx] = $(this).find('.qtyitem').val();
                            billData['rate' + idx] = $(this).find('.rateclass').val();
                            billData['amount' + idx] = $(this).find('.amount-cell')
                                .text();
                            billData['voidyn' + idx] = $(this).find('.voidyn').val();
                        });

                        $.ajax({
                            type: 'POST',
                            url: '{{ route('salebillsubmit') }}',
                            data: billData,
                            success: function(response) {
                                $('#btnBill').prop('disabled', false).text('Bill');
                                lastBillPrintData = (response && typeof response === 'object' &&
                                    response.printdata) ? response.printdata : null;
                                showCurrentBill();
                                pushNotify('success', 'Service Desk',
                                    'Bill Saved Successfully!', 'fade', 300, '',
                                    '', true, true, true, 3000, 20, 20,
                                    'outline', 'right top');
                            },
                            error: function(xhr) {
                                $('#btnBill').prop('disabled', false).text('Bill');
                                console.log(xhr.responseText);
                                // Server likely saved the bill but returned a non-JSON (view/redirect)
                                // response because the AJAX JSON branch isn't wired up yet on the backend.
                                // Still show the bill on screen using what we already have client-side.
                                lastBillPrintData = null;
                                showCurrentBill();
                                pushNotify('warning', 'Service Desk',
                                    'Bill likely saved, but print data could not be fetched. Please verify.',
                                    'fade', 300, '', '', true, true, true, 4000,
                                    20, 20, 'outline', 'right top');
                            }
                        });
                    },
                    error: function() {
                        $('#btnBill').prop('disabled', false).text('Bill');
                    }
                });
            });

            // ---------- Show the current order as the finalized bill, right in the same panel ----------
            function showCurrentBill() {
                $('#currentOrderTitle').text('CURRENT BILL');
                let billnoText = (lastBillPrintData && lastBillPrintData.billno) ?
                    ('Bill #' + lastBillPrintData.billno + ' — ') : '';
                $('#billSuccessMsg').text(billnoText + 'Generated for Table ' + $('#roomno').val());
                $('#billSuccessPanel').removeClass('d-none');
                $('#orderBody :input').prop('disabled', true);
            }

            // ---------- Bill Print (opens salebillprint popup like Sale Bill Entry) ----------
            function openBillPrint(printdata) {
                let kotno = printdata.kotno || '';
                let kotnohead = kotno ? `<strong>KOT No: <span id="kotno">${kotno}</span></strong>` : '';
                let waitersname = printdata.waiter ? '<strong>Waiter: </strong>' + printdata.waiter : '';
                let openfile = window.open('salebillprint', '_blank');
                openfile.onload = function() {
                    $('#roomno', openfile.document).text(printdata.roomno);
                    $('#vdate', openfile.document).text(printdata.vdate);
                    $('#billno', openfile.document).text(printdata.billno);
                    $('#vtype', openfile.document).text(printdata.vtype);
                    $('#departname', openfile.document).text(printdata.departname);
                    $('#kotno', openfile.document).html(kotnohead);
                    $('#waiter', openfile.document).html(waitersname);
                    $('#outletcode', openfile.document).text(printdata.outletcode);
                    $('#departnature', openfile.document).text(printdata.departnature);
                    $('#sale1docid', openfile.document).text(printdata.docid ?? '');
                };
            }

            // ---------- Fallback print: builds a simple printable bill from what's on screen ----------
            function openFallbackBillPrint() {
                let rows = '';
                $('#orderBody tr').each(function() {
                    let name = $(this).find('input[name^="itemname"]').val();
                    let qty = $(this).find('.qtyitem').val();
                    let rate = $(this).find('.rateclass').val();
                    let amt = $(this).find('.amount-cell').text();
                    rows +=
                        `<tr><td>${name}</td><td style="text-align:center;">${qty}</td><td style="text-align:right;">${rate}</td><td style="text-align:right;">${amt}</td></tr>`;
                });
                let outletName = $('#outlet_id option:selected').text();
                let html = `<html><head><title>Bill</title>
                    <style>
                        body{font-family:Arial, sans-serif;padding:20px;}
                        table{width:100%;border-collapse:collapse;margin-top:10px;}
                        td,th{border:1px solid #ccc;padding:4px 8px;font-size:13px;}
                        h3{margin-bottom:2px;}
                    </style></head><body>
                    <h3>${outletName}</h3>
                    <p>Table/Room: ${$('#roomno').val()} &nbsp;&nbsp; Waiter: ${$('#waiter option:selected').text()} &nbsp;&nbsp; KOT No: ${$('#krsno').text()}</p>
                    <table><thead><tr><th>Item</th><th>Qty</th><th>Rate</th><th>Amount</th></tr></thead>
                    <tbody>${rows}</tbody></table>
                    <h4 style="text-align:right;margin-top:12px;">Net Amount: ${$('#netAmount').text()}</h4>
                    </body></html>`;
                let w = window.open('', '_blank');
                w.document.write(html);
                w.document.close();
                setTimeout(() => w.print(), 300);
            }

            $('#btnBillPrintNow').on('click', function() {
                if (lastBillPrintData) {
                    openBillPrint(lastBillPrintData);
                } else {
                    openFallbackBillPrint();
                }
            });

            $('#btnBillNewOrder').on('click', function() {
                resetOrderForm();
            });

            $('#btnNew').on('click', function() {
                resetOrderForm();
            });
            $('#btnCancel').on('click', function() {
                if (confirm('Clear current order?')) {
                    $('#orderBody').empty();
                    totaladditems = 0;
                    $('#totalitems').val(0);
                    updateTotals();
                }
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'F6') {
                    e.preventDefault();
                    $('#btnSave').click();
                }
                if (e.key === 'F7') {
                    e.preventDefault();
                    $('#btnPrint').click();
                }
                if (e.key === 'F8') {
                    e.preventDefault();
                    $('#btnBill').click();
                }
                if (e.key === 'Escape') {
                    window.location.href = "{{ url('/company') }}";
                }
            });

            $('#favourite').trigger('click');
        });
    </script>
@endsection