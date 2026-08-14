<div class="modal fade" id="orderRequestsModal" tabindex="-1" role="dialog" aria-labelledby="orderRequestsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width:95vw;width:95vw;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="orderRequestsModalLabel"><i class="fa-solid fa-bowl-food"></i> Requests</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height:80vh;overflow-y:auto;">
                <div id="orderRequestsListContainer" style="overflow-x:auto;">
                    <p class="text-muted text-center">Loading...</p>
                </div>
                <nav id="orderRequestsPagination" class="d-flex justify-content-center mt-2"></nav>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail View Modal (appears on top of list modal) -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" role="dialog" aria-labelledby="orderDetailModalLabel"
    aria-hidden="true" style="z-index:1060;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="orderDetailModalLabel"><i class="fa-solid fa-list"></i> Request Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <strong>Request ID:</strong> <span id="detailOrderId"></span><br>
                    <strong>Type:</strong> <span id="detailReqType"></span><br>
                    <strong>Outlet/Department:</strong> <span id="detailOutlet"></span><br>
                    <strong>Room/Table No:</strong> <span id="detailRoomNo"></span><br>
                    <strong>Time:</strong> <span id="detailTime"></span><br>
                    <strong>Guest Name:</strong> <span id="detailguestname"></span><br>
                    <strong>Mobile:</strong> <span id="detailmobie"></span><br>
                    <strong>Notes:</strong> <span id="detailNotes"></span><br>
                </div>
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody id="detailItemsBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    #orderDetailModal .modal-backdrop {
        z-index: 1055;
    }
</style>

<script>
    $(function() {
        let cachedCombinedRequests = [];
        const perPage = 5;
        let currentPage = 1;

        function escapeHtml(text) {
            return $('<div/>').text(text || '').html();
        }

        // Group raw order-request rows (one row per item) by order_id
        function groupOrders(orders) {
            const grouped = {};
            orders.forEach(function(item) {
                if (!grouped[item.order_id]) {
                    grouped[item.order_id] = {
                        reqtype: 'order',
                        id: item.order_id,
                        outletname: item.maindepartname,
                        roomno: item.roomno,
                        entrytime: item.entrytime,
                        status: item.reqstatus || 'pending',
                        items: [],
                        guestname: item.guestname,
                        guestmobile: item.guestmobile,
                        departnature: item.nature,
                        notes: item.remarks || ''
                    };
                }
                grouped[item.order_id].items.push({
                    itemname: item.itemname,
                    qty: item.qty
                });
            });
            return Object.values(grouped);
        }

        // Normalize already-grouped service-request objects into the same shape
        function normalizeServiceRequests(requests) {
            return (requests || []).map(function(req) {
                return {
                    reqtype: 'service',
                    id: req.requestno,
                    outletname: req.requestedfrom,
                    roomno: req.roomno,
                    entrytime: (req.requestdate || '') + ' ' + (req.requesttime || ''),
                    status: req.reqstatus || 'Pending',
                    items: (req.items || []).map(function(it) {
                        return {
                            itemname: it.itemname,
                            qty: ''
                        };
                    }),
                    guestname: req.guestname,
                    guestmobile: req.guestmobile,
                    departnature: req.requesttype,
                    notes: req.remarks || ''
                };
            });
        }

        function buildCombinedFromRes(res) {
            const orderGrouped = groupOrders(res.orderrequests || []);
            const serviceNormalized = normalizeServiceRequests(res.servicerequests || []);
            let combined = orderGrouped.concat(serviceNormalized);

            // Pending/In Progress sabse upar (newest-first), Delivered/Rejected niche (newest-first)
            combined.sort(function(a, b) {
                const openStatuses = ['pending', 'in progress'];
                const aOpen = openStatuses.includes((a.status || '').toLowerCase());
                const bOpen = openStatuses.includes((b.status || '').toLowerCase());
                if (aOpen !== bOpen) return aOpen ? -1 : 1;
                return new Date(b.entrytime) - new Date(a.entrytime);
            });

            return combined;
        }

        function loadCombinedRequests(callback) {
            // Agar header ka cached data available hai (2 min se purana nahi), turant use karo
            const cacheAge = window.__lastTicketNotifTime ? (Date.now() - window.__lastTicketNotifTime) :
                Infinity;
            let usedCache = false;

            if (window.__lastTicketNotifRes && cacheAge < 120000) {
                cachedCombinedRequests = buildCombinedFromRes(window.__lastTicketNotifRes);
                usedCache = true;
                if (callback) callback();
            }

            // Background mein hamesha fresh data le aao (chahe cache use kiya ho ya nahi)
            $.ajax({
                url: '{{ route('tools.getMyTicketNotifications') }}',
                method: 'GET',
                success: function(res) {
                    window.__lastTicketNotifRes = res;
                    window.__lastTicketNotifTime = Date.now();
                    cachedCombinedRequests = buildCombinedFromRes(res);

                    if (usedCache) {
                        // Pehle se cache se render ho chuka tha, ab silently refresh kar do
                        if ($('#orderRequestsModal').hasClass('show')) {
                            renderRequestList(cachedCombinedRequests, currentPage);
                        }
                    } else if (callback) {
                        callback();
                    }
                },
                error: function() {
                    if (!usedCache) {
                        $('#orderRequestsListContainer').html(
                            '<p class="text-danger text-center">Failed to load requests.</p>');
                    }
                }
            });
        }

        function renderRequestList(requests, page) {
            const container = $('#orderRequestsListContainer');
            const totalPages = Math.ceil(requests.length / perPage);
            const start = (page - 1) * perPage;
            const pageRequests = requests.slice(start, start + perPage);

            if (requests.length === 0) {
                container.html('<p class="text-muted text-center">No requests today.</p>');
                $('#orderRequestsPagination').html('');
                return;
            }

            let html = '<table class="table table-bordered table-striped table-sm">';
            html += '<thead class="bg-dark text-white"><tr>';
            html +=
                '<th>Type</th><th>Request ID</th><th>Outlet/Dept</th><th>Room/Table No</th><th>Items</th><th>Action</th>';
            html += '</tr></thead><tbody>';

            pageRequests.forEach(function(req) {
                let typeLabel = req.reqtype === 'order' ?
                    '<span class="badge badge-info">Order</span>' :
                    '<span class="badge badge-warning">Service</span>';

                let itemNames = (req.items || [])
                    .map(function(it) {
                        return escapeHtml(it.itemname);
                    })
                    .join(', ');
                if (!itemNames) itemNames = '<span class="text-muted">No items</span>';

                const statusLower = (req.status || '').toLowerCase();

                html += '<tr>';
                html += '<td>' + typeLabel + '</td>';
                html += '<td>' + escapeHtml(req.id) + '</td>';
                html += '<td>' + escapeHtml(req.outletname) + '</td>';
                html += '<td>' + escapeHtml(req.roomno) + '</td>';
                html += '<td style="white-space:normal;">' + itemNames + '</td>';
                html += '<td style="white-space:nowrap;">';
                html += '<button class="btn btn-sm btn-info mr-1 btn-view-req" data-reqtype="' + req
                    .reqtype + '" data-id="' + escapeHtml(req.id) + '" data-roomno="' + escapeHtml(req
                        .roomno) + '">View</button>';

                if (statusLower === 'delivered') {
                    html +=
                        '<span class="text-success font-weight-bold ml-1"><i class="fa fa-check"></i> Delivered</span>';
                } else if (statusLower === 'rejected' || statusLower === 'cancelled') {
                    html +=
                        '<span class="text-danger font-weight-bold ml-1"><i class="fa fa-times"></i> Rejected</span>';
                } else {
                    // Pending / In Progress ya status unknown/missing — hamesha buttons dikhao
                    if (statusLower === 'in progress') {
                        html += '<span class="badge badge-primary ml-1 mr-1">In Progress</span>';
                    }
                    html +=
                        '<button class="btn btn-sm btn-success mr-1 btn-accept-req" data-reqtype="' +
                        req.reqtype + '" data-id="' + escapeHtml(req.id) + '" data-roomno="' +
                        escapeHtml(req.roomno) + '">Accept</button>';
                    html += '<button class="btn btn-sm btn-danger btn-reject-req" data-reqtype="' + req
                        .reqtype + '" data-id="' + escapeHtml(req.id) + '" data-roomno="' + escapeHtml(
                            req.roomno) + '">Reject</button>';
                }

                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            container.html(html);

            let pagHtml = '';
            if (totalPages > 1) {
                pagHtml += '<ul class="pagination pagination-sm">';
                pagHtml += '<li class="page-item ' + (page <= 1 ? 'disabled' : '') +
                    '"><a class="page-link order-page-link" href="#" data-page="' + (page - 1) +
                    '">&laquo;</a></li>';
                for (let p = 1; p <= totalPages; p++) {
                    pagHtml += '<li class="page-item ' + (p === page ? 'active' : '') +
                        '"><a class="page-link order-page-link" href="#" data-page="' + p + '">' + p +
                        '</a></li>';
                }
                pagHtml += '<li class="page-item ' + (page >= totalPages ? 'disabled' : '') +
                    '"><a class="page-link order-page-link" href="#" data-page="' + (page + 1) +
                    '">&raquo;</a></li>';
                pagHtml += '</ul>';
            }
            $('#orderRequestsPagination').html(pagHtml);
        }

        function loadAndRenderRequests() {
            loadCombinedRequests(function() {
                currentPage = 1;
                renderRequestList(cachedCombinedRequests, currentPage);
            });
        }

        // Open combined modal on icon click
        $(document).on('click', '#outletorders', function() {
            $('#orderRequestsModal').modal('show');
            loadAndRenderRequests();
        });

        // Pagination click
        $(document).on('click', '.order-page-link', function(e) {
            e.preventDefault();
            let page = parseInt($(this).data('page'));
            if (isNaN(page) || page < 1) return;
            currentPage = page;
            renderRequestList(cachedCombinedRequests, currentPage);
        });

        // View request details
        $(document).on('click', '.btn-view-req', function() {
            let reqtype = $(this).data('reqtype');
            let id = $(this).data('id');
            let roomno = $(this).data('roomno');
            let req = cachedCombinedRequests.find(r => r.reqtype === reqtype && String(r.id) === String(
                id));
            if (!req) return;

            // Service request ko "view" mark karo -> status Pending se In Progress ho jayega,
            // viewtime + viewuser DB me save ho jayenge (sirf jab abhi Pending ho)
            if (reqtype === 'service' && (req.status || '').toLowerCase() === 'pending') {
                $.ajax({
                    url: '{{ route('servicerequest.view') }}',
                    method: 'POST',
                    data: {
                        requestno: id,
                        roomno: roomno,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        console.log('view-track response:', res);
                        if (res.status !== 'success' || res.updated === 0) {
                            console.warn('View-track ne row update nahi kiya. Response:',
                                res);
                        }
                        // list ko silently refresh kar do taaki status/badge updated dikhe
                        loadAndRenderRequests();
                    },
                    error: function(xhr) {
                        console.error('View-track AJAX FAILED. Status:', xhr.status,
                            'Response:', xhr
                            .responseText);
                    }
                });
            }

            $('#detailOrderId').text(req.id);
            $('#detailReqType').text(req.reqtype === 'order' ? 'Order' : 'Service');
            $('#detailOutlet').text(req.outletname);
            $('#detailTime').text(req.entrytime ? new Date(req.entrytime).toLocaleString('en-GB') : req
                .entrytime);
            $('#detailNotes').text(req.notes || '-');

            if (req.reqtype === 'order' && (req.departnature || '').toLowerCase() === 'room service') {
                $('#detailRoomNo').text('Room ' + req.roomno);
                $('#detailguestname').text(req.guestname ?? '');
                $('#detailmobie').text(req.guestmobile ?? '');
            } else if (req.reqtype === 'order') {
                $('#detailRoomNo').text('Table ' + req.roomno);
                $('#detailguestname').text(req.guestname ?? '');
                $('#detailmobie').text(req.guestmobile ?? '');
            } else {
                $('#detailRoomNo').text('Room ' + req.roomno);
                $('#detailguestname').text(req.guestname ?? '');
                $('#detailmobie').text(req.guestmobile ?? '');
            }

            let html = '';
            (req.items || []).forEach(function(item, idx) {
                html += '<tr>';
                html += '<td>' + (idx + 1) + '</td>';
                html += '<td>' + escapeHtml(item.itemname) + '</td>';
                html += '<td>' + escapeHtml(item.qty) + '</td>';
                html += '</tr>';
            });
            $('#detailItemsBody').html(html);
            $('#orderDetailModal').modal('show');
        });

        // Accept request (routes to correct backend based on type)
        $(document).on('click', '.btn-accept-req', function() {
            let btn = $(this);
            let reqtype = btn.data('reqtype');
            let id = btn.data('id');
            let roomno = btn.data('roomno');

            const url = reqtype === 'order' ? '/order-request/accept' : '/service-request/accept';
            const payload = reqtype === 'order' ? {
                order_id: id,
                _token: $('meta[name="csrf-token"]').attr('content')
            } : {
                requestno: id,
                roomno: roomno,
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            Swal.fire({
                title: 'Accept Request?',
                text: 'This will accept ' + (reqtype === 'order' ? 'order ' :
                    'service request ') + id,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Accept',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).text('Processing...');
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: payload,
                        success: function(res) {
                            const ok = res.status === 'success' || res.success ===
                                true;
                            if (ok) {
                                Swal.fire('Delivered!', res.message, 'success');
                                loadAndRenderRequests();
                            } else {
                                Swal.fire('Error', res.message, 'error');
                                btn.prop('disabled', false).text('Accept');
                            }
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON ? xhr.responseJSON.message :
                                'Something went wrong';
                            Swal.fire('Error', msg, 'error');
                            btn.prop('disabled', false).text('Accept');
                        }
                    });
                }
            });
        });

        // Reject request (routes to correct backend based on type)
        $(document).on('click', '.btn-reject-req', function() {
            let btn = $(this);
            let reqtype = btn.data('reqtype');
            let id = btn.data('id');
            let roomno = btn.data('roomno');

            const url = reqtype === 'order' ? '/order-request/reject' : '/service-request/reject';
            const payload = reqtype === 'order' ? {
                order_id: id,
                _token: $('meta[name="csrf-token"]').attr('content')
            } : {
                requestno: id,
                roomno: roomno,
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            Swal.fire({
                title: 'Reject Request?',
                text: 'This will reject ' + (reqtype === 'order' ? 'order ' :
                    'service request ') + id,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).text('Rejecting...');
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: payload,
                        success: function(res) {
                            const ok = res.status === 'success' || res.success ===
                                true;

                            if (ok) {
                                Swal.fire('Rejected!', res.message, 'success');
                                loadAndRenderRequests();
                            } else {
                                Swal.fire('Error', res.message, 'error');
                                btn.prop('disabled', false).text('Reject');
                            }
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON ? xhr.responseJSON.message :
                                'Something went wrong';
                            Swal.fire('Error', msg, 'error');
                            btn.prop('disabled', false).text('Reject');
                        }
                    });
                }
            });
        });
    });
</script>
