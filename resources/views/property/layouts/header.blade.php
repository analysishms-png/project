<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Analysis</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('admin/images/favicon.png') }}">
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Analysis HMS">
    <link rel="apple-touch-icon" href="{{ asset('admin/images/pwa-192.png') }}">
    <meta name="description" content="Professional Hotel Management System">
    <meta name="mobile-web-app-capable" content="yes">
    <!-- Pignose Calender -->
    <link href="{{ asset('admin/plugins/pg-calendar/css/pignose.calendar.min.css') }}" rel="stylesheet">
    <!-- Chartist -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/chartist/css/chartist.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/chartist-plugin-tooltips/css/chartist-plugin-tooltip.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Custom Stylesheet -->
    <link href="{{ asset('admin/css/style.css') }}" rel="stylesheet">
    <!-- HMS Design System (Bootstrap 5-style layer, UI-only) -->
    <link href="{{ asset('admin/css/hms.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/plugins/select2/css/select2.min.css') }}" rel="stylesheet">
    <!-- DataTables 2.x (core + Buttons + Responsive, BS4 theme) -->
    <link href="{{ asset('admin/plugins/datatables2/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/plugins/datatables2/css/buttons.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/plugins/datatables2/css/responsive.dataTables.min.css') }}" rel="stylesheet">
    <link
        href="{{ asset('admin/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}"
        rel="stylesheet">
    <!-- Color picker plugins css -->
    <link href="{{ asset('admin/plugins/jquery-asColorPicker-master/css/asColorPicker.css') }}" rel="stylesheet">
    <!-- Daterange picker plugins css -->
    <link href="{{ asset('admin/plugins/timepicker/bootstrap-timepicker.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Notify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-notify@1.0.4/dist/simple-notify.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="backdate" content="{{ Auth::user()->backdate }}">
    <style>
        .top-notification-wrapper {
            position: relative;
            margin-right: 15px;
            list-style: none;
        }

        .top-notification-icon {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f4f6fb;
            cursor: pointer;
            border: 1px solid #e6ebf5;
        }

        .top-notification-icon.blink {
            animation: topBellBlink 1s infinite;
        }

        @keyframes topBellBlink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.2;
            }

            100% {
                opacity: 1;
            }
        }

        .top-notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 10px;
            background: #dc3545;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .top-notification-dropdown {
            width: 480px;
            max-height: 520px;
            overflow-y: auto;
            padding: 0;
        }

        .top-notification-item {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f1f1;
            cursor: pointer;
        }

        .top-notification-item:hover {
            background: #f8f9fc;
        }

        .top-notification-title {
            font-size: 13px;
            font-weight: 700;
            color: #3a3a3a;
        }

        .top-notification-text {
            font-size: 13px;
            color: #6b7280;
            margin-top: 3px;
        }

        .top-notification-time {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 3px;
        }

        /* Fix dropdown z-index to appear above all elements */
        .dropdown-menu {
            z-index: 10000 !important;
            position: fixed !important;
            right: 20px !important;
            bottom: auto !important;
            left: auto !important;
            min-width: 480px !important;
            margin: 0 !important;
            max-height: 520px !important;
            overflow-y: auto !important;
            border: 1px solid #dee2e6 !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
        }

        .dropdown-profile {
            z-index: 10000 !important;
        }

        .icons.dropdown {
            z-index: 10000 !important;
            position: relative;
        }

        .help-line-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
        }

        .help-line-info h6 {
            color: #007bff;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .help-line-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }

        /* Ensure dropdown is visible */
        .dropdown-menu.show {
            display: block !important;
            visibility: visible !important;
        }
    </style>
</head>

<body>

    <div class="loader-overlay">
        <div class="sk-cube-grid">
            <div class="sk-cube"></div>
            <div class="sk-cube"></div>
            <div class="sk-cube"></div>
            <div class="sk-cube"></div>
            <div class="sk-cube"></div>
            <div class="sk-cube"></div>
            <div class="sk-cube"></div>
            <div class="sk-cube"></div>
            <div class="sk-cube"></div>
        </div>
        <div class="loader-text">Analysis HMS</div>
    </div>

    <!--*******************
        Preloader start
    ********************-->
    {{-- <div id="preloader">
        <div class="loader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3"
                    stroke-miterlimit="10" />
            </svg>
        </div>
    </div> --}}
    <!--*******************
        Preloader end
    ********************-->
    {{-- <div id="myloader" class="loader-overlay none">
        <div class="loader-content">
            <div class="loader-spinner">
                <div class="loader-circle"></div>
                <img src="{{ asset('admin/icons/custom/jogging.gif') }}" alt="Jogging" class="loader-image">
            </div>
            <div class="loader-text">Please wait...</div>
        </div>
    </div> --}}

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <!--**********************************
            Nav header start
        ***********************************-->

        @if (empty(companydata()->dealer_logo) ||
                !file_exists(public_path('storage/admin/dealer_logo/' . companydata()->dealer_logo)))
            <div class="nav-header">
                <div class="brand-logo">
                    <a href="{{ url('/company') }}">
                        <b class="logo-abbr">
                            <img class="rounded-circle" src="{{ env('APP_URL') }}/admin/images/user/letter-a.gif"
                                alt="">
                        </b>
                        <img src="{{ asset('admin/images/logo-text.png') }}" class="img-fluid" alt="">

                    </a>
                </div>
            </div>
        @else
            <div style="width: 100%;height: auto;" class="nav-header">
                <img style="min-width: 10em;max-width: 17.5em;"
                    src="storage/admin/dealer_logo/{{ companydata()->dealer_logo }}" alt="" />
            </div>
        @endif
        <!--**********************************
            Nav header end
        ***********************************-->

        <!--**********************************
            Header start
        ***********************************-->
        <div class="header">
            <div class="header-content clearfix">

                <div class="nav-control">
                    <div class="hamburger">
                        <span class="toggle-icon"><i class="icon-menu"></i></span>
                    </div>
                </div>
                <div class="header-left"></div>
                <div class="header-center">
                    <div class="input-group icons">
                        <div class="two alt-two">
                            <h1 class="mainhead"><span id="compnamed"></span>
                                <span id="showfinyear"></span>
                            </h1>
                        </div>
                    </div>
                </div>
                <style>
                    @media (max-width: 991px) {
                        .mobileView {
                            margin-top: -75px !important;
                        }
                    }

                    @keyframes blink-animation {
                        0% {
                            opacity: 1;
                        }

                        50% {
                            opacity: 0;
                        }

                        100% {
                            opacity: 1;
                        }
                    }

                    .blink {
                        animation: blink-animation 1s infinite;
                    }
                </style>

                <div class="header-right d-flex align-items-center mobileView">
                    {{-- ROOM STATUS ICON --}}
                    <li class="icons dropdown top-notification-wrapper" style="list-style: none;">
                        <div class="top-notification-icon" onclick="window.location.href='{{ route('roomstatus') }}'">
                            <i class="fa-solid fa-bed"></i>
                        </div>
                    </li>

                    {{-- RESERVATION LIST ICON --}}
                    <li class="icons dropdown top-notification-wrapper" style="list-style: none;">
                        <div class="top-notification-icon" onclick="window.location.href='{{ route('reservation') }}'">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                    </li>
                    {{-- @if (isset($visiblebell)) --}}
                    <li class="icons dropdown top-notification-wrapper">
                        <div class="top-notification-icon" id="outletorders">
                            <i class="fa-solid fa-bowl-food"></i>
                            <span class="top-notification-badge" id="outletOrdersBadge"></span>
                        </div>
                    </li>
                    <li class="icons dropdown top-notification-wrapper">
                        <div class="top-notification-icon" data-toggle="dropdown" id="userTopNotificationIcon">
                            <i class="fa-solid fa-bell"></i>
                            <span class="top-notification-badge" id="userTopNotificationBadge"></span>
                        </div>
                        <div class="dropdown-menu dropdown-menu-right top-notification-dropdown"
                            id="userTopNotificationDropdown">
                            <div class="p-2 border-bottom"><strong>Notifications</strong></div>
                            <div id="userTopNotificationList" class="p-2 text-muted">No notifications</div>
                        </div>
                    </li>

                    @if (count(myproperties()) > 1)
                        <div class="my-properties-wrapper position-relative mr-3">
                            {{-- <li class="my-properties-toggle" style="cursor: pointer; list-style: none;">
                                <i class="fa-solid fa-house"></i> My Properties
                            </li> --}}
                            <ul style="margin: 2em 0 0 -10em; width: max-content;"
                                class="submenuproperty cursor-pointer position-absolute shadow p-2"
                                style="display: none; list-style: none; min-width: 200px; z-index: 999;">
                                @foreach (myproperties() as $item)
                                    <li data-propertyid="{{ $item->propertyid }}" data-userid="{{ $item->userid }}"
                                        data-username="{{ Auth::user()->u_name }}"
                                        class="p-2 propertysllist bg-light font-11 {{ Auth::user()->propertyid == $item->propertyid ? 'bg-dark text-white' : '' }}">
                                        {{ $item->propertyid }} - {{ $item->comp_name }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Profile Dropdown -->
                    <li class="icons dropdown" style="list-style: none;">
                        <div class="user-img c-pointer position-relative" data-toggle="dropdown">
                            <span class="activity active"></span>
                            <img src="{{ env('APP_URL') }}/admin/images/user/letter-a.gif" height="40"
                                width="40" alt="">
                        </div>
                        <div class="drop-down dropdown-profile animated fadeIn dropdown-menu">
                            <div class="dropdown-content-body">
                                <ul>
                                    <li>
                                        <a href=""><i class="icon-user"></i><span
                                                id="usernameshow"></span></a>
                                    </li>
                                    @if (count(myproperties()) > 1)
                                        <div class="my-properties-wrapper position-relative mr-3">
                                            <li class="my-properties-toggle"
                                                style="cursor: pointer; list-style: none;">
                                                <i class="fa-solid fa-house"></i> My Properties
                                            </li>
                                            {{-- <ul class="submenuproperty cursor-pointer position-absolute shadow p-2"
                                                style="display: none; list-style: none; min-width: 200px; z-index: 999;">
                                                @foreach (myproperties() as $item)
                                                    <li data-propertyid="{{ $item->propertyid }}" data-userid="{{ $item->userid }}"
                                                        data-username="{{ Auth::user()->u_name }}"
                                                        class="p-2 propertysllist bg-light font-11 {{ Auth::user()->propertyid == $item->propertyid ? 'bg-dark text-white' : '' }}">
                                                        {{ $item->propertyid }} - {{ $item->comp_name }}
                                                    </li>
                                                @endforeach
                                            </ul> --}}
                                        </div>
                                    @endif
                                    <li class="update-log">
                                        <button type="button" class="btn btn-info btn-lg" data-toggle="modal"
                                            data-target="#updateLogModal">
                                            Update Log
                                        </button>
                                    </li>
                                    <li class="help-line-info p-3 border-top">
                                        <div class="text-center">
                                            <h6 class="mb-2"><i class="fa-solid fa-phone"></i> Help Line</h6>
                                            <p class="mb-1 font-weight-bold">1800 570 0898</p>
                                            <small class="d-block text-muted">
                                                <span><b>Mon - Sat: 8:30 AM - 9:00 PM</b></span><br>
                                                <span><b>Sunday: 10:30 AM - 7:00 PM</b></span>
                                            </small>
                                        </div>
                                    </li>
                                    <li>
                                        <a href="{{ route('logout') }}"><i
                                                class="icon-key"></i><span>Logout</span></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>

                </div>

            </div>
        </div>

        <div class="modal fade" id="updateLogModal" role="dialog" tabindex="-1"
            aria-labelledby="updateLogModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content shadow-lg rounded-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="updateLogModalLabel"><i class="fas fa-history"></i> Update Log
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="update-log-content p-3" id="updateLogContent">
                            <p class="text-muted text-center">Loading updates...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        {{-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> --}}
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>

        {{-- DataTables 2.x core + extensions (loaded after jQuery, before page scripts) --}}
        <script src="{{ asset('admin/plugins/datatables2/js/dataTables.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables2/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables2/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables2/js/buttons.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables2/js/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables2/js/buttons.print.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables2/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables2/js/jszip.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables2/js/pdfmake.min.js') }}"></script>
        <script src="{{ asset('admin/plugins/datatables2/js/vfs_fonts.js') }}"></script>

        {{-- HMS shared report helpers: fmt/fmtDate/radioVal single copy (REDIS_JS_PLAN Phase J-A) --}}
        <script src="{{ asset('js/hms-report.js') }}"></script>
        <script src="{{ asset('js/hms-table.js') }}"></script>

        <audio id="orderNotificationSound" preload="auto">
            <source src="{{ asset('assets/music/ordermsg.wav') }}" type="audio/wav">
        </audio>

        <script>
            let audioUnlocked = false;

            function unlockAudioOnce() {
                if (audioUnlocked) return;
                const audio = document.getElementById('orderNotificationSound');
                if (!audio) return;
                audio.muted = true;
                audio.play().then(() => {
                    audio.pause();
                    audio.currentTime = 0;
                    audio.muted = false;
                    audioUnlocked = true;
                }).catch(() => {});
            }

            document.addEventListener('click', unlockAudioOnce, {
                once: true
            });
            document.addEventListener('keydown', unlockAudioOnce, {
                once: true
            });
            // ============ NAYA CODE YAHAN TAK ============
            function userEscapeHtml(text) {
                return $('<div/>').text(text || '').html();
            }

            function playOrderSound() {
                var audio = document.getElementById('orderNotificationSound');
                if (!audio) {
                    console.log('Audio element not found!');
                    return;
                }
                audio.currentTime = 0;
                try {
                    var playPromise = audio.play();
                    if (playPromise !== undefined && typeof playPromise.catch === 'function') {
                        playPromise.catch(function(err) {
                            console.log('Audio play blocked:', err);
                        });
                    }
                } catch (error) {
                    console.log('Audio play error:', error);
                }
            }

            var _orderPollCount = 0;

            var _orderPollCount = 0;
            var _lastKnownCombinedCount = 0;


            function countPendingOrders(rows) {
                // order_requests ek row per item hoti hai, isliye unique order_id count karo
                const ids = new Set();
                (rows || []).forEach(function(r) {
                    if ((r.reqstatus || '').toLowerCase() === 'pending') {
                        ids.add(r.order_id);
                    }
                });
                return ids.size;
            }

            function countPendingServices(rows) {
                return (rows || []).filter(function(r) {
                    const s = (r.reqstatus || '').toLowerCase();
                    return s === 'pending' || s === 'in progress';
                }).length;
            }

            function updateUserTopNotifications() {
                _orderPollCount++;
                $.ajax({
                    url: '{{ route('tools.getMyTicketNotifications') }}',
                    method: 'GET',
                    success: function(res) {
                        window.__lastTicketNotifRes = res;
                        window.__lastTicketNotifTime = Date.now();
                        const icon = $('#userTopNotificationIcon');
                        const badge = $('#userTopNotificationBadge');
                        const list = $('#userTopNotificationList');
                        const outletordersicon = $('#outletorders');
                        const outletOrdersBadge = $('#outletOrdersBadge');

                        let orderrequests = res.orderrequests || [];
                        let servicerequests = res.servicerequests || [];

                        // FIX: sirf PENDING count karo, accepted/rejected ko nahi
                        let combinedCount = countPendingOrders(orderrequests) + countPendingServices(
                            servicerequests);

                        if (combinedCount > 0) {
                            outletordersicon.addClass('blink');
                            outletOrdersBadge.text(combinedCount > 99 ? '99+' : combinedCount).css('display',
                                'inline-flex');
                        } else {
                            outletordersicon.removeClass('blink');
                            outletOrdersBadge.hide().text('');
                        }

                        // Sound sirf tab bajao jab count PEHLE se BADH gaya ho (naya request aaya)
                        if (_orderPollCount > 1 && combinedCount > _lastKnownCombinedCount) {
                            playOrderSound();
                        }
                        _lastKnownCombinedCount = combinedCount;

                        if (!res.success || !Array.isArray(res.tickets) || res.tickets.length === 0) {
                            icon.removeClass('blink');
                            badge.hide().text('');
                            list.html('<div class="text-muted">No notifications</div>');
                            return;
                        }

                        icon.addClass('blink');
                        badge.text(res.tickets.length > 99 ? '99+' : res.tickets.length).css('display',
                            'inline-flex');

                        let html = '';
                        res.tickets.slice(0, 20).forEach(function(item) {
                            const titleSuffix = item.type === 'status' ? 'Status Update' : 'SMS';
                            html += `
        <div class="top-notification-item" onclick="window.location.href='{{ route('tools.myTickets') }}?focus_ticket=${item.id}'">
            <div class="top-notification-title">${userEscapeHtml(item.ticket_number)} • ${titleSuffix}</div>
            <div class="top-notification-text">${userEscapeHtml(item.text || '')}</div>
            <div class="top-notification-time">${userEscapeHtml(item.time || '')}</div>
        </div>
    `;
                        });

                        list.html(html);
                    }
                });
            }

            $(document).ready(function() {
                updateUserTopNotifications();
                setInterval(updateUserTopNotifications, 3600000);
            });

            var yearManageUrl = "{{ url('yearmanage') }}";
            $(document).on('click', '.my-properties-toggle', function(e) {
                e.stopPropagation();
                $('.submenuproperty').slideToggle();
            });

            $(document).on('click', function() {
                $('.submenuproperty').slideUp();
            });

            $(document).on('click', '.propertysllist', function() {
                let userid = $(this).data('userid');
                let username = $(this).data('username');
                let propertyid = $(this).data('propertyid');
                let currentUrl = window.location.href;

                $.ajax({
                    url: '/auto-login',
                    type: 'POST',
                    data: {
                        userid: userid,
                        username: username,
                        propertyid: propertyid,
                        redirect_url: currentUrl,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            window.location.href = response.redirect;
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'Info',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        alert('Something went wrong!');
                    }
                });
            });
        </script>

        @if (isset($message))
            <script>
                Swal.fire({
                    icon: '{{ $type }}',
                    title: '{{ $type }}',
                    text: '{{ $message }}',
                    timer: 5000,
                    showConfirmButton: true
                });
            </script>
        @endif

        @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                });
            </script>
        @endif
        @if (session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                });
            </script>
        @endif
        @if (session('infosale'))
            <script>
                Swal.fire({
                    icon: 'info',
                    title: "Sale Bill Entry",
                    text: "{{ session('infosale')['text'] }}",
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let printdata = {!! session('infosale')['printdata'] !!};

                        let filetoopen;
                        if (printdata.printsetup.description === 'Bill Windows Plain Paper 1') {
                            filetoopen = 'salebillprint';
                            let openfile = window.open(filetoopen, '_blank');
                            openfile.onload = function() {
                                $('#roomno', openfile.document).text(printdata.roomno);
                                $('#vdate', openfile.document).text(printdata.vdate);
                                $('#billno', openfile.document).text(printdata.billno);
                                $('#vtype', openfile.document).text(printdata.vtype);
                                $('#departname', openfile.document).text(printdata.departname);
                                $('#kotno', openfile.document).text(printdata.kotno);
                                $('#waiter', openfile.document).text(printdata.waiter);
                                $('#outletcode', openfile.document).text(printdata.outletcode);
                                $('#departnature', openfile.document).text(printdata.departnature);
                                $('#sale1docid', openfile.document).text(printdata.docid);
                            }
                        } else if (printdata.printsetup.description === 'Bill Windows Plain Paper 2') {
                            filetoopen = 'salebillprinttype2';
                            let openfile = window.open(filetoopen, '_blank');
                            openfile.onload = function() {
                                $('#roomno', openfile.document).text(printdata.roomno);
                                $('#vdate', openfile.document).text(printdata.vdate);
                                $('#billno', openfile.document).text(printdata.billno);
                                $('#vtype', openfile.document).text(printdata.vtype);
                                $('#departname', openfile.document).text(printdata.departname);
                                $('#kotno', openfile.document).text(printdata.kotno);
                                $('#waiter', openfile.document).text(printdata.waiter);
                                $('#outletcode', openfile.document).text(printdata.outletcode);
                                $('#departnature', openfile.document).text(printdata.departnature);
                                $('#sale1docid', openfile.document).text(printdata.docid);
                            }
                        } else if (printdata.printsetup.description === '3 Inch Running Paper Windows Print 1') {
                            filetoopen = 'salebillprint2';
                            let openfile = window.open(filetoopen, '_blank');
                            openfile.onload = function() {
                                $('#roomno', openfile.document).text(printdata.roomno);
                                $('#vdate', openfile.document).text(printdata.vdate);
                                $('#billno', openfile.document).text(printdata.billno);
                                $('#vtype', openfile.document).text(printdata.vtype);
                                $('#departname', openfile.document).text(printdata.departname);
                                $('#kotno', openfile.document).text(printdata.kotno);
                                $('#waiter', openfile.document).text(printdata.waiter);
                                $('#outletcode', openfile.document).text(printdata.outletcode);
                                $('#departnature', openfile.document).text(printdata.departnature);
                                $('#sale1docid', openfile.document).text(printdata.docid);
                            }
                        } else if (printdata.printsetup.description === '3 Inch Running Paper Windows Print 2') {
                            filetoopen = 'salebillprint2type2';
                            let openfile = window.open(filetoopen, '_blank');
                            openfile.onload = function() {
                                $('#roomno', openfile.document).text(printdata.roomno);
                                $('#vdate', openfile.document).text(printdata.vdate);
                                $('#billno', openfile.document).text(printdata.billno);
                                $('#vtype', openfile.document).text(printdata.vtype);
                                $('#departname', openfile.document).text(printdata.departname);
                                $('#kotno', openfile.document).text(printdata.kotno);
                                $('#waiter', openfile.document).text(printdata.waiter);
                                $('#outletcode', openfile.document).text(printdata.outletcode);
                                $('#departnature', openfile.document).text(printdata.departnature);
                                $('#sale1docid', openfile.document).text(printdata.docid);
                            }
                        } else if (printdata.printsetup.description === '3 Inch Running Paper DOS Print') {
                            console.log(printdata);
                            $.ajax({
                                url: 'salebillprintthermal',
                                data: {
                                    docid: printdata.docid
                                },
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                method: "POST",
                                success: function() {
                                    // setTimeout(() => window.location.reload(), 500);
                                },
                                error: function(error) {
                                    console.log(error);
                                }
                            });
                        }
                    }
                });
            </script>
        @endif

        @if (session('nightinfo'))
            <script>
                Swal.fire({
                    icon: 'info',
                    title: 'Night Audit',
                    text: "{{ session('nightinfo')['message'] }}",
                    showConfirmButton: true,
                    confirmButtonText: 'Click To View',
                    showCancelButton: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        let bills = '{!! session('nightinfo')['bills'] !!}';
                        var rowcode = "{{ session('nightinfo')['row'] }}";
                        var cname, ur;
                        if (rowcode == 1) {
                            let pendingbillskot = new XMLHttpRequest();
                            pendingbillskot.open('POST', '/pendingbillskot', true);
                            pendingbillskot.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                            pendingbillskot.onreadystatechange = function() {
                                if (pendingbillskot.readyState === 4 && pendingbillskot.status === 200) {
                                    let results = JSON.parse(pendingbillskot.responseText);
                                    let tbody = $('#pendingkotnightaudit tbody');
                                    tbody.empty();
                                    let tdata = '';
                                    results.forEach((data) => {
                                        if (rowcode == 1) {
                                            cname = 'kotrow';
                                            ur = `salebillentry?dcode=${data.restcode}&roomno=${data.roomno}`;
                                        } else if (rowcode == 2) {
                                            cname = 'salerow';
                                            ur =
                                                `settlemententry?dcode=${data.restcode}&tableno=${data.roomno}&vno=${data.vno}`;
                                        }
                                        tdata += `<tr class="${cname}" data-vno="${data.vno}" data-id="${data.restcode}" data-value="${data.roomno}">
                                    <td>${data.vno}</td>
                                    <td>${data.roomno}</td>
                                    <td>${data.waitername}</td>
                                    <td>${data.depname}</td>
                                    <td>Pending</td>
                                </tr>`;
                                    });
                                    tbody.append(tdata);
                                    $('.pendingkotnightaudit').removeClass('none');
                                }
                            }
                            pendingbillskot.send(`bills=${bills}&_token={{ csrf_token() }}`);
                        } else if (rowcode == 2) {
                            let salewarnxhr = new XMLHttpRequest();
                            salewarnxhr.open('GET', '/salewarnxhr', true);
                            salewarnxhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                            salewarnxhr.onreadystatechange = function() {
                                if (salewarnxhr.status === 200 && salewarnxhr.readyState === 4) {
                                    let result = JSON.parse(salewarnxhr.responseText);
                                    let count = result.count;
                                    if (count > 0) {
                                        if (result.msg.length > 0) {
                                            $('#headingth').text('Bill No.');
                                            let msg = result.msg;
                                            let salerows = result.salerows;
                                            let tdata = '';
                                            salerows.forEach((data, index) => {
                                                tdata += `<tr class="salerow" data-vno="${data.vno}" data-id="${data.restcode}" data-value="${data.roomno}">
                                                <td>${data.vno}</td>
                                                <td>${data.roomno}</td>
                                                <td>${data.waitername}</td>
                                                <td>${data.depname}</td>
                                                <td>Pending</td>
                                            </tr>`;
                                            });
                                            let tfoot = `<tr>
                                            <td>Maxed</td>
                                            </tr>`;
                                            $('#pendingkotnightaudit tfoot').append(tfoot);
                                            $('#pendingkotnightaudit tbody').append(tdata);
                                            $('.pendingkotnightaudit').removeClass('none');
                                            $(document).on('click', '.salerow', function() {
                                                let roomno = $(this).data('value');
                                                let restcode = $(this).data('id');
                                                let vno = $(this).data('vno');
                                                window.location.href =
                                                    `settlemententry?dcode=${restcode}&tableno=${roomno}&vno=${vno}`;
                                            });
                                        }
                                    }
                                }
                            }
                            salewarnxhr.send();
                        }

                    }
                });

                $(document).on('click', '.kotrow, .salerow', function() {
                    let roomno = $(this).data('value');
                    let restcode = $(this).data('id');
                    let vno = $(this).data('vno');
                    let rowcode = "{{ session('nightinfo')['row'] }}";
                    let ur;
                    if (rowcode == 1) {
                        ur = `salebillentry?dcode=${restcode}&roomno=${roomno}`;
                    } else if (rowcode == 2) {
                        ur = `settlemententry?dcode=${restcode}&tableno=${roomno}&vno=${vno}`;
                    }
                    window.location.href = ur;
                });
            </script>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                fetch('/getcompdt', {
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    })
                    .then(response => response.json())
                    .then(results => {
                        // let count = 0;

                        // function showAlertRepeatedly() {
                        //     if (count < 2) {
                        //         Swal.fire({
                        //             title: 'Pending Payment',
                        //             text: 'Your Payment is due. Software will be closed soon. Please make the payment now.',
                        //             icon: 'info',
                        //             confirmButtonText: 'OK'
                        //         }).then(() => {
                        //             count++;
                        //             showAlertRepeatedly();
                        //         });
                        //     }
                        // }

                        // if (results.company.propertyid == '122') {
                        //     showAlertRepeatedly();
                        // }

                        const datemanage = results.datemanage;
                        $('#usernameshow').text(results.user.name);
                        document.getElementById('compnamed').textContent = results.company.comp_name + " (" +
                            results.company.propertyid + ")";
                        document.getElementById('showfinyear').textContent =
                            `${datemanage.finyear.current}-${datemanage.hf.end}`;
                        $('#start_dtdd').text(`01-04-${datemanage.finyear.current}`);
                        $('#end_dtdd').text(`31-03-${datemanage.finyear.nextyear}`);
                    })
                    .catch(error => console.error('Error fetching data:', error));
            });

            $.ajax({
                url: '{{ route('getwpenviro') }}',
                type: 'GET',
                success: function(response) {
                    if (response === true) {
                        $('#wpmsgerror').text(
                            '⚠️ Your WhatsApp balance is low. Please recharge. to send automatic messages');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        </script>

        @include('property.layouts.orderrequests')
