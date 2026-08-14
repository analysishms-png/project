@extends('property.layouts.main')
@section('main-container')
    <div class="content-body">
        <div id="bookingmodal" class="bookingmodal">
            <h3>In House <span class="fa-2xs blinking-text ARDR">Room Status is a color coded indicator that shows
                    the status of the Rooms.</span></h3>
            <div class="booking-status">
                <div class="indicator confirmed">In House</div>
                <div class="indicator confirmreservation">Confirm Reservation</div>
                <div class="indicator billed">Billed</div>
                <div class="indicator delayed">Delayed</div>
                <div class="indicator outoforder">Out of Order</div>
                <div class="indicator maintainence">Maintainence</div>
                <div class="indicator management">Management</div>
                <div class="indicator marriage">Marriage</div>
                <div class="indicator complimentry">Complimentry</div>
                <div class="indicator dirty">Dirty</div>
                <div class="indicator inprogress">In Progress</div>
                {{-- <div class="indicator stayover">Stayover</div>
            <div class="indicator dayusenormal">Dayuse</div>
            <div class="indicator checked-out">Checked Out</div>
            <div class="indicator due-out">Due Out</div>
            <div class="indicator inhouse">Inhouse</div>
            <div class="indicator dayuse">Day Use Reservation</div>
            <div class="indicator maintenance-block">Maintenance Block</div> --}}
            </div>
            {{-- <h3>Booking Indicators</h3>
        <div class="booking-indicators">
            <div class="indicator group-owner"><img alt="Analysishms" src="{{ asset('admin/icons/custom/king.svg') }}">
                Group Owner</div>
            <div class="indicator payment-pending"><img alt="Analysishms"
                    src="{{ asset('admin/icons/custom/dollar.svg') }}"> Payment Pending</div>
            <div class="indicator single-lady"><img alt="Analysishms"
                    src="{{ asset('admin/icons/custom/singlelady.svg') }}"> Single Lady</div>
            <div class="indicator split-reservation"><img alt="Analysishms"
                    src="{{ asset('admin/icons/custom/split.svg') }}"> Split Reservation</div>
            <div class="indicator group-booking"><img alt="Analysishms"
                    src="{{ asset('admin/icons/custom/groupbooking.svg') }}"> Group Booking</div>
            <div class="indicator stop-room-move"><img alt="Analysishms"
                    src="{{ asset('admin/icons/custom/stoproom.svg') }}"> Stop Room Move</div>
            <div class="indicator vip-guest"><img alt="Analysishms" src="{{ asset('admin/icons/custom/star.svg') }}">
                VIP Guest</div>
        </div>
        <h3>Room Indicators</h3>
        <div class="room-indicators">
            <div class="indicator no-smoking"><img alt="Analysishms"
                    src="{{ asset('admin/icons/custom/dhuanotallow.svg') }}"> No Smoking</div>
            <div class="indicator dirty"><img alt="Analysishms" src="{{ asset('admin/icons/custom/dirty.svg') }}"> Dirty
            </div>
            <div class="indicator work-order"><img alt="Analysishms"
                    src="{{ asset('admin/icons/custom/workorder.svg') }}"> Work Order</div>
            <div class="indicator smoking"><img alt="Analysishms" src="{{ asset('admin/icons/custom/ciggerate.svg') }}">
                Smoking</div>
            <div class="indicator connected-rooms"><img alt="Analysishms"
                    src="{{ asset('admin/icons/custom/connroom.svg') }}"> Connected Rooms</div>
        </div>
        <h3>Inventory</h3>
        <div class="inventory">
            <div class="indicator unassigned-room">Unassigned Room</div>
            <div class="indicator unconfirmed-bookings">Unconfirm Bookings</div>
            <div class="indicator confirmed">Confirmed Reservation</div>
            <div class="indicator inventry">Inventry</div>
        </div> --}}
        </div>
        {{-- {{ $ncurdate }} --}}
        <input style="display: none;" type="date" id="fixncur" value="{{ $ncurdate }}">
        <input style="display: none;" type="date" id="sss" value="{{ date('Y-m-d', strtotime('-1 day', strtotime($ncurdate))) }}">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body roomstatus">

                        {{-- <div class="row"> --}}
                        {{-- <div class="col-md-8"> --}}
                        {{-- <div class="table-responsive"> --}}
                        {{-- {{ $roomtype = $roomcategorydata }} --}}

                        <form action="" method="post">
                            <input type="hidden" name="showList" value="">
                        </form>
                        <table style="background: #ffffff;margin: 0 0 -4px -14px;" class="table table-primary">
                            <thead>
                                <tr>
                                    <th>
                                        <input value="{{ date('Y-m-d', strtotime('-1 day', strtotime($ncurdate))) }}" class="form-control rhead" type="date"
                                            id="fromdate">
                                    </th>
                                    <th>
                                        <input type="search" placeholder="Search..." class="form-control rhead"
                                            name="guestsearch" id="guestsearch">
                                    </th>
                                    <th style="cursor: pointer;" id="infoicon"><i class="fa-regular fa-circle-question"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>

                        {{-- <div
                                    style="position: fixed; top: 0; right: 0; padding: 10px; background-color: #f1f1f1;">
                                    <i class="fa-regular fa-circle-question"></i>
                                </div> --}}
                        {{--
                            </div>
                        </div>
                    </div> --}}

                        <table class="table-responsive table" id="dateTable"></table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="changeprofilemodal" tabindex="-1" aria-labelledby="changeprofilemodalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div style="width: 57rem;" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeprofilemodalLabel">Profile Change For: <span class="ADA"
                            id="profilechangespan"></span></h5>
                    <h5 style="right: 3rem;" class="modal-title absolute-element" id="changeprofilemodalLabel">Folio No.:
                        <span class="BANX" id="profilechangecode"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe id="changeprofileframe" src="" frameborder="0" style="width: 100%; height: 60rem;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ammendstaymodal" tabindex="-1" aria-labelledby="ammendstaymodalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ammendstaymodalLabel">Ammend Stay For: <span class="ADA"
                            id="ammendstayspan"></span></h5>
                    <h5 style="right: 3rem;" class="modal-title absolute-element" id="ammendstaymodalLabel">Folio No.:
                        <span class="BANX" id="guestcode1"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe id="ammenstayiframe" src="" frameborder="0" style="width: 100%; height: 15em;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="guestledgermodal" tabindex="-1" aria-labelledby="guestledgermodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="guestledgermodalLabel">Guest Ledger For: <span class="ADA"
                            id="guestledgerspan"></span></h5>
                    <h5 style="right: 3rem;" class="modal-title absolute-element" id="guestledgermodalLabel">Folio No.:
                        <span class="BANX" id="guestcode2"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe id="guestledgeriframe" src="" frameborder="0" style="width: 100%; height: 35em;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="guestchargemodal" tabindex="-1" aria-labelledby="guestchargemodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="guestchargemodalLabel">Guest Charge Summary For: <span class="ADA"
                            id="guestchargespan"></span></h5>
                    <h5 style="right: 3rem;" class="modal-title absolute-element" id="guestchargemodalLabel">Folio No.:
                        <span class="BANX" id="guestcode7"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe id="guestchargeiframe" src="" frameborder="0" style="width: 100%; height: 35em;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="roomchangemodal" tabindex="-1" aria-labelledby="roomchangemodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roomchangemodalLabel">Room Change For: <span class="ADA"
                            id="roomchangespan"></span></h5>
                    <h5 style="right: 3rem;" class="modal-title absolute-element" id="roomchangemodalLabel">Folio No.:
                        <span style="display: none;" id="docidd"></span>
                        <span class="BANX" id="guestcode3"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe id="roomchangeiframe" src="" frameborder="0" style="width: 100%; height: 37em;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="advchargemodal" tabindex="-1" aria-labelledby="advchargemodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="advchargemodalLabel">Advance Change For: <span class="ADA"
                            id="advchargespan"></span></h5>
                    <h5 style="right: 3rem;" class="modal-title absolute-element" id="advchargemodalLabel">Folio No.:
                        <span style="display: none;" id="docidd"></span>
                        <span class="BANX" id="guestcode4"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe id="advchargeiframe" src="" frameborder="0" style="width: 100%; height: 37em;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="billsettlemodal" tabindex="-1" aria-labelledby="billsettlemodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="billsettlemodalLabel">Bill Settlement For: <span class="ADA"
                            id="billsettlespan"></span></h5>
                    <h5 style="right: 3rem;" class="modal-title absolute-element" id="billsettlemodalLabel">Folio No.:
                        <span style="display: none;" id="docidd"></span>
                        <span class="BANX" id="guestcode6"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe id="billsettleiframe" src="" frameborder="0" style="width: 100%; height: 37em;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="billprintmodal" tabindex="-1" aria-labelledby="billprintmodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="billprintmodalLabel">Bill Print For: <span class="ADA"
                            id="billprintspan"></span></h5>
                    <h5 style="right: 3rem;" class="modal-title absolute-element" id="billprintmodalLabel">Folio No.:
                        <span class="BANX" id="guestcode5"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe id="billprintiframe" src="" frameborder="0" style="width: 100%; height: 35em;"></iframe>
                </div>
            </div>
        </div>
    </div>
    <style>
        #dateTable tr td.roomstatus-stack-cell {
            padding: 0 !important;
            vertical-align: top;
            background: #fff !important;
            background-image: none !important;
        }

        #dateTable tr td .roomstatus-stack {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-height: 100%;
        }

        #dateTable tr td .roomstatus-segment.roomstatus-segment-crown {
            gap: 2px;
        }

        #dateTable tr td .roomstatus-segment .roomstatus-segment-crown-icon {
            width: 14px;
            height: 14px;
            object-fit: contain;
        }

        #dateTable tr td .roomstatus-segment:hover {
            filter: brightness(0.98);
        }

        #dateTable tr td .roomstatus-segment.roomstatus-search-match {
            outline: 2px solid #111;
            box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.55);
            position: relative;
            z-index: 2;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#myloader').removeClass('none');
        setTimeout(() => {
            $('#myloader').addClass('none');
        }, 2000);
        let activePopup = null;
        let roomStatusSearchAnimationFrame = null;
        let roomStatusSearchDebounceTimer = null;
        let roomStatusMatchedSegments = [];
        let roomStatusActiveMatchIndex = -1;

        $(document).ready(function() {
            function exportdateformat(formatteddate) {
                datearray = formatteddate;
            }

            $(document).on('click', '#roomcategorybtn', function() {
                $('#listroomcat').toggle();
            });

            $(document).on('change', '.roomcatcheckbox', function() {
                if (!$(this).is(':checked')) {
                    let valueToHide = $(this).val();
                    $('#dateTable tbody tr').each(function() {
                        if ($(this).data('value') == valueToHide) {
                            $(this).hide();
                        }
                    });
                } else {
                    let valueToHide = $(this).val();
                    $('#dateTable tbody tr').each(function() {
                        if ($(this).data('value') == valueToHide) {
                            $(this).show();
                        }
                    });
                }
            });

            $(document).on('click', '#infoicon', function() {
                let modal = $('#bookingmodal');
                if (modal.css('display') === 'none') {
                    modal.css('display', 'block');
                } else {
                    modal.css('display', 'none');
                }
            });

            function generateDateRow() {
                let startDate = document.getElementById('fromdate').value;
                let date = new Date(startDate);
                let table = document.getElementById('dateTable');
                table.innerHTML = '';
                let row = table.insertRow();
                row.classList.add('dateheader');

                let firstCell = row.insertCell();
                let nameSpan = document.createElement('span');
                nameSpan.innerHTML = `
                    <button style="width: -webkit-fill-available;" type="button" class="btn rhead btn-outline-primary"
                            name="roomcategorybtn" id="roomcategorybtn">Room Type <i
                            class="fa-solid fa-angle-down"></i></button>
                        <ul id="listroomcat" style="display:none;">
                            @foreach ($roomcategorydata as $item)
                                <li>
                                    <input class="roomcatcheckbox" value="{{ $item->cat_code }}" type="checkbox" checked>
                                    <span>{{ $item->name }}</span>
                                </li>
                            @endforeach
                        </ul>`;
                firstCell.appendChild(nameSpan);

                for (let i = 1; i <= 30; i++) {
                    let cell = row.insertCell();
                    cell.classList.add('dateheadertd');
                    cell.dataset.nu = i;

                    let year = date.getFullYear();
                    let monthNum = String(date.getMonth() + 1).padStart(2, '0');
                    let dayNum = String(date.getDate()).padStart(2, '0');

                    let completedate = `${year}-${monthNum}-${dayNum}`;
                    cell.dataset.date = completedate;

                    let monthSpan = document.createElement('span');
                    monthSpan.textContent = new Intl.DateTimeFormat('en-US', {
                        month: 'short'
                    }).format(date);
                    cell.appendChild(monthSpan);

                    cell.appendChild(document.createElement('br'));

                    let strong = document.createElement("strong");
                    strong.textContent = date.getDate();
                    cell.appendChild(strong);

                    let yearSpan = document.createElement('span');
                    yearSpan.style.display = 'none';
                    yearSpan.textContent = year;
                    cell.appendChild(yearSpan);

                    cell.appendChild(document.createElement('br'));

                    let daySpan = document.createElement('span');
                    daySpan.textContent = new Intl.DateTimeFormat('en-US', {
                        weekday: 'short'
                    }).format(date);
                    cell.appendChild(daySpan);

                    exportdateformat(completedate);

                    date.setDate(date.getDate() + 1);
                }

                async function fetchRoomCategory() {
                    try {
                        const response = await fetch('/roomcategoryget');
                        const categories = await response.json();

                        let table = document.getElementById('dateTable');

                        for (let category of categories) {

                            // 🔹 CATEGORY ROW
                            let row = table.insertRow();

                            // first column (category name)
                            let cell = row.insertCell();
                            cell.classList.add('rstatuscatname');
                            cell.setAttribute('data-cat', category.cat_code);

                            $(cell).html(`${category.name} <span class="badge badge-primary">${category.norooms}</span>`);

                            row.dataset.value = category.cat_code;

                            for (let i = 1; i <= 30; i++) {
                                let td = row.insertCell();
                                const header = document.querySelectorAll('.dateheadertd')[i - 1];
                                td.className = 'cat-avail-cell cursor-pointer';
                                td.dataset.cat = category.cat_code;
                                td.dataset.date = $(header).data('date');
                                td.innerHTML = '';
                            }

                            for (let room of await fetchRooms(category.cat_code)) {
                                row = table.insertRow();

                                cell = row.insertCell();

                                if (
                                    room.room_stat === 'D' &&
                                    "{{ fomparameter()->housekeepingroomstatus }}" == 1 &&
                                    room.cleaningstatus === 'In Progress'
                                ) {
                                    const cleaning = getCleaningTimeStatus(room.vtime, room.esttime);

                                    cell.style.color = cleaning.isOver ? '#dc3545' : '#385ceb';

                                    cell.innerHTML = `${room.rcode} <i class="fa-solid fa-broom"></i><br>
                                                    <small>${cleaning.text}</small>`;
                                } else if (room.room_stat === 'D' && "{{ fomparameter()->housekeepingroomstatus }}" == 1) {
                                    cell.style.color = '#ffc107';
                                    cell.innerHTML = `${room.rcode} <i class="fa-solid fa-smog"></i>`;
                                } else {
                                    cell.textContent = room.rcode;
                                }

                                row.dataset.value = room.room_cat;

                                for (let i = 1; i <= 30; i++) {
                                    cell = row.insertCell();
                                    cell.classList.add('roomstatuscell');
                                    cell.innerHTML = '&nbsp;';
                                    cell.dataset.value = room.rcode;

                                    const querySelector = document.querySelectorAll('.dateheadertd')[i - 1];
                                    const htmlContent = querySelector.innerHTML;

                                    const year = htmlContent.match(/(\d{4})/)[1];
                                    const dateMatch = htmlContent.match(/<strong>(\d+)<\/strong>/);
                                    const date = dateMatch ? dateMatch[1].padStart(2, '0') : null;

                                    const monthName = htmlContent.match(/<span>([a-zA-Z]+)<\/span>/)[1];
                                    const monthNumber = (new Date(Date.parse(monthName + " 1, " + year)).getMonth() + 1)
                                        .toString()
                                        .padStart(2, '0');

                                    const fromdate = year + '-' + monthNumber + '-' + date;

                                    cell.headers = fromdate;
                                }
                            }
                        }

                    } catch (error) {
                        console.error('Error:', error);
                    }
                }

                async function fetchRooms(categoryCode) {
                    try {
                        const response = await fetch('/roomget', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            },
                            body: JSON.stringify({
                                categoryCode: categoryCode,
                            }),
                        });

                        const rooms = await response.json();
                        return rooms;
                    } catch (error) {
                        console.error('Error:', error);
                    }
                }

                function popupblockout(roomblockout) {
                    roomblockout.forEach(function(rdata) {
                        const roomno = rdata.roomcode;
                        const fromdate = new Date(rdata.fromdate);
                        const todate = new Date(rdata.todate);
                        const block = rdata.block;
                        const reasons = rdata.reasons;
                        const alltd = $('td[data-value="' + roomno + '"]');

                        alltd.each(function() {
                            const td = $(this);
                            const headers = new Date(td.attr('headers'));

                            if (headers >= fromdate && headers <= todate) {
                                if (rdata.block == 'Out of Order') {
                                    td.addClass('oorder').text(block);
                                    td.append('<div class="opopshow">' + reasons + '</div>');
                                }
                                if (rdata.block == 'Maintainence') {
                                    td.addClass('maintainence').text(block);
                                    td.append('<div class="opopshow">' + reasons + '</div>');
                                }
                                if (rdata.block == 'Management') {
                                    td.addClass('management').text(block);
                                    td.append('<div class="opopshow">' + reasons + '</div>');
                                }
                                if (rdata.block == 'Marriage') {
                                    td.addClass('marriage').text(block);
                                    td.append('<div class="opopshow">' + reasons + '</div>');
                                }
                            }
                        });
                    });
                }

                async function fetchBookedRooms() {
                    try {
                        const response = await fetch('/bookedroomget');

                        if (response.ok) {
                            const datas = await response.json();
                            const data = datas.bookedroomdata;
                            const amountdetails = datas.amountdetails;
                            let roomblockout = datas.roomblockout;
                            if (roomblockout.length > 0) {
                                popupblockout(roomblockout);
                            }
                            if (data.length > 0) {
                                data.forEach(function(booking) {
                                    const name = booking.name || '';
                                    const roomno = booking.roomno;
                                    const [firstName] = name.split(' ');
                                    let tmpncurdate = document.getElementById('sss').value;
                                    let options = {
                                        timeZone: 'Asia/Kolkata',
                                        hour12: false,
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        second: '2-digit'
                                    };
                                    let currentTime = new Date().toLocaleString('en-US', options);

                                    const getInHouseStatusClass = function(currentBooking) {
                                        if (currentBooking.billno != '0') {
                                            return 'billedtd';
                                        }

                                        if (currentTime > currentBooking.envcheck && currentBooking.billno == '0' && currentBooking.depdate_minus_one <= tmpncurdate) {
                                            return 'delaytd';
                                        }

                                        if (currentBooking.complimentry == 'Y') {
                                            return 'complimentry';
                                        }

                                        return 'fromtd';
                                    };

                                    appendRoomStatusRange(roomno, booking.chkindate, booking.depdate_minus_one, {
                                        text: buildRoomStatusLabel(booking.con_prefix, firstName),
                                        statusClass: getInHouseStatusClass(booking),
                                        crown: booking.leaderyn == 'Y',
                                        travellogo: booking.travellogo || 'N',
                                        travelname: booking.travelname || '',
                                        searchName: booking.GuestName || booking.name || '',
                                        segmentKey: ['inhouse', booking.docid, booking.sno1, booking.roomno, booking.chkindate, booking.depdate_minus_one].join('|'),
                                        onClick: function() {
                                            OpenBookingInfoModal(booking, amountdetails);
                                        }
                                    });
                                });
                            } else {
                                pushNotify('info', 'Room Status', 'No Booked Rooms Data Found', 'fade', 300, '', '', true, true, true, 2000, 20, 20, 'outline', 'left top');
                            }
                        } else {
                            console.error('Failed to fetch booked rooms. Status:', response.status);
                        }
                    } catch (error) {
                        console.error('An error occurred during the fetch:', error);
                    }
                }

                async function fetchBookedRoomsres() {
                    try {
                        const response = await fetch('/reservedroomget');

                        if (response.ok) {
                            const data = await response.json();
                            const bookingdata = data.bookedroomdata;

                            if (bookingdata.length > 0) {
                                bookingdata.forEach(function(booking) {
                                    const name = booking.GuestName || '';
                                    const roomno = booking.RoomNo;
                                    const [firstName] = name.split(' ');
                                    appendRoomStatusRange(roomno, booking.ArrDate, booking.depdate_minus_one, {
                                        text: buildRoomStatusLabel(booking.con_prefix, firstName),
                                        statusClass: 'fromtd2',
                                        travellogo: booking.travellogo || 'N',
                                        travelname: booking.travelname || '',
                                        searchName: booking.GuestName || '',
                                        segmentKey: ['reserved', booking.BookingDocid, booking.Sno, booking.RoomNo, booking.ArrDate, booking.depdate_minus_one].join('|'),
                                        onClick: function() {
                                            openbookngreservemodal(booking);
                                        }
                                    });
                                });
                            } else {
                                pushNotify('info', 'Room Status', 'No Reserved Rooms Data Found', 'fade', 300, '', '', true, true, true, 2000, 20, 20, 'outline', 'right top');
                            }
                        } else {
                            console.error('Failed to fetch booked rooms. Status:', response.status);
                        }
                    } catch (error) {
                        console.error('An error occurred during the fetch:', error);
                    }
                }

                function getAllDatesFromHeader() {
                    let dates = [];

                    $('.dateheadertd').each(function() {
                        let date = $(this).data('date');
                        if (date) {
                            dates.push(date);
                        }
                    });

                    return dates;
                }

                function fetchAvailabilityFromServer(dates) {
                    fetch('/get-availability', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            body: JSON.stringify({
                                dates: dates
                            })
                        })
                        .then(res => res.json())
                        .then(data => {

                            if ($('#availabilityRow').length === 0) {
                                createAvailabilityRow();
                            }

                            $('.avail-cell').each(function() {
                                let date = $(this).data('date');

                                if (data[date] !== undefined) {
                                    let totalAvail = data[date].total_available;
                                    let totalRooms = Object.values(data[date].categories)
                                        .reduce((sum, c) => sum + c.total, 0);

                                    $(this).text(totalAvail);

                                    // reset
                                    $(this).removeClass('avail-low avail-medium avail-high');

                                    let percent = (totalAvail / totalRooms) * 100;

                                    if (percent < 30) {
                                        $(this).addClass('avail-low');
                                    } else if (percent < 70) {
                                        $(this).addClass('avail-medium');
                                    } else {
                                        $(this).addClass('avail-high');
                                    }
                                }
                            });


                            $('.cat-avail-cell').each(function() {
                                let date = $(this).data('date');
                                let cat = $(this).data('cat');

                                if (
                                    data[date] &&
                                    data[date].categories &&
                                    data[date].categories[cat]
                                ) {
                                    let c = data[date].categories[cat];

                                    $(this).text(`${c.available}/${c.total}`);

                                    // reset
                                    $(this).removeClass('avail-low avail-medium avail-high');

                                    let percent = (c.available / c.total) * 100;

                                    if (percent < 30) {
                                        $(this).addClass('avail-low');
                                    } else if (percent < 70) {
                                        $(this).addClass('avail-medium');
                                    } else {
                                        $(this).addClass('avail-high');
                                    }
                                }
                            });

                            if ($('#occupancyRow').length === 0) {
                                createOccupancyRow();
                            }

                            $('.occ-cell').each(function() {
                                const date = $(this).data('date');

                                if (data[date]) {
                                    let occ = parseFloat(data[date].occupancy);

                                    $(this).text(occ + ' %');

                                    // reset classes
                                    $(this).removeClass('occ-low occ-medium occ-high');

                                    if (occ < 50) {
                                        $(this).addClass('occ-low');
                                    } else if (occ < 80) {
                                        $(this).addClass('occ-medium');
                                    } else {
                                        $(this).addClass('occ-high');
                                    }
                                }
                            });

                        });
                }

                function createAvailabilityRow() {
                    let row = document.createElement('tr');
                    row.id = 'availabilityRow';

                    let firstCell = document.createElement('td');
                    firstCell.textContent = 'TOTAL AVL';
                    firstCell.style.fontWeight = 'bold';
                    firstCell.style.background = '#1f4e79';
                    firstCell.style.color = '#fff';
                    row.appendChild(firstCell);

                    $('.dateheadertd').each(function() {

                        let td = document.createElement('td');

                        td.className = 'avail-cell cursor-pointer';
                        td.dataset.date = $(this).data('date');

                        td.style.background = '#1f4e79';
                        td.style.color = '#fff';
                        td.style.textAlign = 'center';

                        row.appendChild(td);

                    });

                    $('#dateTable tbody tr.dateheader').nextAll('tr').first().before(row);
                }

                function createOccupancyRow() {
                    let row = document.createElement('tr');
                    row.id = 'occupancyRow';

                    let firstCell = document.createElement('td');
                    firstCell.textContent = 'Occupancy';
                    firstCell.style.fontWeight = 'bold';
                    firstCell.style.background = '#1f4e79';
                    firstCell.style.color = '#fff';
                    row.appendChild(firstCell);

                    $('.dateheadertd').each(function() {
                        let td = document.createElement('td');
                        td.classList.add('occ-cell');
                        td.setAttribute('data-date', $(this).data('date'));

                        td.style.background = '#1f4e79';
                        td.style.color = '#fff';
                        td.style.textAlign = 'center';

                        row.appendChild(td);
                    });

                    $('#dateTable').find('tr:last').after(row);
                }

                setTimeout(() => {
                    let dates = getAllDatesFromHeader();
                    fetchAvailabilityFromServer(dates);
                }, 2000);

                async function fetchCheckoutRooms() {
                    try {
                        const startDate = document.getElementById('fromdate').value;
                        const dateHeader = document.querySelector('tr.dateheader');
                        const lastDateCell = dateHeader?.lastElementChild;
                        const lastDateStr = lastDateCell?.getAttribute('data-date');

                        const url = new URL('/checkoutroomget', window.location.origin);
                        if (startDate) url.searchParams.append('fromdate', startDate);
                        if (lastDateStr) url.searchParams.append('todate', lastDateStr);

                        const response = await fetch(url.toString());
                        if (response.ok) {
                            const data = await response.json();
                            const bookingdata = data.checkoutroomdata;
                            const amountdetails = data.amountdetails;
                            const isCheckout = data.isCheckout || false;
                            if (bookingdata.length > 0) {
                                bookingdata.forEach(function(booking) {
                                    const name = booking.name || '';
                                    const roomno = booking.roomno;
                                    const [firstName] = name.split(' ');
                                    appendRoomStatusRange(roomno, booking.chkindate, booking.depdate_minus_one, {
                                        text: buildRoomStatusLabel(booking.con_prefix, firstName),
                                        statusClass: 'checkouttd',
                                        travellogo: booking.travellogo || 'N',
                                        travelname: booking.travelname || '',
                                        searchName: booking.GuestName || booking.name || '',
                                        segmentKey: ['checkout', booking.docid, booking.sno1, booking.roomno, booking.chkindate, booking.depdate_minus_one].join('|'),
                                        onClick: function() {
                                            OpenBookingInfoModal(booking, amountdetails, isCheckout);
                                        }
                                    });
                                });
                            }
                        }
                    } catch (error) {
                        console.error('Error fetching checkout rooms:', error);
                    }
                }

                setTimeout(() => {
                    fetchBookedRooms();
                    fetchBookedRoomsres();
                    fetchCheckoutRooms();
                }, 5000);

                fetchRoomCategory();

            }

            function buildRoomStatusLabel(prefix, firstName) {
                return [prefix ?? '', firstName ?? ''].join(' ').replace(/\s+/g, ' ').trim();
            }

            function normalizeRoomStatusDate(dateValue) {
                if (!dateValue) {
                    return '';
                }

                if (dateValue instanceof Date) {
                    const year = dateValue.getFullYear();
                    const month = String(dateValue.getMonth() + 1).padStart(2, '0');
                    const day = String(dateValue.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                }

                const normalizedValue = String(dateValue).trim().substring(0, 10);
                const parsedDate = new Date(normalizedValue);
                if (Number.isNaN(parsedDate.getTime())) {
                    return normalizedValue;
                }

                return normalizeRoomStatusDate(parsedDate);
            }

            function appendRoomStatusRange(roomno, startDate, endDate, options) {
                const normalizedStartDate = normalizeRoomStatusDate(startDate);
                const normalizedEndDate = normalizeRoomStatusDate(endDate);

                if (!roomno || !normalizedStartDate || !normalizedEndDate || normalizedStartDate > normalizedEndDate) {
                    return;
                }

                const visibleDates = Array.from(document.querySelectorAll('.dateheadertd'))
                    .map(function(headerCell) {
                        return headerCell.dataset.date;
                    })
                    .filter(Boolean);

                if (!visibleDates.length) {
                    return;
                }

                const visibleStartDate = normalizedStartDate < visibleDates[0] ? visibleDates[0] : normalizedStartDate;
                const visibleEndDate = normalizedEndDate > visibleDates[visibleDates.length - 1] ? visibleDates[visibleDates.length - 1] : normalizedEndDate;

                if (visibleStartDate > visibleEndDate) {
                    return;
                }

                const startCell = document.querySelector(`td[data-value="${roomno}"][headers="${visibleStartDate}"]`);
                if (!startCell) {
                    return;
                }

                const startIndex = visibleDates.indexOf(visibleStartDate);
                const endIndex = visibleDates.indexOf(visibleEndDate);

                if (startIndex === -1 || endIndex === -1 || endIndex < startIndex) {
                    return;
                }

                let spanCount = 1;
                let nextCell = startCell.nextElementSibling;
                const targetSpanCount = endIndex - startIndex + 1;

                while (spanCount < targetSpanCount && nextCell) {
                    if (nextCell.matches('td[headers]')) {
                        const cellToRemove = nextCell;
                        nextCell = nextCell.nextElementSibling;
                        cellToRemove.remove();
                        spanCount += 1;
                        continue;
                    }

                    nextCell = nextCell.nextElementSibling;
                }

                startCell.colSpan = spanCount;
                startCell.dataset.rangeStart = visibleStartDate;
                startCell.dataset.rangeEnd = visibleDates[startIndex + spanCount - 1] || visibleStartDate;
                appendRoomStatusSegment(startCell, options);
            }

            function getRoomStatusSegmentStyles(statusClass, crown = false) {
                const styleMap = {
                    fromtd: {
                        backgroundColor: '#FAD5CD'
                    },
                    betweentd: {
                        backgroundColor: '#FAD5CD'
                    },
                    totd: {
                        backgroundColor: '#FAD5CD'
                    },
                    delaytd: {
                        backgroundColor: '#f4d35e'
                    },
                    complimentry: {
                        backgroundColor: '#f61067',
                        color: '#fff'
                    },
                    billedtd: {
                        backgroundColor: '#e9cdfa'
                    },
                    fromtd2: {
                        backgroundColor: '#cdfad5'
                    },
                    betweentd2: {
                        backgroundColor: '#cdfad5'
                    },
                    totd2: {
                        backgroundColor: '#cdfad5'
                    },
                    checkouttd: {
                        backgroundColor: '#ADD8E6'
                    }
                };

                const styles = Object.assign({
                    backgroundColor: '#fff',
                    color: '#000'
                }, styleMap[statusClass] || {});

                if (crown) {
                    styles.fontWeight = '700';
                }

                return styles;
            }

            function normalizeGuestSearchTerm(value) {
                return String(value || '').trim().toLowerCase();
            }

            function clearRoomStatusSearchMatch() {
                document.querySelectorAll('#dateTable .roomstatus-segment.roomstatus-search-match').forEach(function(segment) {
                    segment.classList.remove('roomstatus-search-match');
                });
            }

            function getRoomStatusMatchingSegments(searchValue) {
                const normalizedSearchValue = normalizeGuestSearchTerm(searchValue);

                if (!normalizedSearchValue) {
                    return [];
                }

                return Array.from(document.querySelectorAll('#dateTable .roomstatus-segment')).filter(function(segment) {
                    const searchableText = [
                        segment.dataset.searchName || '',
                        segment.textContent || '',
                        segment.title || ''
                    ].join(' ').toLowerCase();

                    return searchableText.includes(normalizedSearchValue);
                });
            }

            function activateRoomStatusMatch(matchIndex) {
                clearRoomStatusSearchMatch();

                if (!roomStatusMatchedSegments.length) {
                    roomStatusActiveMatchIndex = -1;
                    return;
                }

                const normalizedIndex = ((matchIndex % roomStatusMatchedSegments.length) + roomStatusMatchedSegments.length) % roomStatusMatchedSegments.length;
                const matchingSegment = roomStatusMatchedSegments[normalizedIndex];

                if (!matchingSegment || !document.body.contains(matchingSegment)) {
                    roomStatusMatchedSegments = [];
                    roomStatusActiveMatchIndex = -1;
                    return;
                }

                roomStatusActiveMatchIndex = normalizedIndex;
                matchingSegment.classList.add('roomstatus-search-match');
                matchingSegment.focus({
                    preventScroll: true
                });
                matchingSegment.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                    inline: 'center'
                });
            }

            function focusRoomStatusSegmentByGuestName(searchValue, preferredIndex = 0) {
                roomStatusMatchedSegments = getRoomStatusMatchingSegments(searchValue);

                if (!roomStatusMatchedSegments.length) {
                    clearRoomStatusSearchMatch();
                    roomStatusActiveMatchIndex = -1;
                    return;
                }

                activateRoomStatusMatch(preferredIndex);
            }

            function debounceRoomStatusSearch(searchValue) {
                if (roomStatusSearchDebounceTimer) {
                    clearTimeout(roomStatusSearchDebounceTimer);
                }

                roomStatusSearchDebounceTimer = setTimeout(function() {
                    roomStatusSearchDebounceTimer = null;
                    focusRoomStatusSegmentByGuestName(searchValue);
                }, 450);
            }

            function moveRoomStatusSearchMatch(step) {
                const currentSearchValue = document.getElementById('guestsearch').value;

                if (!normalizeGuestSearchTerm(currentSearchValue)) {
                    return false;
                }

                const latestMatches = getRoomStatusMatchingSegments(currentSearchValue);
                if (!latestMatches.length) {
                    roomStatusMatchedSegments = [];
                    roomStatusActiveMatchIndex = -1;
                    clearRoomStatusSearchMatch();
                    return false;
                }

                roomStatusMatchedSegments = latestMatches;

                let nextIndex = roomStatusActiveMatchIndex;
                if (nextIndex === -1) {
                    nextIndex = step > 0 ? 0 : roomStatusMatchedSegments.length - 1;
                } else {
                    nextIndex += step;
                }

                activateRoomStatusMatch(nextIndex);
                return true;
            }

            function shouldHandleRoomStatusTabNavigation() {
                const activeElement = document.activeElement;
                return activeElement &&
                    (activeElement.id === 'guestsearch' || activeElement.classList.contains('roomstatus-segment')) &&
                    normalizeGuestSearchTerm(document.getElementById('guestsearch').value);
            }

            function handleRoomStatusSearchTab(event) {
                if (event.key !== 'Tab' || !shouldHandleRoomStatusTabNavigation()) {
                    return;
                }

                if (!moveRoomStatusSearchMatch(event.shiftKey ? -1 : 1)) {
                    return;
                }

                event.preventDefault();
            }

            function scheduleRoomStatusSearchSync() {
                if (roomStatusSearchAnimationFrame) {
                    cancelAnimationFrame(roomStatusSearchAnimationFrame);
                }

                roomStatusSearchAnimationFrame = requestAnimationFrame(function() {
                    roomStatusSearchAnimationFrame = null;
                    const searchValue = document.getElementById('guestsearch').value;
                    if (!normalizeGuestSearchTerm(searchValue)) {
                        clearRoomStatusSearchMatch();
                        roomStatusMatchedSegments = [];
                        roomStatusActiveMatchIndex = -1;
                        return;
                    }

                    const existingActiveSegment = roomStatusMatchedSegments[roomStatusActiveMatchIndex];
                    const latestMatches = getRoomStatusMatchingSegments(searchValue);
                    roomStatusMatchedSegments = latestMatches;

                    if (!latestMatches.length) {
                        clearRoomStatusSearchMatch();
                        roomStatusActiveMatchIndex = -1;
                        return;
                    }

                    const existingIndex = existingActiveSegment ? latestMatches.indexOf(existingActiveSegment) : -1;
                    activateRoomStatusMatch(existingIndex >= 0 ? existingIndex : 0);
                });
            }

            function appendRoomStatusSegment(td, options) {
                if (!td) {
                    return;
                }

                const removableCellClasses = ['fromtd', 'totd', 'betweentd', 'delaytd', 'complimentry', 'billedtd', 'fromtd2', 'totd2', 'betweentd2', 'crown'];
                td.classList.remove(...removableCellClasses);
                td.classList.add('roomstatus-stack-cell');

                let stack = td.querySelector('.roomstatus-stack');
                if (!stack) {
                    td.innerHTML = '';
                    stack = document.createElement('div');
                    stack.className = 'roomstatus-stack';
                    td.appendChild(stack);
                }

                const segment = document.createElement('button');
                segment.type = 'button';
                segment.className = 'roomstatus-segment';
                segment.title = options.searchName || options.text || '';

                if (options.travellogo && options.travellogo !== 'N') {
                    const logoUrl = "{{ asset('uploads/favicons') }}/" + encodeURIComponent(options.travellogo);

                    const logoImg = document.createElement('img');
                    logoImg.src = logoUrl;
                    logoImg.alt = options.travelname || 'Travel Logo';
                    logoImg.title = options.travelname || '';
                    logoImg.className = 'roomstatus-segment-logo';

                    segment.appendChild(logoImg);

                    if (options.travelname && options.travelname.trim() !== '') {
                        const travelName = document.createElement('span');
                        travelName.className = 'roomstatus-segment-travelname';

                        travelName.textContent = options.travelname.length > 9 ?
                            options.travelname.substring(0, 9) + '...' :
                            options.travelname;

                        travelName.title = options.travelname;

                        segment.appendChild(travelName);
                    }
                }

                if (options.crown) {
                    segment.classList.add('roomstatus-segment-crown');

                    const crownIcon = document.createElement('img');
                    crownIcon.src = "{{ asset('admin/icons/custom/king.svg') }}";
                    crownIcon.alt = 'Leader Room';
                    crownIcon.className = 'roomstatus-segment-crown-icon';
                    segment.appendChild(crownIcon);
                }

                const segmentText = document.createElement('span');
                segmentText.textContent = options.text || '';
                segment.appendChild(segmentText);

                const segmentStyles = getRoomStatusSegmentStyles(options.statusClass, options.crown);
                Object.keys(segmentStyles).forEach(function(styleKey) {
                    segment.style[styleKey] = segmentStyles[styleKey];
                });

                if (typeof options.onClick === 'function') {
                    segment.addEventListener('click', function(event) {
                        event.stopPropagation();
                        options.onClick();
                    });
                }

                if (options.segmentKey) {
                    const duplicateSegment = Array.from(stack.children).find(function(existingSegment) {
                        return existingSegment.dataset.segmentKey === options.segmentKey;
                    });

                    if (duplicateSegment) {
                        return;
                    }

                    segment.dataset.segmentKey = options.segmentKey;
                }

                if (options.searchName) {
                    segment.dataset.searchName = normalizeGuestSearchTerm(options.searchName);
                }

                stack.appendChild(segment);
                scheduleRoomStatusSearchSync();
            }

            document.getElementById('guestsearch').addEventListener('input', function(event) {
                debounceRoomStatusSearch(event.target.value);
            });

            document.getElementById('guestsearch').addEventListener('search', function(event) {
                focusRoomStatusSegmentByGuestName(event.target.value);
            });

            document.addEventListener('keydown', handleRoomStatusSearchTab);

            document.getElementById('fromdate').addEventListener('change', generateDateRow);

            generateDateRow();
        });
    </script>

    {{-- <script src="{{ asset('admin/js/room.js') }}"></script> --}}


    <script>
        var globalguestcode;
        var globalname;
        var globalfoliono;
        var globaldocid;
        var globalsno1;
        var globalsno;
        var globalroomno;
        var globaldepdata;
        var globalncurdate;
        var globalleaderyn;

        var rglobaldocid
        var rglobalsno1

        function OpenBookingInfoModal(bookingarray, amountdetails, isCheckout = false) {
            if (!document.querySelector('.modal-packed')) {
                let leaderyn = bookingarray['leaderyn'];
                let paydetail;
                if (leaderyn == 'Y') {
                    paydetail = amountdetails.filter(x => x.docid == bookingarray['docid']);
                } else {
                    paydetail = amountdetails.filter(x => x.docid == bookingarray['docid'] && x.sno1 == bookingarray['sno1']);
                }

                let totalamt = 0.00;
                let paidamt = 0.00;
                let balance = 0.00;
                paydetail.forEach((data) => {
                    totalamt += parseFloat(data.totalamt);
                    paidamt += parseFloat(data.paidamt);
                    balance += parseFloat(data.balance);
                });

                globalguestcode = bookingarray['guestcode'];
                folioNo = bookingarray['folioNo'];
                globaldocid = bookingarray['docid'];
                globalsno1 = bookingarray['sno1'];
                globalsno = bookingarray['sno'];
                globalroomno = bookingarray['roomno'];
                globaldepdata = bookingarray['depdate'];
                globalleaderyn = bookingarray['leaderyn'];
                let crowncls = globalleaderyn == 'Y' ? 'crown' : '';
                globalncurdate = $('#fixncur').val();
                document.getElementById('docidd').value = globaldocid;
                let prefix = bookingarray['con_prefix'] ?? '';
                globalname = prefix + ' ' + bookingarray['name'];
                const div1 = document.createElement('div');
                div1.classList.add('modal-packed');
                const modal = document.createElement('div');
                modal.classList.add('modal-custom');
                let formbtndisplay;
                let billprintbtndisp;
                if (bookingarray['billno'] == '0') {
                    formbtndisplay = 'none';
                    billprintbtndisp = 'block';
                } else {
                    formbtndisplay = 'block';
                    billprintbtndisp = 'none';
                }
                let buttonGroupDisplay = isCheckout ? 'none' : 'block';
                let chkoutstring = `<p>Checkout: ${new Date(bookingarray['chkoutdate']).toLocaleDateString('en-GB').replace(/\//g,'-')}</p>`;
                modal.innerHTML = `
                    <div class="modal-content content-packed">
                        <div style="display:contents;">
                            <h3 class="RBT ${crowncls}"><i class="fa-solid fa-hotel"></i> ${bookingarray['con_prefix'] ? bookingarray['con_prefix'] : ''} ${bookingarray['name'] ? bookingarray['name'] : ''}</h3>
                            <img class="close" onclick="deletemodaldiv()" src="{{ asset('admin/icons/custom/close2.svg') }}" alt="Close"></div>
                            <p><i class="fa-solid fa-phone"></i> ${bookingarray['mobile_no'] ? bookingarray['mobile_no'] : ''}</p>
                            <div class="button-group mb-2" style="display:${buttonGroupDisplay};">
                                <div aria-label="Vertical button group" role="group" class="">
                                    <button style="display:none;" class="btn btn-eight ti-write btn-sm btn-dribbble" type="button"> Edit Reservation</button>
                                    <button data-toggle="modal" data-target="#changeprofilemodal" class="btn btn-eight ti-id-badge btn-sm btn-vimeo" type="button"> Change Profile</button>
                                    <div role="group" class="btn-group">
                                        <button data-toggle="dropdown" class="btn btn-eight btn-sm btn-outline-success dropdown-toggle" type="button">More Options</button>
                                        <div class="dropdown-menu .custom-dropdown">
                                        <button style="display:${billprintbtndisp}; width: 100%; margin-bottom: 8px;" data-toggle="modal" data-target="#ammendstaymodal" class="btn btn-eight btn-sm btn-outline-primary" type="button">Amend Stay</button>
                                        <button style="width: 100%; margin-bottom: 8px;" data-toggle="modal" data-target="#guestledgermodal" class="btn btn-eight btn-sm btn-outline-primary" type="button">Guest Ledger</button>
                                        <button style="width: 100%; margin-bottom: 8px;" data-toggle="modal" data-target="#guestchargemodal" class="btn btn-eight btn-sm btn-outline-primary" type="button">Ledger Item</button>
                                        <button style="display:${billprintbtndisp}; width: 100%; margin-bottom: 8px;" data-toggle="modal" data-target="#roomchangemodal" class="btn btn-eight btn-sm btn-outline-primary" type="button">Room Change</button>
                                        <button style="display:${billprintbtndisp}; width: 100%; margin-bottom: 8px;" data-toggle="modal" data-target="#advchargemodal" class="btn btn-eight btn-sm btn-outline-primary" type="button">Advance Charge</button>
                                        <button id="billprint" style="display:${billprintbtndisp}; width: 100%; margin-bottom: 8px;" data-toggle="modal" data-target="#billprintmodal" class="btn btn-eight btn-sm btn-outline-primary" type="button">Bill Print</button>
                                        <form id="cancelForm" method="POST" action="{{ route('billcancel') }}" style="width: 100%;">
                                            @csrf
                                            <input type="hidden" name="docid" value="${bookingarray['docid']}">
                                            <input type="hidden" name="sno1" value="${bookingarray['sno1']}">
                                            <input type="hidden" id="cancelreason" name="cancelreason" value="">
                                            <button style="display:${formbtndisplay}; width: 100%; margin-bottom: 8px;" id="billcancelbtn" type="button" onclick="openCancelPrompt()" class="btn btn-eight btn-sm btn-outline-primary">Bill Cancel</button>
                                            <button data-toggle="modal" data-target="#billsettlemodal" style="display:${formbtndisplay}; width: 100%; margin-bottom: 8px;" type="button" class="btn btn-eight btn-sm btn-outline-primary">Bill Settle</button>
                                        </form>
                                        </div>
                                    </div>
                                    <button id="compsettle" style="display:none;" class="btn btn-success btn-sm" type="button">Comp. Settle</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p>Folio No.:  ${bookingarray['folioNo']}</p>
                                    <p>Check-in Date: ${new Date(bookingarray['chkindate']).toLocaleDateString('en-GB').replace(/\//g,'-')}</p>
                                    <p>Room Type: ${bookingarray['roomcatname']}</p>
                                    <p>Rate Plan: ${bookingarray['planname'] ? bookingarray['planname'] : ''}</p> 
                                    <p>Plan Amount: ${bookingarray['planamt'] ? bookingarray['planamt'] : ''}</p>
                                    <p>Company: ${bookingarray['company'] ? bookingarray['companyname'] : ''}</p>
                                    <p>Booked By: ${bookingarray['BookedBy'] ? bookingarray['BookedBy'] : ''}</p>
                                    <p>Remarks: ${bookingarray['remarks'] ? bookingarray['remarks'] : ''}</p>
                                    ${isCheckout == true ? chkoutstring : ''}
                                </div>
                                <div class="col-md-6">
                                    <p>Adults <i class="fa-solid fa-user"></i> : ${bookingarray['adult']} Children <i class="fa-solid fa-child"></i> : ${bookingarray['children'] ? bookingarray['children'] : ''}</p>
                                    <p>Exp. Departure Date: ${new Date(bookingarray['depdate']).toLocaleDateString('en-GB').replace(/\//g,'-')}</p>
                                    <p>Room Number: ${bookingarray['roomno']}</p>
                                    <p>Room Rate: ${bookingarray['roomrate']}</p>
                                    <p>Bill No.: ${bookingarray['billno'] == '0' ? '' : bookingarray['billno']}</p>
                                    <p>Travel: ${bookingarray['travelname'] ? bookingarray['travelname'] : ''}</p>
                                    <p>Pick Up/Drop <i class="fa-solid fa-truck-pickup"></i>: ${bookingarray['pickupdrop'] ? bookingarray['pickupdrop'] : ''}</p>
                                    <p>Complimentry: ${bookingarray['complimentry'] == 'Y' ? 'Yes' : 'No'}</p>
                                </div>
                                <div style="position: absolute;bottom: 0;width: -webkit-fill-available;" class="modal-footer">
                                <div style="width: -webkit-fill-available;" class="card shadow-sm">
                                        <div class="card-body p-0">
                                            <table class="table amountshow table-hover mb-0">
                                                <tbody>
                                                    <tr class="hover-row">
                                                        <td class="fw-bold">Total</td>
                                                        <td class="text-end">Rs. ${totalamt.toFixed(2)}</td>
                                                    </tr>
                                                    <tr class="hover-row">
                                                        <td class="fw-bold">Paid</td>
                                                        <td class="text-end">Rs. ${paidamt.toFixed(2)}</td>
                                                    </tr>
                                                    <tr class="hover-row">
                                                        <td class="fw-bold text-danger">Balance</td>
                                                        <td class="text-end text-danger">Rs. ${balance.toFixed(2)}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>`;

                document.body.appendChild(div1);
                div1.appendChild(modal);

                if (bookingarray['complimentry'] == 'Y') {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        }
                    });
                    $.ajax({
                        type: 'POST',
                        url: '/checkamountpayable',
                        data: {
                            docid: globaldocid,
                            sno1: globalsno1
                        },
                        success: function(response) {
                            let balance = Number(response.tbl.balance);
                            localStorage.setItem('compbalance', balance);
                            if (balance <= 0 || balance == null) {
                                pushNotify('info', 'Room Status', 'Complimentry Room', 'fade', 300, '', '', true, true, true, 15000, 20, 20, 'outline', 'right top');
                                $('#compsettle').css('display', 'block');
                                $('#billprint').css('display', 'none');
                                $('#billcancelbtn').css('display', 'none');
                            }
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                            Swal.fire('Error', 'Something went wrong while checking bill details!', 'error');
                        }
                    });
                }
            }
        }

        function openbookngreservemodal(bookingarray) {
            if (document.querySelector('.modal-packed')) {
                return;
            }

            let rglobalguestcode = bookingarray['guestcode'];
            let rfolioNo = bookingarray['BookNo'];
            rglobaldocid = bookingarray['BookingDocid'];
            rglobalsno1 = bookingarray['Sno'];
            let rglobalroomno = bookingarray['RoomNo'];
            let rglobalarrdate = bookingarray['ArrDate'];
            globalncurdate = $('#fixncur').val();

            let dispbtn = '';
            let oldchk = 'green';

            if (rglobalarrdate < globalncurdate) oldchk = 'red';

            if (rglobalarrdate <= globalncurdate) {
                dispbtn = `<button class="checkinbtnrocc" data-docid="${rglobaldocid}" data-sno="${rglobalsno1}" type="button" style="padding: 5px 8px; background-color: ${oldchk}; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                        <i class="fa fa-check-circle" style="margin-right: 8px;"></i> Check In
                   </button>`;
            }

            let prefix = bookingarray['con_prefix'] ?? '';
            let rglobalname = prefix + ' ' + bookingarray['GuestName'];

            let adthead = '';
            let adtr = '';
            let tfoot = '';
            let totalamt = 0.00;

            if (bookingarray['advance'].length > 0) {
                adthead = `<tr>
                        <th>Paytype</th>
                        <th>On Date</th>
                        <th>Amount</th>
                   </tr>`;

                bookingarray['advance'].forEach((data) => {
                    totalamt += parseFloat(data.amtcr);
                    adtr += `<tr class="hover-row">
                        <td>${data.paytype}</td>
                        <td>${dmy(data.vdate)} ${data.vtime}</td>
                        <td class="text-end">Rs. ${data.amtcr}</td>
                     </tr>`;
                });

                tfoot = `<tr>
                    <td></td><td></td>
                    <td class="text-end">Rs. ${totalamt.toFixed(2)}</td>
                 </tr>`;
            }

            $.ajax({
                url: "/fetch-room-inclusive/" + rglobaldocid,
                type: "GET",
                success: function(response) {

                    let exthead = '';
                    let extr = '';
                    let extfoot = '';

                    if (response.length > 0) {
                        exthead = `<tr>
                                <th>Name</th>
                                <th>Post</th>
                                <th>Amount</th>
                           </tr>`;

                        let totalextamt = 0.00;

                        response.forEach((item) => {
                            totalextamt += parseFloat(item.amount);
                            extr += `<tr class="hover-row">
                                <td>${item.name}</td>
                                <td>${item.chargepost}</td>
                                <td class="text-end">Rs. ${item.amount}</td>
                             </tr>`;
                        });

                        extfoot = `<tr>
                                <td></td><td></td>
                                <td class="text-end">Rs. ${totalextamt.toFixed(2)}</td>
                           </tr>`;
                    }

                    const div1 = document.createElement('div');
                    div1.classList.add('modal-packed');

                    const modal = document.createElement('div');
                    modal.classList.add('modal-custom');

                    modal.innerHTML = `
                <div style="min-height:-webkit-fill-available;" class="modal-content content-packed">
                    <div style="display:contents;">
                        <h3 class="RBT"><i class="fa-solid fa-hotel"></i> ${rglobalname}</h3>
                        <img class="close" onclick="deletemodaldiv()" src="{{ asset('admin/icons/custom/close2.svg') }}">
                    </div>

                    <p><i class="fa-solid fa-phone"></i> ${bookingarray['mobile_no'] ?? ''}</p>

                    <div class="button-group mb-2">
                        ${dispbtn}
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p>Folio No.: ${bookingarray['BookNo']}</p>
                            <p>Check-in Date: ${new Date(bookingarray['ArrDate']).toLocaleDateString('en-GB').replace(/\//g,'-')}</p>
                            <p>Room Type: ${bookingarray['roomcatname']}</p>
                            <p>Rate Plan: ${bookingarray['planname'] ?? ''}</p>
                            <p>Plan Amount: ${bookingarray['plannetamt'] ?? ''}</p>
                            <p>Company: ${bookingarray['company'] ? bookingarray['companyname'] : ''}</p>
                            <p>Remarks: ${bookingarray['Remarks'] ?? ''}</p>
                            <p>Bill To: ${bookingarray['bill_to'] ?? ''}</p>
                        </div>

                        <div class="col-md-6">
                            <p>Adults <i class="fa-solid fa-user"></i>: ${bookingarray['Adults']}
                               Children <i class="fa-solid fa-child"></i>: ${bookingarray['Childs'] ?? ''}</p>
                            <p>Exp. Departure Date: ${new Date(bookingarray['DepDate']).toLocaleDateString('en-GB').replace(/\//g,'-')}</p>
                            <p>Room Number: ${bookingarray['RoomNo']}</p>
                            <p>Room Rate: ${bookingarray['Tarrif']}</p>
                            <p>Travel: ${bookingarray['travelname'] ?? ''}</p>
                            <p>Booked By: ${bookingarray['BookedBy'] ?? ''}</p>
                            <p>Pick Up/Drop: ${bookingarray['pickupdrop'] ?? ''}</p>
                            <p>Status: ${bookingarray['ResStatus'] ?? ''}</p>
                        </div>
                    </div>

                    <div class="mt-3">
                        ${exthead ? `<div style="font-size:14px; padding:4px 6px;">Room Inclusive</div>` : ''}

                        <table class="table amountshow table-hover mb-2">
                            <thead>${exthead}</thead>
                            <tbody>${extr}</tbody>
                            
                        </table>

                        ${adthead ? `<div style="font-size:14px; padding:4px 6px;">Advances</div>` : ''}

                        <table class="table amountshow table-hover mb-0">
                            <thead>${adthead}</thead>
                            <tbody>${adtr}</tbody>
                           
                        </table>
                    </div>

                    <div class="modal-footer p-0">
                    </div>

                </div>
            `;

                    document.body.appendChild(div1);
                    div1.appendChild(modal);
                }
            });
        }


        $(document).on('click', '#compsettle', function() {
            let compbalance = Number(localStorage.getItem('compbalance'));

            if (compbalance < 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Complimentry Settlement',
                    text: `Balance is ${compbalance}. First Post Refund and Retention! To Complimentry Settlement`,
                });
                return;
            }

            const postdata = {
                'docid': globaldocid,
                'sno1': globalsno1,
                'sno': globalsno
            };

            const options = {
                method: 'POST',
                headers: {
                    'content-type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify(postdata)
            };
            fetch('/nillsettle', options)
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Settlement',
                            text: data.message,
                        });
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                })
                .catch(error => {
                    console.log(error);
                })
        });

        $(document).on('click', '.checkinbtnrocc', function() {
            let docid = $(this).data('docid');
            let sno = $(this).data('sno');
            sendroute(docid, sno);
        });

        $(document).on('click', '#billprint', function() {
            $('#myloader').removeClass('none');
            $('#myloader').find('img').attr('src', 'admin/icons/custom/typewriter.gif');
            $('#myloader').find('.loader-text').html('Getting Bill Details');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            });

            $.ajax({
                type: 'POST',
                url: '/checkbillalreadyprinted',
                data: {
                    docid: globaldocid,
                    sno1: globalsno1
                },
                success: function(response) {
                    if (response.exists == true) {
                        Swal.fire({
                            title: "Bill Print",
                            text: `Bill Already Printed For Room No : ${globalroomno}. Refreshing Page.`,
                            icon: "info",
                            timer: 3000
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        setTimeout(() => {
                            $('#myloader').addClass('none');
                            let checkpostxhr = new XMLHttpRequest();
                            checkpostxhr.open('POST', '/checkchargecount', true);
                            checkpostxhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            checkpostxhr.onreadystatechange = function() {
                                if (checkpostxhr.readyState === 4 && checkpostxhr.status === 200) {
                                    let resultsd = JSON.parse(checkpostxhr.responseText);
                                    let results = resultsd.chargecount;
                                    let allrooms = resultsd.allrooms || [];
                                    let leaderyn = resultsd.leaderyn;
                                    if (results === 0) {
                                        Swal.fire({
                                            title: "Bill Print",
                                            text: `Charge Posting For Room No : ${globalroomno}, for date ${dmy(resultsd.checkdate)} ?`,
                                            icon: "error",
                                            showCancelButton: true,
                                            confirmButtonColor: "#1EE01E",
                                            cancelButtonColor: "#d33",
                                            confirmButtonText: "Yes"
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                $('#billprintmodal').modal('hide');
                                                let startposting = new XMLHttpRequest();
                                                startposting.open('POST', '/postchargesone', true);
                                                startposting.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                                startposting.onreadystatechange = function() {
                                                    if (startposting.status === 200 && startposting.readyState === 4) {
                                                        let results = JSON.parse(startposting.responseText);
                                                        $('#billprintmodal').modal('hide');
                                                        if (results.success == 'Charge Posted') {
                                                            Swal.fire({
                                                                title: "Charge Posting",
                                                                text: `Charges have been posted for Room No. : ${globalroomno}. Reopen Bill Print.`,
                                                                icon: "success",
                                                                timer: 3000
                                                            }).then(() => {
                                                                if (results.backposting == 1 && allrooms && Array.isArray(allrooms)) {
                                                                    showAlertForRooms(allrooms, 0, results.backposting);
                                                                } else {
                                                                    continueAfterRoomPost([{
                                                                        docid: globaldocid,
                                                                        sno1: globalsno1,
                                                                        roomno: globalroomno
                                                                    }], 0, results.backposting);
                                                                }
                                                            });
                                                        } else if (results.error == 'Unable To Post Charge') {
                                                            Swal.fire({
                                                                title: "Charge Posting",
                                                                text: `Unable to Post Charge`,
                                                                icon: "error",
                                                                timer: 3000
                                                            }).then(() => {
                                                                if (allrooms && Array.isArray(allrooms)) {
                                                                    showAlertForRooms(allrooms, 0);
                                                                }
                                                            });
                                                        } else {
                                                            Swal.fire({
                                                                title: "Charge Posting",
                                                                text: `Unknown Error Occurred`,
                                                                icon: "error",
                                                                timer: 3000
                                                            }).then(() => {
                                                                if (allrooms && Array.isArray(allrooms)) {
                                                                    showAlertForRooms(allrooms, 0);
                                                                }
                                                            });
                                                        }
                                                    }
                                                };
                                                startposting.send(`docid=${globaldocid}&chargedate=${resultsd.checkdate}&roomno=${globalroomno}&sno1=${globalsno1}&_token={{ csrf_token() }}`);
                                            } else if (result.isDismissed) {
                                                if (allrooms && Array.isArray(allrooms)) {
                                                    showAlertForRooms(allrooms, 0);
                                                }
                                            }
                                        });
                                    } else {
                                        if (allrooms && Array.isArray(allrooms)) {
                                            showAlertForRooms(allrooms, 0);
                                        }
                                    }
                                }
                            };
                            checkpostxhr.send(`docid=${globaldocid}&sno1=${globalsno1}&_token={{ csrf_token() }}`);
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    Swal.fire('Error', 'Something went wrong while fetching bill details!', 'error');
                    $('#myloader').addClass('none');
                }
            });
        });

        function continueAfterRoomPost(rooms, index, backposting) {
            backposting = Number(backposting) || 0;

            if (backposting == 1) {
                showAlertForRooms(rooms, index, backposting);
                return;
            }

            const postedRoom = rooms[index] || {};

            if (postedRoom.docid == globaldocid && postedRoom.sno1 == globalsno1) {
                let checkpostxhr = new XMLHttpRequest();
                checkpostxhr.open('POST', '/checkchargecount', true);
                checkpostxhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                checkpostxhr.onreadystatechange = function() {
                    if (checkpostxhr.readyState === 4 && checkpostxhr.status === 200) {
                        let resultsd = JSON.parse(checkpostxhr.responseText);
                        let allrooms = resultsd.allrooms || [];

                        if (allrooms && Array.isArray(allrooms) && allrooms.length > 0) {
                            showAlertForRooms(allrooms, 0, resultsd.backposting);
                        } else {
                            showAlertForRooms([], 0, 0);
                        }
                    }
                };
                checkpostxhr.send(`docid=${globaldocid}&sno1=${globalsno1}&_token={{ csrf_token() }}`);
                return;
            }

            showAlertForRooms(rooms, index + 1, backposting);
        }

        function showAlertForRooms(rooms, index, backposting) {
            backposting = Number(backposting) || 0;

            console.log('Rooms:', rooms);
            console.log('Index:', index);
            console.log('Backposting:', backposting);

            // Handle undefined or null rooms
            if (!rooms || !Array.isArray(rooms)) {
                console.warn('Rooms data is undefined or not an array');
                return;
            }

            if (backposting == 0) {
                if (rooms.length === 0) {
                    console.log('No more rooms to process, opening bill print modal');
                    let billprintiframe = document.getElementById("billprintiframe");
                    billprintiframe.src = "{{ url('/billprint') }}" + "?docid=" + globaldocid + "&sno1=" + globalsno1 + "&sno=" + globalsno + "&origin=roomstatus";
                    $('#billprintmodal').modal('show');
                    return;
                }

                if (index >= rooms.length) return;
            } else {
                let billprintiframe = document.getElementById("billprintiframe");
                billprintiframe.src = "{{ url('/billprint') }}" + "?docid=" + globaldocid + "&sno1=" + globalsno1 + "&sno=" + globalsno + "&origin=roomstatus";
                $('#billprintmodal').modal('show');
            }

            if (typeof rooms[index] != 'undefined' || backposting == 1) {
                let checkpostxhr = new XMLHttpRequest();
                checkpostxhr.open('POST', '/checkchargecount', true);
                checkpostxhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                checkpostxhr.onreadystatechange = function() {
                    if (checkpostxhr.readyState === 4 && checkpostxhr.status === 200) {
                        let resultsd = JSON.parse(checkpostxhr.responseText);
                        let results = resultsd.chargecount;
                        let allrooms = resultsd.allrooms || [];
                        if (results === 0) {
                            Swal.fire({
                                title: "Bill Print",
                                text: `Charge Posting For Room No : ${rooms[index].roomno}, for date ${dmy(resultsd.checkdate)} ?`,
                                icon: "error",
                                showCancelButton: true,
                                confirmButtonColor: "#1EE01E",
                                cancelButtonColor: "#d33",
                                confirmButtonText: "Yes"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#billprintmodal').modal('hide');
                                    let startposting = new XMLHttpRequest();
                                    startposting.open('POST', '/postchargesone', true);
                                    startposting.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                    startposting.onreadystatechange = function() {
                                        if (startposting.status === 200 && startposting.readyState === 4) {
                                            let results = JSON.parse(startposting.responseText);
                                            $('#billprintmodal').modal('hide');
                                            if (results.success == 'Charge Posted') {
                                                Swal.fire({
                                                    title: "Charge Posting",
                                                    text: `Charges have been posted for Room No. : ${rooms[index].roomno}. Reopen Bill Print.`,
                                                    icon: "success",
                                                    timer: 3000
                                                }).then(() => {
                                                    continueAfterRoomPost(rooms, index, results.backposting);
                                                });
                                            } else if (results.error == 'Unable To Post Charge') {
                                                Swal.fire({
                                                    title: "Charge Posting",
                                                    text: `Unable to Post Charge`,
                                                    icon: "error",
                                                    timer: 3000
                                                }).then(() => {
                                                    continueAfterRoomPost(rooms, index, results.backposting);
                                                });
                                            } else {
                                                Swal.fire({
                                                    title: "Charge Posting",
                                                    text: `Unknown Error Occurred`,
                                                    icon: "error",
                                                    timer: 3000
                                                }).then(() => {
                                                    continueAfterRoomPost(rooms, index, results.backposting);
                                                });
                                            }
                                        }
                                    };
                                    startposting.send(`docid=${rooms[index].docid ?? ''}&chargedate=${resultsd.checkdate}&roomno=${rooms[index].roomno}&sno1=${rooms[index].sno1}&_token={{ csrf_token() }}`);
                                } else if (result.isDismissed) {
                                    showAlertForRooms(rooms, index + 1, results.backposting);
                                }
                            });
                        } else {
                            showAlertForRooms(rooms, index + 1, resultsd.backposting);
                        }
                    }
                }
                checkpostxhr.send(`docid=${rooms[index].docid}&sno1=${rooms[index].sno1}&_token={{ csrf_token() }}`);
            }

        }

        function openCancelPrompt() {
            // var reason = prompt("Why do you want to cancel this bill?");
            Swal.fire({
                icon: 'question',
                title: 'Bill',
                text: 'Why do you want to cancel this bill?',
                input: 'text',
                inputPlaceholder: 'Reason',
                inputValue: 'Wrong Bill Entry',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Reason is required';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("cancelreason").value = result.value;
                    document.getElementById("cancelForm").submit();
                }
            });
        }

        function deletemodaldiv() {
            const modal = document.querySelector('.modal-custom');
            const modalpacked = document.querySelector('.modal-packed');
            modal.remove();
            modalpacked.remove();
        }

        $(document).keydown(function(event) {
            if (event.keyCode === 27) {
                deletemodaldiv();
            }
        });

        $('#changeprofilemodal').on('show.bs.modal', function(event) {
            var iframe = document.getElementById("changeprofileframe");
            let profilechangespan = document.getElementById('profilechangespan');
            let profilechangecode = document.getElementById('profilechangecode');
            profilechangespan.textContent = globalname;
            profilechangecode.textContent = folioNo;
            iframe.src = "{{ url('/changeprofile') }}" + "?docid=" + globaldocid + "&sno1=" + globalsno1;
        });

        $('#ammendstaymodal').on('show.bs.modal', function(event) {
            var iframe = document.getElementById("ammenstayiframe");
            let profilechangespan = document.getElementById('ammendstayspan');
            let guestcode1 = document.getElementById('guestcode1');
            profilechangespan.textContent = globalname;
            guestcode1.textContent = folioNo;
            iframe.src = "{{ url('/ammendstay') }}" + "?docid=" + globaldocid + "&sno1=" + globalsno1 + "&sno=" + globalsno;
        });

        $('#guestledgermodal').on('show.bs.modal', function(event) {
            var iframe = document.getElementById("guestledgeriframe");
            let guestledgerspan = document.getElementById('guestledgerspan');
            let guestcode1 = document.getElementById('guestcode2');
            guestledgerspan.textContent = globalname;
            guestcode1.textContent = folioNo;
            iframe.src = "{{ url('/guestledger') }}" + "?docid=" + globaldocid + "&sno1=" + globalsno1;
        });

        $('#guestchargemodal').on('show.bs.modal', function(event) {
            var iframe = document.getElementById("guestchargeiframe");
            let guestchargespan = document.getElementById('guestchargespan');
            let guestcode1 = document.getElementById('guestcode7');
            guestchargespan.textContent = globalname;
            guestcode1.textContent = folioNo;
            iframe.src = "{{ url('/guestcharge') }}" + "?docid=" + globaldocid + "&sno1=" + globalsno1;
        });

        $('#roomchangemodal').on('show.bs.modal', function(event) {
            var iframe = document.getElementById("roomchangeiframe");
            let profilechangespan = document.getElementById('roomchangespan');
            profilechangespan.textContent = globalname;
            let guestcode3 = document.getElementById('guestcode3');
            guestcode3.textContent = folioNo;
            iframe.src = "{{ url('/roomchange') }}" + "?docid=" + globaldocid + "&sno1=" + globalsno1 + "&sno=" + globalsno;
        });

        $('#advchargemodal').on('show.bs.modal', function(event) {
            var iframe = document.getElementById("advchargeiframe");
            let profilechangespan = document.getElementById('advchargespan');
            profilechangespan.textContent = globalname;
            let guestcode4 = document.getElementById('guestcode4');
            guestcode4.textContent = folioNo;
            iframe.src = "{{ url('/advcharge') }}" + "?docid=" + globaldocid + "&sno1=" + globalsno1 + "&sno=" + globalsno;
        });

        $('#billprintmodal').on('show.bs.modal', function(event) {
            var iframe = document.getElementById("billprintiframe");
            let billprintspan = document.getElementById('billprintspan');
            let guestcode5 = document.getElementById('guestcode5');
            billprintspan.textContent = globalname;
            guestcode5.textContent = folioNo;
            iframe.src = "{{ url('/billprint') }}" + "?docid=" + globaldocid + "&sno1=" + globalsno1 + "&sno=" + globalsno + "&origin=roomstatus";
        });

        $('#billsettlemodal').on('show.bs.modal', function(event) {
            var iframe = document.getElementById("billsettleiframe");
            let profilechangespan = document.getElementById('billsettlespan');
            profilechangespan.textContent = globalname;
            let guestcode6 = document.getElementById('guestcode6');
            guestcode6.textContent = folioNo;
            iframe.src = "{{ url('/billsettle') }}" + "?docid=" + globaldocid + "&sno1=" + globalsno1 + "&sno=" + globalsno;
        });

        function createTodaysArrivalsModal() {
            if ($('#todaysArrivalsModal').length) {
                return $('#todaysArrivalsModal');
            }

            const modalMarkup = `
                    <div class="modal fade" id="todaysArrivalsModal" tabindex="-1" aria-labelledby="todaysArrivalsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" style="max-width: 96vw;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h5 class="modal-title font-weight-bold" id="todaysArrivalsModalLabel">Today's Arrivals</h5>
                                        <small class="text-muted" id="todaysArrivalsModalSubtext"></small>
                                    </div>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body pt-2" style="max-height: 80vh; overflow-y: auto;">
                                    <div class="table-responsive" style="max-height: 72vh; overflow: auto;">
                                        <table class="table table-hover table-bordered mb-0" id="todaysArrivalsTable">
                                            <thead>
                                                <tr>
                                                    <th>Booking No</th>
                                                    <th>Room No</th>
                                                    <th>Guest Name</th>
                                                    <th>Room Type</th>
                                                    <th>Mobile No</th>
                                                    <th>Arrival</th>
                                                    <th>Departure</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;

            $('body').append(modalMarkup);
            return $('#todaysArrivalsModal');
        }

        function renderTodaysArrivals(arrivals) {
            const modal = createTodaysArrivalsModal();
            const modalSubtext = modal.find('#todaysArrivalsModalSubtext');
            const tableBody = modal.find('#todaysArrivalsTable tbody');
            const bookingColors = ['#fff7d6', '#e8f6ff', '#eafbe7', '#fdecef', '#f3edff'];
            const bookingColorMap = {};
            let colorIndex = 0;

            const rowsHtml = arrivals.map(function(arrival) {
                const bookingDocid = arrival.BookingDocid || '';

                if (!bookingColorMap[bookingDocid]) {
                    bookingColorMap[bookingDocid] = bookingColors[colorIndex % bookingColors.length];
                    colorIndex += 1;
                }

                const bookingNumber = String(bookingDocid).replace(/\s+/g, ' ').trim();

                return `
                        <tr class="arrival-row cursor-pointer"
                            data-docid="${escapeHtml(arrival.BookingDocid)}"
                            data-sno="${escapeHtml(arrival.Sno)}"
                            style="background-color: ${bookingColorMap[bookingDocid]};"
                            title="Open prefilled walk-in">
                            <td class="font-weight-bold">${escapeHtml(arrival.BookNo || '-')}</td>
                            <td class="font-weight-bold">${escapeHtml(arrival.RoomNo || '-')}</td>
                            <td>${escapeHtml(arrival.guestname || '-')}</td>
                            <td>${escapeHtml(arrival.roomcatname || arrival.RoomCat || '-')}</td>
                            <td>${escapeHtml(arrival.mobile_no || '-')}</td>
                            <td>${escapeHtml(formatArrivalDate(arrival.ArrDate))} ${escapeHtml(arrival.ArrTime || '')}</td>
                            <td>${escapeHtml(formatArrivalDate(arrival.DepDate))} ${escapeHtml(arrival.DepTime || '')}</td>
                            <td>${escapeHtml(arrival.ResStatus || '-')}</td>
                        </tr>`;
            }).join('');

            modalSubtext.text(arrivals.length + ' arrival' + (arrivals.length > 1 ? 's' : '') + ' found');
            tableBody.html(rowsHtml);
            modal.modal('show');
        }

        $(document).on('click', '.avail-cell', function() {
            let date = $(this).data('date');

            $.ajax({
                url: 'todaysarrivalsbydate',
                type: 'POST',
                data: {
                    date: date
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(data) {
                    const arrivals = Array.isArray(data) ? data : [];

                    if (!arrivals.length) {
                        pushNotify('info', 'Room Status', 'No Arrivals Today', 'fade', 300, '', '', true, true, true, 2000, 20, 20, 'outline', 'left top');

                        return;
                    }

                    renderTodaysArrivals(arrivals);
                },
                error: function(xhr, status, error) {
                    swal("Unable to fetch today's arrivals");
                    console.error("Error fetching today's arrivals:", error);
                }
            });

        });

        $(document).on('click', '.cat-avail-cell', function() {
            let date = $(this).data('date');
            let cat = $(this).data('cat');

            $.ajax({
                url: 'todaysarrivalsbydate',
                type: 'POST',
                data: {
                    date: date,
                    roomcat: cat
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(data) {
                    const arrivals = Array.isArray(data) ? data : [];

                    if (!arrivals.length) {
                        pushNotify('info', 'Room Status', 'No Arrivals Today', 'fade', 300, '', '', true, true, true, 2000, 20, 20, 'outline', 'left top');

                        return;
                    }

                    renderTodaysArrivals(arrivals);
                },
                error: function(xhr, status, error) {
                    swal("Unable to fetch today's arrivals");
                    console.error("Error fetching today's arrivals:", error);
                }
            });

        });

        function sendroute(docid, sno) {
            Swal.fire({
                title: 'Room Status',
                text: "Mentioned thing(s) are compulsory in order to proceed to check in. Do you want to add this information?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'No, cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = 'prefilledwalkin?docid=' + encodeURIComponent(docid) + '&sno=' + encodeURIComponent(sno);
                    window.location.href = url;
                }
            });
        }

        function deletemodaldiv2() {
            const modalpacked = document.querySelector('.modal-packed');
            modalpacked.remove();
        }
    </script>
@endsection
