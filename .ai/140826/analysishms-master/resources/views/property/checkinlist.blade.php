@extends('property.layouts.main')
@section('main-container')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        #guestfolio td,
        #guestfolio th {
            vertical-align: middle;
            white-space: nowrap;
        }

        #guestfolio td.ins {
            white-space: normal !important;
            min-width: 220px;
        }

        #guestfolio .chkouttime-trigger {
            cursor: pointer;
            color: #007bff;
            font-weight: 600;
        }

        #guestfolio .chkouttime-trigger.disabled {
            cursor: default;
            color: inherit;
            font-weight: 400;
        }

        #guestfolio .action-buttons {
            display: flex !important;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: flex-start;
            gap: 6px;
        }

        #guestfolio .action-buttons .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        div.dataTables_processing {
            z-index: 2;
        }
    </style>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="guestfolio" class="table table-striped table-bordered w-100">
                                    <thead>
                                        <tr>
                                            <th>Sn</th>
                                            <th>Guest Name</th>
                                            <th>City</th>
                                            <th>Mobile</th>
                                            <th>Room No</th>
                                            <th>Folio No</th>
                                            <th>Bill</th>
                                            <th>In Date</th>
                                            <th>In Time</th>
                                            <th>Exp. Dep Date</th>
                                            <th>Dep Date</th>
                                            <th>Dep Time</th>
                                            <th>Rate</th>
                                            <th>Tax Inc</th>
                                            <th>Comp.</th>
                                            <th>Travel</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(function() {
            const canChangeCheckoutTime = "{{ Auth::user()->useroradmin }}" === 'admin' ||
                "{{ optional(userdata())->allowchkouttimechange }}" ===
                'Y';

            const table = $('#guestfolio').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                paging: true,
                ordering: true,
                pageLength: 15,
                order: [
                    [5, 'desc']
                ],
                lengthMenu: [
                    [15, 25, 50, 100],
                    [15, 25, 50, 100]
                ],
                scrollX: true,
                autoWidth: false,
                ajax: {
                    url: "{{ route('checkinlist.data') }}",
                    type: 'GET',
                    beforeSend: function() {
                        if (typeof showLoader === 'function') {
                            showLoader();
                        }
                    },
                    dataSrc: function(json) {
                        if (typeof hideLoader === 'function') {
                            hideLoader();
                        }

                        return json.data || [];
                    },
                    error: function(xhr) {
                        if (typeof hideLoader === 'function') {
                            setTimeout(hideLoader, 1000);
                        }

                        const message = xhr.responseJSON && xhr.responseJSON.message ?
                            xhr.responseJSON.message :
                            'Failed to load check-in list.';

                        Swal.fire({
                            title: 'Error',
                            text: message,
                            icon: 'error'
                        });
                    }
                },
                columns: [{
                        data: 'sn',
                        name: 'sn',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'guest_name',
                        name: 'guest_name'
                    },
                    {
                        data: 'city',
                        name: 'city'
                    },
                    {
                        data: 'mobile_no',
                        name: 'mobile_no'
                    },
                    {
                        data: 'room_no',
                        name: 'room_no'
                    },
                    {
                        data: 'folio_no',
                        name: 'folio_no'
                    },
                    {
                        data: 'bill_no',
                        name: 'bill_no'
                    },
                    {
                        data: 'chkindate',
                        name: 'chkindate'
                    },
                    {
                        data: 'checkin_time',
                        name: 'checkin_time'
                    },
                    {
                        data: 'exp_dep_date',
                        name: 'exp_dep_date'
                    },
                    {
                        data: 'dep_date',
                        name: 'dep_date'
                    },
                    {
                        data: 'deptime',
                        name: 'deptime',
                        render: function(data, type, row) {
                            const display = data || '';
                            const clickable = canChangeCheckoutTime && row.type === 'O';
                            const cls = clickable ? 'chkouttime-trigger' : 'chkouttime-trigger disabled';

                            if (type !== 'display') {
                                return display;
                            }

                            return '<span class="' + cls + '" data-time="' + (row.deptime_raw || '') + '">' +
                                display + '</span>';
                        }
                    },
                    {
                        data: 'rate',
                        name: 'rate'
                    },
                    {
                        data: 'tax_inc',
                        name: 'tax_inc'
                    },
                    {
                        data: 'compname',
                        name: 'compname'
                    },
                    {
                        data: 'travelagent',
                        name: 'travelagent'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'ins'
                    }
                ],
                createdRow: function(row, data) {
                    $(row).attr('data-docid', data.docid);
                    $(row).attr('data-sno1', data.sno1);
                    $(row).attr('data-type', data.type || '');
                },
                language: {
                    processing: 'Loading check-in data...'
                }
            });

            table.on('preXhr.dt', function() {
                if (typeof showLoader === 'function') {
                    showLoader();
                }
            });

            table.on('xhr.dt', function() {
                if (typeof hideLoader === 'function') {
                    hideLoader();
                }
            });

            $(document).on('click', '.chkouttime-trigger', function() {
                if (!canChangeCheckoutTime || $(this).hasClass('disabled')) {
                    return;
                }

                const el = $(this);
                const row = el.closest('tr');
                const sno1 = row.data('sno1');
                const docid = row.data('docid');
                const type = row.data('type');

                if (type !== 'O') {
                    return;
                }

                Swal.fire({
                    title: 'Change Checkout Time',
                    input: 'time',
                    inputLabel: 'Select new checkout time',
                    inputValue: el.data('time') || el.text().trim(),
                    showCancelButton: true,
                    confirmButtonText: 'Change',
                    cancelButtonText: 'Cancel',
                    inputAttributes: {
                        step: 60
                    },
                    preConfirm: (newTime) => {
                        if (!newTime) {
                            Swal.showValidationMessage('Please select a time');
                            return false;
                        }

                        return newTime;
                    }
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: "{{ route('updatecheckouttime') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            new_checkout_time: result.value,
                            sno1: sno1,
                            docid: docid
                        },
                        beforeSend: function() {
                            Swal.showLoading();
                        },
                        success: function(res) {
                            if (res.success === true) {
                                const updatedTime = res.formatted_time || result.value;
                                el.text(updatedTime);
                                el.attr('data-time', result.value);

                                Swal.fire({
                                    title: 'Success',
                                    text: res.message || 'Checkout time updated successfully',
                                    icon: 'success'
                                });

                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: res.message || 'Update failed',
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON && xhr.responseJSON.message ?
                                xhr.responseJSON.message :
                                'Something went wrong';

                            Swal.fire({
                                title: 'Error',
                                text: message,
                                icon: 'error'
                            });
                        }
                    });
                });
            });

            $(document).on('click', '.js-delete-checkin', function(e) {
                e.preventDefault();

                const deleteUrl = $(this).attr('href');
                const guestName = $(this).data('guest-name') || 'Guest';
                const folioNo = $(this).data('folio-no') || '';
                const recordLabel = `${guestName}${folioNo ? ' - ' + folioNo : ''}`;

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Are you sure want to delete this ${recordLabel} record`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            });

            $(document).on('click', '.js-push-roomkey', function(e) {
                e.preventDefault();

                const button = $(this);
                const url = button.data('push-url');

                if (!url) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Push URL is not configured.',
                        icon: 'error'
                    });
                    return;
                }

const pushStatus = button.data('push-status');
                    let pushTitle = 'Push Room Key';
                    let pushText = 'Do you want to push the room key now? This will queue the request.';

                    if (pushStatus === 'already_pushed') {
                        pushTitle = 'Already pushed';
                        pushText = 'Key is already pushed. Do you want to push again?';
                    } else if (pushStatus === 'failed') {
                        pushTitle = 'Push failed';
                        pushText = 'Last push was not successful. Do you want to push again?';
                    }

                    Swal.fire({
                        title: pushTitle,
                        text: pushText,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, push it',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Please wait',
                                html: 'Sending room key request...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(res) {
                            let title = 'Success';
                            let icon = 'success';
                            let text = res.message || 'Room key request queued.';

                            if (res.status === 1) {
                                title = 'Room key allotted';
                                text = res.message || 'Room key was allotted successfully.';
                            } else if (res.status === 2) {
                                title = 'Push failed';
                                icon = 'error';
                                text = res.message || 'Room key push failed.';
                            }

                            Swal.fire({
                                title: title,
                                text: text,
                                icon: icon
                            }).then(() => {
                                table.ajax.reload(null, false);
                            });
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to push room key.';
                            Swal.fire({
                                title: 'Error',
                                text: message,
                                icon: 'error'
                            });
                        }
                    });
                });
            });
        });
    </script>
@endsection
