@extends('property.layouts.main')
@section('main-container')
@php
    use Carbon\Carbon;
    $currentHour = Carbon::now()->format('H');
    if ($currentHour >= 5 && $currentHour < 12) { $greeting = 'Good Morning'; $greetEmoji = '🌅'; }
    elseif ($currentHour >= 12 && $currentHour < 17) { $greeting = 'Good Afternoon'; $greetEmoji = '☀️'; }
    elseif ($currentHour >= 17 && $currentHour < 21) { $greeting = 'Good Evening'; $greetEmoji = '🌆'; }
    else { $greeting = 'Good Night'; $greetEmoji = '🌙'; }

    $softwareDate = Carbon::parse($datearr['ncurdate'])->format('l, d F Y');
    $userName = Auth::user()->name;
    $compName = $user->comp_name ?? 'Hotel';

    // ── Room status counts ──────────────────────────────────────────────
    $occupiedCount        = $status['Occupied'] ? count($status['Occupied']) : 0;
    $expectedCheckout     = $status['ExpectedCheckOut'] ? count($status['ExpectedCheckOut']) : 0;
    $occupiedDirtyCount   = $status['OccupiedDirtyRooms'] ? count($status['OccupiedDirtyRooms']) : 0;
    $vacantDirtyCount     = $status['VacantDirtyRooms'] ? count($status['VacantDirtyRooms']) : 0;
    $expectedArrivalCount = 0;
    if ($status['ExpectedArrival']) {
        $expectedArrivalCount = $status['ExpectedArrival']->filter(fn($i) => $i->total_rooms > 0)->sum('total_rooms');
    }
    $availableClean = max(0, $totalRooms - $occupiedCount - $expectedCheckout - $occupiedDirtyCount - $vacantDirtyCount - $expectedArrivalCount);

    $today = $metrics['todaySummary'];
    $weekly = $metrics['weekly'];

    // Donut segments: [label, count, color]
    $donutData = [
        ['Occupied Room',        $occupiedCount,        '#ef4444'],
        ['Expected CheckOut',    $expectedCheckout,     '#8b5cf6'],
        ['Occupied Dirty Room',  $occupiedDirtyCount,   '#f97316'],
        ['Vacant Dirty Room',    $vacantDirtyCount,     '#f59e0b'],
        ['Expected Arrival',     $expectedArrivalCount, '#2dd4bf'],
        ['Available Clean Room', $availableClean,       '#93c5fd'],
    ];
@endphp

<style>
    /* ============ Analytics Dashboard (screenshot design) ============ */
    .adx { padding: 4px 8px 24px; font-family: var(--hms-font, "Roboto", system-ui, sans-serif); }
    .adx .card { background:#fff; border:1px solid #eef0f4; border-radius:14px; box-shadow:0 1px 3px rgba(16,24,40,.05); }

    /* Greeting banner */
    .adx-banner { background: linear-gradient(100deg,#5b5fe9 0%,#6d5ce7 45%,#7c3aed 100%); border-radius:16px; color:#fff; padding:26px 30px; display:flex; justify-content:space-between; align-items:center; gap:24px; margin-bottom:16px; }
    .adx-banner .bx-icon { font-size:44px; line-height:1; margin-right:18px; }
    .adx-banner h2 { font-size:26px; font-weight:800; margin:0 0 4px; letter-spacing:.2px; }
    .adx-banner .bx-sub { font-size:14px; opacity:.85; margin:0; }
    .adx-banner .bx-date { display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.25); border-radius:10px; padding:7px 14px; font-size:13px; margin-top:14px; }
    .adx-right { display:flex; align-items:center; gap:26px; }
    .adx-weather { text-align:right; }
    .adx-weather .wx-temp { font-size:34px; font-weight:800; line-height:1.1; }
    .adx-weather .wx-cond { font-size:13px; opacity:.9; }
    .adx-weather .wx-city { font-size:12px; opacity:.75; }
    .adx-clock { position:relative; width:86px; height:86px; border-radius:50%; background:radial-gradient(circle at 35% 30%, #ffffff, #e8eaf6); box-shadow:0 4px 14px rgba(0,0,0,.25), inset 0 0 0 4px #f5f6fb; flex:0 0 auto; }
    .adx-clock .hand { position:absolute; left:50%; bottom:50%; transform-origin:50% 100%; border-radius:3px; }
    .adx-clock .h-hr { width:4px; height:22px; margin-left:-2px; background:#1e293b; }
    .adx-clock .h-min { width:3px; height:30px; margin-left:-1.5px; background:#475569; }
    .adx-clock .h-sec { width:1.5px; height:34px; margin-left:-.75px; background:#ef4444; }
    .adx-clock .h-pin { position:absolute; top:50%; left:50%; width:7px; height:7px; margin:-3.5px; border-radius:50%; background:#1e293b; }
    .adx-livetime { font-size:11px; letter-spacing:1.5px; opacity:.85; text-align:center; margin-top:6px; font-weight:600; }

    /* Alert strip */
    .adx-alert { display:flex; align-items:center; justify-content:center; gap:10px; background:#fff7ed; border:1px solid #fed7aa; color:#c2410c; font-size:13.5px; font-weight:600; border-radius:10px; padding:10px 18px; margin-bottom:18px; }

    /* Stat cards */
    .adx-stats { display:grid; grid-template-columns:repeat(6,1fr); gap:14px; margin-bottom:18px; }
    .adx-stat { position:relative; overflow:hidden; padding:16px 16px 12px; }
    .adx-stat .st-head { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
    .adx-stat .st-ic { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; flex:0 0 auto; }
    .adx-stat .st-label { font-size:12px; font-weight:600; color:#475569; line-height:1.25; }
    .adx-stat .st-count { font-size:30px; font-weight:800; margin:2px 0 8px; }
    .adx-stat .st-link { font-size:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
    .adx-stat .st-link:hover { text-decoration:underline; }
    .adx-stat .st-wave { position:absolute; right:-8px; bottom:-8px; width:70px; height:34px; opacity:.5; border-radius:50% 50% 0 0; }
    .st-red    .st-ic { background:#fee2e2; color:#dc2626; } .st-red    .st-count, .st-red .st-link { color:#dc2626; } .st-red .st-wave { background:#fee2e2; }
    .st-purple .st-ic { background:#ede9fe; color:#7c3aed; } .st-purple .st-count, .st-purple .st-link { color:#7c3aed; } .st-purple .st-wave { background:#ede9fe; }
    .st-orange .st-ic { background:#ffedd5; color:#ea580c; } .st-orange .st-count, .st-orange .st-link { color:#ea580c; } .st-orange .st-wave { background:#ffedd5; }
    .st-yellow .st-ic { background:#fef9c3; color:#ca8a04; } .st-yellow .st-count, .st-yellow .st-link { color:#ca8a04; } .st-yellow .st-wave { background:#fef9c3; }
    .st-teal   .st-ic { background:#ccfbf1; color:#0d9488; } .st-teal   .st-count, .st-teal .st-link { color:#0d9488; } .st-teal .st-wave { background:#ccfbf1; }
    .st-blue   .st-ic { background:#dbeafe; color:#2563eb; } .st-blue   .st-count, .st-blue .st-link { color:#2563eb; } .st-blue .st-wave { background:#dbeafe; }

    /* Middle row */
    .adx-mid { display:grid; grid-template-columns:1.15fr 1fr; gap:16px; margin-bottom:18px; }
    .adx-card { padding:20px 22px; }
    .adx-card .card-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
    .adx-card .card-head h5 { font-size:16px; font-weight:700; color:#0f172a; margin:0; }
    .adx-card .card-head a, .adx-card .card-head .btn-link { font-size:12.5px; font-weight:600; color:#6d5ce7; text-decoration:none; }
    .adx-card .card-head a:hover { text-decoration:underline; }

    /* Donut */
    .adx-donut-wrap { display:flex; align-items:center; gap:28px; }
    .adx-donut-box { position:relative; width:190px; height:190px; flex:0 0 auto; }
    .adx-donut-center { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; pointer-events:none; }
    .adx-donut-center .dc-label { font-size:12px; color:#64748b; }
    .adx-donut-center .dc-total { font-size:26px; font-weight:800; color:#0f172a; }
    .adx-legend { flex:1; }
    .adx-legend .lg-row { display:flex; align-items:center; gap:10px; padding:7px 0; font-size:13.5px; color:#334155; border-bottom:1px dashed #f1f5f9; }
    .adx-legend .lg-row:last-child { border-bottom:0; }
    .adx-legend .lg-dot { width:10px; height:10px; border-radius:50%; flex:0 0 auto; }
    .adx-legend .lg-name { flex:1; }
    .adx-legend .lg-count { font-weight:700; color:#0f172a; }
    .adx-legend .lg-pct { color:#94a3b8; font-size:12.5px; min-width:64px; text-align:right; }

    /* Today summary tiles */
    .adx-sumgrid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
    .adx-sumtile { display:flex; align-items:center; gap:12px; border:1px solid #eef0f4; border-radius:12px; padding:14px; background:#fbfcfe; }
    .adx-sumtile .su-ic { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex:0 0 auto; }
    .adx-sumtile .su-label { font-size:12px; color:#64748b; font-weight:600; }
    .adx-sumtile .su-value { font-size:20px; font-weight:800; color:#0f172a; line-height:1.15; }
    .su-green  .su-ic { background:#dcfce7; color:#16a34a; } .su-green  .su-value { color:#16a34a; }
    .su-red    .su-ic { background:#fee2e2; color:#dc2626; } .su-red    .su-value { color:#dc2626; }
    .su-blue   .su-ic { background:#dbeafe; color:#2563eb; } .su-blue   .su-value { color:#2563eb; }
    .su-purple .su-ic { background:#ede9fe; color:#7c3aed; } .su-purple .su-value { color:#7c3aed; }
    .su-orange .su-ic { background:#ffedd5; color:#ea580c; } .su-orange .su-value { color:#ea580c; }
    .su-teal   .su-ic { background:#ccfbf1; color:#0d9488; } .su-teal   .su-value { color:#0d9488; }

    /* Charts row */
    .adx-charts { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
    .adx-charts .adx-card { min-width:0; }
    .adx-range { font-size:12.5px; font-weight:600; color:#475569; border:1px solid #e2e8f0; border-radius:8px; padding:5px 10px; background:#fff; }
    .chart-box { position:relative; height:230px; }

    /* Footer */
    .adx-footer { text-align:center; font-size:12.5px; color:#94a3b8; margin-top:22px; }

    @media (max-width:1400px){ .adx-stats { grid-template-columns:repeat(3,1fr); } .adx-charts { grid-template-columns:1fr; } }
    @media (max-width:992px){ .adx-mid { grid-template-columns:1fr; } .adx-banner { flex-direction:column; align-items:flex-start; } .adx-right { align-self:center; } }
    @media (max-width:640px){ .adx-stats { grid-template-columns:1fr 1fr; } .adx-sumgrid { grid-template-columns:1fr; } .adx-donut-wrap { flex-direction:column; } }
</style>

<div class="adx">

    {{-- ═══════════ Greeting banner ═══════════ --}}
    <div class="adx-banner">
        <div class="d-flex align-items-center">
            <span class="bx-icon" id="adxGreetIcon">{{ $greetEmoji }}</span>
            <div>
                <h2>{{ $greeting }}, {{ $userName }}! 👋</h2>
                <p class="bx-sub">Have a productive day ahead.</p>
                <span class="bx-date">📅 {{ $softwareDate }}</span>
            </div>
        </div>
        <div class="adx-right">
            <div class="text-center">
                <div class="adx-clock" id="adxClock">
                    <div class="hand h-hr" id="adxHr"></div>
                    <div class="hand h-min" id="adxMin"></div>
                    <div class="hand h-sec" id="adxSec"></div>
                    <div class="h-pin"></div>
                </div>
                <div class="adx-livetime">LIVE TIME</div>
            </div>
            <div class="adx-weather" id="adxWeather">
                <div class="wx-temp"><span id="adxWxIcon">☁️</span> <span id="adxWxTemp">--°C</span></div>
                <div class="wx-cond" id="adxWxCond">Loading…</div>
                <div class="wx-city">📍 {{ $user->city ?? 'Kanpur' }}</div>
            </div>
        </div>
    </div>

    {{-- ═══════════ WhatsApp balance alert ═══════════ --}}
    @if (isset($whatsappBal) && $whatsappBal !== null && (float) $whatsappBal < 100)
        <div class="adx-alert">
            ⚠️ Your WhatsApp Balance (₹{{ number_format((float) $whatsappBal, 2) }}) Is Low. Please Recharge To Send Automatic Messages
        </div>
    @endif

    {{-- ═══════════ Stat cards ═══════════ --}}
    <div class="adx-stats">
        <div class="card adx-stat st-red">
            <div class="st-head"><span class="st-ic">👤</span><span class="st-label">Occupied Room</span></div>
            <div class="st-count">{{ $occupiedCount }}</div>
            <a class="st-link" href="{{ route('inhoseroomstatus') }}">View Details →</a>
            <span class="st-wave"></span>
        </div>
        <div class="card adx-stat st-purple">
            <div class="st-head"><span class="st-ic">⏳</span><span class="st-label">Expected CheckOut Room</span></div>
            <div class="st-count">{{ $expectedCheckout }}</div>
            <a class="st-link" href="{{ url('expectedcheckout') }}">View Details →</a>
            <span class="st-wave"></span>
        </div>
        <div class="card adx-stat st-orange">
            <div class="st-head"><span class="st-ic">🔔</span><span class="st-label">Occupied Dirty Room</span></div>
            <div class="st-count">{{ $occupiedDirtyCount }}</div>
            <a class="st-link" href="{{ route('roomstatusboard') }}">View Details →</a>
            <span class="st-wave"></span>
        </div>
        <div class="card adx-stat st-yellow">
            <div class="st-head"><span class="st-ic">🔔</span><span class="st-label">Vacant Dirty Room</span></div>
            <div class="st-count">{{ $vacantDirtyCount }}</div>
            <a class="st-link" href="{{ route('roomstatusboard') }}">View Details →</a>
            <span class="st-wave"></span>
        </div>
        <div class="card adx-stat st-teal">
            <div class="st-head"><span class="st-ic">✈️</span><span class="st-label">Expected Arrival Room</span></div>
            <div class="st-count">{{ $expectedArrivalCount }}</div>
            <a class="st-link" href="{{ route('todaysarrivals') }}">View Details →</a>
            <span class="st-wave"></span>
        </div>
        <div class="card adx-stat st-blue">
            <div class="st-head"><span class="st-ic">🛏️</span><span class="st-label">Total Rooms</span></div>
            <div class="st-count">{{ $totalRooms }}</div>
            <a class="st-link" href="{{ route('roomstatus') }}">View Details →</a>
            <span class="st-wave"></span>
        </div>
    </div>

    {{-- ═══════════ Middle row: donut + today's summary ═══════════ --}}
    <div class="adx-mid">
        <div class="card adx-card">
            <div class="card-head">
                <h5>Room Status Overview</h5>
                <a href="{{ route('roomstatusboard') }}">View Full Report →</a>
            </div>
            <div class="adx-donut-wrap">
                <div class="adx-donut-box">
                    <canvas id="adxDonut"></canvas>
                    <div class="adx-donut-center">
                        <span class="dc-label">Total</span>
                        <span class="dc-total">{{ $totalRooms }}</span>
                    </div>
                </div>
                <div class="adx-legend" id="adxLegend"></div>
            </div>
        </div>

        <div class="card adx-card">
            <div class="card-head"><h5>Today's Summary</h5></div>
            <div class="adx-sumgrid">
                <div class="adx-sumtile su-green">
                    <span class="su-ic">🧑‍💼</span>
                    <div><div class="su-label">Check In</div><div class="su-value">{{ $today['checkIn'] }}</div></div>
                </div>
                <div class="adx-sumtile su-red">
                    <span class="su-ic">🚪</span>
                    <div><div class="su-label">Check Out</div><div class="su-value">{{ $today['checkOut'] }}</div></div>
                </div>
                <div class="adx-sumtile su-blue">
                    <span class="su-ic">👤</span>
                    <div><div class="su-label">In House Guest</div><div class="su-value">{{ $today['inhouseGuests'] }}</div></div>
                </div>
                <div class="adx-sumtile su-purple">
                    <span class="su-ic">🧾</span>
                    <div><div class="su-label">Total Revenue</div><div class="su-value">₹{{ number_format($today['totalRevenue']) }}</div></div>
                </div>
                <div class="adx-sumtile su-orange">
                    <span class="su-ic">💰</span>
                    <div><div class="su-label">ADR</div><div class="su-value">₹{{ number_format($today['adr']) }}</div></div>
                </div>
                <div class="adx-sumtile su-teal">
                    <span class="su-ic">📈</span>
                    <div><div class="su-label">RevPAR</div><div class="su-value">₹{{ number_format($today['revpar']) }}</div></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ Trend charts ═══════════ --}}
    <div class="adx-charts">
        <div class="card adx-card">
            <div class="card-head">
                <h5>Revenue Trend</h5>
                <select class="adx-range" data-chart="revenue">
                    <option value="7">This Week</option>
                    <option value="30">This Month</option>
                </select>
            </div>
            <div class="chart-box"><canvas id="adxRevChart"></canvas></div>
        </div>
        <div class="card adx-card">
            <div class="card-head">
                <h5>Occupancy Trend</h5>
                <select class="adx-range" data-chart="occupancy">
                    <option value="7">This Week</option>
                    <option value="30">This Month</option>
                </select>
            </div>
            <div class="chart-box"><canvas id="adxOccChart"></canvas></div>
        </div>
        <div class="card adx-card">
            <div class="card-head">
                <h5>ADR &amp; RevPAR Trend</h5>
                <select class="adx-range" data-chart="adrrev">
                    <option value="7">This Week</option>
                    <option value="30">This Month</option>
                </select>
            </div>
            <div class="chart-box"><canvas id="adxAdrChart"></canvas></div>
        </div>
    </div>

    <div class="adx-footer">© {{ date('Y') }} Analysis Hotel Management System. All rights reserved.</div>
</div>

<script>
/* Chart.js itself comes from the layout footer (v4.4.5) — do NOT include
   another copy here or the footer version overwrites the global and orphans
   the chart instances. Init is deferred to window.load so the footer's
   Chart global is final before we create anything. */
window.addEventListener('load', function () {
(function () {
    'use strict';

    /* ── Data from server ─────────────────────────────────────────── */
    var WEEKLY = @json($weekly);
    var DONUT = @json($donutData);
    var TOTAL_ROOMS = {{ (int) $totalRooms }};

    /* ── Live clock (analog) ──────────────────────────────────────── */
    function tickClock() {
        var n = new Date();
        var h = n.getHours() % 12, m = n.getMinutes(), s = n.getSeconds();
        document.getElementById('adxHr').style.transform  = 'rotate(' + (h * 30 + m * .5) + 'deg)';
        document.getElementById('adxMin').style.transform = 'rotate(' + (m * 6 + s * .1) + 'deg)';
        document.getElementById('adxSec').style.transform = 'rotate(' + (s * 6) + 'deg)';
    }
    tickClock();
    setInterval(tickClock, 1000);

    /* ── Weather via Open-Meteo (no API key needed) ───────────────── */
    var CITY = @json($user->city ?? 'Kanpur');
    var WX = {
        0: ['Clear sky', '☀️'], 1: ['Mainly clear', '🌤️'], 2: ['Partly cloudy', '⛅'], 3: ['Overcast', '☁️'],
        45: ['Fog', '🌫️'], 48: ['Rime fog', '🌫️'], 51: ['Light drizzle', '🌦️'], 53: ['Drizzle', '🌧️'],
        55: ['Heavy drizzle', '🌧️'], 61: ['Light rain', '🌦️'], 63: ['Rain', '🌧️'], 65: ['Heavy rain', '⛈️'],
        71: ['Light snow', '🌨️'], 73: ['Snow', '🌨️'], 75: ['Heavy snow', '❄️'],
        80: ['Rain showers', '🌦️'], 81: ['Showers', '🌧️'], 82: ['Heavy showers', '⛈️'], 95: ['Thunderstorm', '⛈️']
    };
    fetch('https://geocoding-api.open-meteo.com/v1/search?name=' + encodeURIComponent(CITY) + '&count=1')
        .then(function (r) { return r.json(); })
        .then(function (g) {
            if (!g.results || !g.results.length) throw 0;
            var c = g.results[0];
            return fetch('https://api.open-meteo.com/v1/forecast?latitude=' + c.latitude + '&longitude=' + c.longitude + '&current=temperature_2m,weather_code');
        })
        .then(function (r) { return r.json(); })
        .then(function (w) {
            var code = w.current.weather_code, info = WX[code] || ['—', '🌡️'];
            document.getElementById('adxWxTemp').textContent = Math.round(w.current.temperature_2m) + '°C';
            document.getElementById('adxWxCond').textContent = info[0];
            document.getElementById('adxWxIcon').textContent = info[1];
        })
        .catch(function () {
            document.getElementById('adxWxTemp').textContent = '--°C';
            document.getElementById('adxWxCond').textContent = 'Unavailable';
        });

    /* ── Donut chart + legend ─────────────────────────────────────── */
    var legendEl = document.getElementById('adxLegend');
    DONUT.forEach(function (d) {
        var pct = TOTAL_ROOMS > 0 ? (d[1] / TOTAL_ROOMS * 100).toFixed(1) : 0;
        legendEl.insertAdjacentHTML('beforeend',
            '<div class="lg-row"><span class="lg-dot" style="background:' + d[2] + '"></span>' +
            '<span class="lg-name">' + d[0] + '</span>' +
            '<span class="lg-count">' + d[1] + '</span>' +
            '<span class="lg-pct">(' + pct + '%)</span></div>');
    });

    var donutCfg = {
        type: 'doughnut',
        data: {
            labels: DONUT.map(function (d) { return d[0]; }),
            datasets: [{
                data: DONUT.map(function (d) { return d[1]; }),
                backgroundColor: DONUT.map(function (d) { return d[2]; }),
                borderWidth: 2, borderColor: '#fff'
            }]
        },
        options: {
            cutout: '68%', responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: true } }
        }
    };
    new Chart(document.getElementById('adxDonut'), donutCfg);

    /* ── Trend charts (7-day default, switchable to 30) ───────────── */
    function slice(n) { return WEEKLY.slice(-n); }
    var GRID = { color: '#f1f5f9' };
    var TICKS = { color: '#64748b', font: { size: 11 } };

    function lineDataset(label, color, key, fill) {
        return {
            label: label, data: key, borderColor: color, backgroundColor: fill || color + '22',
            fill: !!fill, tension: .35, borderWidth: 2.5, pointRadius: 3, pointHoverRadius: 5
        };
    }
    function baseOpts(yFmt, stacked) {
        return {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: GRID, ticks: TICKS },
                y: { grid: GRID, ticks: { color: '#64748b', font: { size: 11 }, callback: yFmt }, beginAtZero: true, stacked: !!stacked }
            }
        };
    }

    var revChart = new Chart(document.getElementById('adxRevChart'), {
        type: 'line',
        data: { labels: [], datasets: [lineDataset('Revenue', '#6d5ce7', [])] },
        options: baseOpts(function (v) { return '₹' + Number(v).toLocaleString('en-IN'); })
    });
    var occChart = new Chart(document.getElementById('adxOccChart'), {
        type: 'line',
        data: { labels: [], datasets: [lineDataset('Occupancy', '#6d5ce7', [])] },
        options: baseOpts(function (v) { return v + '%'; })
    });
    var adrChart = new Chart(document.getElementById('adxAdrChart'), {
        type: 'line',
        data: { labels: [], datasets: [lineDataset('ADR', '#a855f7', []), lineDataset('RevPAR', '#10b981', [])] },
        options: $.extend(true, {}, baseOpts(function (v) { return '₹' + Number(v).toLocaleString('en-IN'); }), {
            plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 11 }, usePointStyle: true } } }
        })
    });

    function updateCharts(n) {
        var d = slice(n);
        revChart.data.labels = d.map(function (x) { return x.label; });
        revChart.data.datasets[0].data = d.map(function (x) { return x.revenue; });
        revChart.update();

        occChart.data.labels = d.map(function (x) { return x.label; });
        occChart.data.datasets[0].data = d.map(function (x) { return x.occupancy; });
        occChart.update();

        adrChart.data.labels = d.map(function (x) { return x.label; });
        adrChart.data.datasets[0].data = d.map(function (x) { return x.adr; });
        adrChart.data.datasets[1].data = d.map(function (x) { return x.revpar; });
        adrChart.update();
    }
    updateCharts(7);

    document.querySelectorAll('.adx-range').forEach(function (sel) {
        sel.addEventListener('change', function () { updateCharts(parseInt(sel.value, 10)); });
    });
})();
});
</script>
@endsection
