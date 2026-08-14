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
                        <div class="col-md-5 d-flex align-items-center gap-3">
                            <span class="pos-label">Order Type</span>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="orderType" id="dineIn"
                                    value="Dine In" checked>
                                <label class="form-check-label fw-bold" for="dineIn">Dine In</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="orderType" id="takeAway"
                                    value="Take Away">
                                <label class="form-check-label fw-bold" for="takeAway">Take Away</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="orderType" id="delivery"
                                    value="Delivery">
                                <label class="form-check-label fw-bold" for="delivery">Delivery</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="orderType" id="roomService"
                                    value="Room Service">
                                <label class="form-check-label fw-bold" for="roomService">Room Service</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center border p-1 rounded bg-light">
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
                        <div class="col-md-2 d-flex align-items-center gap-2">
                            <label class="pos-label">Pax</label>
                            <select class="form-select form-select-sm fw-bold" name="pax" id="pax" required>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>
                                        {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-center gap-2">
                            <label class="pos-label">Steward</label>
                            <select class="form-select form-select-sm" name="waiter" id="waiter" required>
                                <option value="">Select Waiter</option>
                                @foreach ($servermast as $item)
                                    <option value="{{ $item->scode }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center border p-1 rounded bg-light">
                                <span class="fw-bold text-dark me-2">Date / Time</span>
                                <span class="fw-bold text-dark" id="curtime">{{ date('d-m-Y  h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mt-1">
                        <div class="col-md-4 d-flex align-items-center gap-2">
                            <label class="pos-label">Guest Name</label>
                            <input type="text" class="form-control form-control-sm" name="guest_name" id="guest_name"
                                placeholder="Enter Guest Name">
                        </div>
                        <div class="col-md-4 d-flex align-items-center gap-2">
                            <label class="pos-label">Mobile</label>
                            <input type="text" class="form-control form-control-sm fw-bold" name="mobile"
                                id="mobile">
                        </div>
                    </div>
                </div>
>
                <div class="row g-2">
                    <div class="col-md-2">
                        <div class="pos-card category-container-wrapper">
                            <div class="pos-header-navy text-center text-uppercase">CATEGORY</div>
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
                            <div class="row g-2" id="itemsGrid">
                            </div>
                        </div>
                    </div>

                    <!-- Current Order -->
                    <div class="col-md-5">
                        <div class="pos-card h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="pos-header-navy text-center text-uppercase">CURRENT ORDER</div>
                                <table class="table table-bordered table-sm align-middle text-center mb-0 order-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 6%;">Sr</th>
                                            <th style="width: 10%;">Qty</th>
                                            <th class="text-start" style="width: 32%;">Item Name</th>
                                            <th style="width: 12%;">Rate</th>
                                            <th style="width: 12%;">Amount</th>
                                            <th style="width: 18%;">Note</th>
                                            <th style="width: 10%;">✕</th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderBody">
                            
                                    </tbody>
                                </table>
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
                    <div class="col"><button type="button" class="btn btn-pos-hold w-100 pos-bottom-btn" disabled
                            title="Coming Soon"><i class="fa-solid fa-pause mb-1"></i> Hold<small>F9</small></button>
                    </div>
                    <div class="col"><button type="button" class="btn btn-pos-recall w-100 pos-bottom-btn" disabled
                            title="Coming Soon"><i class="fa-solid fa-rotate mb-1"></i> Recall</button></div>
                    <div class="col"><button type="button" class="btn btn-pos-merge w-100 pos-bottom-btn" disabled
                            title="Coming Soon"><i class="fa-solid fa-code-merge mb-1"></i> Merge Table</button></div>
                    <div class="col"><button type="button" class="btn btn-pos-split w-100 pos-bottom-btn" disabled
                            title="Coming Soon"><i class="fa-solid fa-border-all mb-1"></i> Split Bill</button></div>
                    <div class="col"><a href="{{ url('kottransfer') }}?dcode={{ $dcode }}"
                            class="btn btn-pos-transfer w-100 pos-bottom-btn"><i
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
            const token = '{{ csrf_token() }}';

            // ---------- Item image helpers ----------
            const itemPlaceholderImage = "{{ asset('admin/icons/custom/60x60.svg') }}";
            const itemStorageBase = "{{ asset('storage/property/itempicture') }}";
            const itemFallbackImage = "{{ url('assets/img/100_90.svg') }}";

            function getItemImageSrc(item) {
                if (!item.iempic) return itemPlaceholderImage;
                return `${itemStorageBase}/${item.iempic}`;
            }

            // ---------- Outlet switch -> reload page with new dcode ----------
            $('#outlet_id').on('change', function() {
                window.location.href = "{{ url('servicedesk') }}?dcode=" + $(this).val();
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

            // ---------- Item add to order ----------
            $(document).on('click', '.tditemname', function() {
                let itemcode = $(this).data('value');
                let itemrate = parseFloat($(this).data('id')) || 0;
                let itemrestcode = $(this).data('itemrestcode');
                let itemname = $(this).find('.item-title').text().trim();

                let existing = $('#orderBody tr').filter(function() {
                    return $(this).find('input[name^="itemcode"]').val() === itemcode;
                });

                if (existing.length) {
                    let qtyInput = existing.find('.qtyitem');
                    qtyInput.val(parseInt(qtyInput.val()) + 1).trigger('change');
                } else {
                    totaladditems++;
                    let idx = totaladditems;
                    let row = `<tr>
                        <td class="sr">${idx}</td>
                        <td>
                            <input type="hidden" name="itemcode${idx}" value="${itemcode}">
                            <input type="hidden" name="itemrestcode${idx}" value="${itemrestcode}">
                            <input type="hidden" name="itemname${idx}" value="${itemname}">
                            <input type="hidden" name="voidyn${idx}" value="No">
                            <input type="text" class="form-control form-control-sm qtyitem text-center" name="quantity${idx}" value="1">
                        </td>
                        <td class="text-start fw-bold">${itemname}</td>
                        <td><input type="text" class="form-control form-control-sm rateclass text-end" name="rate${idx}" value="${itemrate.toFixed(2)}" readonly></td>
                        <td class="fw-bold amount-cell">${itemrate.toFixed(2)}</td>
                        <td>
                            <input type="hidden" name="description${idx}" id="description${idx}" value="">
                            <span class="text-primary small description-cell" style="cursor:pointer;text-decoration:underline;" data-idx="${idx}">+ Note</span>
                        </td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger removeItem">✕</button></td>
                    </tr>`;
                    $('#orderBody').append(row);
                    $('#totalitems').val(totaladditems);
                }
                updateTotals();
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
                    $(this).find('.sr').text(idx);
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
                    let amt = qty * rate;
                    $(this).find('.amount-cell').text(amt.toFixed(2));
                    totalQty += qty;
                    gross += amt;
                });
                $('#totalQty').text(totalQty.toFixed(2));
                $('#grossAmount').text(gross.toFixed(2));
                $('#netAmount').text(gross.toFixed(
                    2
                    )); // Tax/discount abhi include nahi kiya — zarurat ho to salebillentry ki calculatetaxes() logic yaha bhi la sakte ho
            }

            function resetOrderForm() {
                $('#orderBody').empty();
                totaladditems = 0;
                $('#totalitems').val(0);
                $('#olddocidpendingkot').val('');
                $('#guest_name').val('');
                $('#mobile').val('');
                $('#kotremark').val('');
                $('#pax').val(1);
                $('#waiter').val('');
                $('#roomno').val('');
                updateTotals();
            }

            // ---------- Save KOT (reuses existing /kotstore -> Kot::submitkotentry) ----------
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

            // ---------- Print KOT (reuses existing /sendprintdata) ----------
            $('#btnPrint').on('click', function() {
                let docid = $('#olddocidpendingkot').val();
                if (!docid) {
                    pushNotify('warning', 'Service Desk', 'Please Save KOT First', 'fade', 300, '', '',
                        true, true, true, 2000, 20, 20, 'outline', 'right top');
                    return;
                }
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

            // ---------- Bill: save KOT then submit sale bill on this same page (no redirect) ----------
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

                // Step 1: Save KOT first
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
                            guestname: $('#guest_name').val(),
                            guestmobile: $('#mobile').val(),
                            totalitemsum: grossAmount.toFixed(2),

                          
                        };

                        // Per-item details, same shape salebillentry's form submits
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
                            billData['voidyn' + idx] = 'No';
                        });

                        $.ajax({
                            type: 'POST',
                            url: '{{ route('salebillsubmit') }}',
                            data: billData,
                            success: function(response) {
                                $('#btnBill').prop('disabled', false).text('Bill');
                                pushNotify('success', 'Service Desk',
                                    'Bill Saved Successfully!', 'fade', 300, '',
                                    '', true, true, true, 3000, 20, 20,
                                    'outline', 'right top');
                                resetOrderForm();
                            },
                            error: function(xhr) {
                                $('#btnBill').prop('disabled', false).text('Bill');
                                console.log(xhr.responseText);
                                pushNotify('error', 'Service Desk',
                                    'Bill submit failed — check console',
                                    'fade', 300, '', '', true, true, true, 3000,
                                    20, 20, 'outline', 'right top');
                            }
                        });
                    },
                    error: function() {
                        $('#btnBill').prop('disabled', false).text('Bill');
                    }
                });
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

            // ---------- Keyboard shortcuts ----------
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

            // ---------- Load default (Favourite) category items on page load ----------
            $('#favourite').trigger('click');
        });
    </script>
@endsection
