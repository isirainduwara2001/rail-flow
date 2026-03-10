@extends('layouts.app')

@section('title', 'My Bookings - RailFlow')

@section('content')
    <div class="container-fluid py-4">

        <div class="row">
            <div class="col-12">
                <div class="card my-4 border-0 shadow-lg">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                            <h6 class="text-white text-capitalize ps-3">My Booking History</h6>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="bookingsTable">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Train & Route</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Seat</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Date</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Amount</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-hidden="true">
        
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Ticket Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="modalContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Live Train Tracking Modal -->
    <div class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">
                        <i class="material-icons align-middle me-2">train</i>
                        Live Train Tracking
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-md-4 p-3">
                            <div class="mb-2">
                                <span class="text-muted text-xs d-block">Route</span>
                                <span class="fw-bold" id="trackRouteLabel">-</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted text-xs d-block">Departure</span>
                                <span class="fw-bold text-primary" id="trackDepartureLabel">-</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted text-xs d-block">Arrival (Scheduled / ETA)</span>
                                <span class="fw-bold text-secondary" id="trackArrivalLabel">-</span>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <span class="text-muted text-xs d-block">Last Update</span>
                                <span class="fw-bold" id="trackUpdatedLabel">Waiting for live data...</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted text-xs d-block">Speed</span>
                                <span class="fw-bold" id="trackSpeedLabel">-</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted text-xs d-block">Distance from Departure / to Destination</span>
                                <span class="fw-bold" id="trackDistanceLabel">-</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div id="trackingMap" style="height: 380px; width: 100%; border-radius: 0 0 0.5rem 0; overflow: hidden;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <!-- Demo Controls -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge demo-badge-status idle" id="trackDemoBadge">
                            <span class="demo-dot"></span> DEMO IDLE
                        </span>
                        <button class="btn btn-sm btn-demo-start" id="btn-track-demo-start" title="Simulate train movement along route">
                            <i class="material-icons" style="font-size:1rem;vertical-align:middle;">play_arrow</i> Start Demo
                        </button>
                        <button class="btn btn-sm btn-demo-pause" id="btn-track-demo-pause" disabled>
                            <i class="material-icons" style="font-size:1rem;vertical-align:middle;">pause</i>
                        </button>
                        <button class="btn btn-sm btn-demo-stop" id="btn-track-demo-stop" disabled>
                            <i class="material-icons" style="font-size:1rem;vertical-align:middle;">stop</i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* ── Train Demo Controls in Tracking Modal ── */
        .btn-demo-start {
            background: linear-gradient(135deg,#10b981,#059669);
            border: none; color:#fff; font-weight:700;
            border-radius:0.6rem; transition:all .25s;
            box-shadow:0 0 12px rgba(16,185,129,.35);
        }
        .btn-demo-start:hover  { box-shadow:0 0 20px rgba(16,185,129,.6); color:#fff; }
        .btn-demo-start:disabled { opacity:.4; cursor:not-allowed; box-shadow:none; }

        .btn-demo-pause {
            background:linear-gradient(135deg,#f59e0b,#d97706);
            border:none; color:#fff; font-weight:700;
            border-radius:0.6rem; transition:all .25s;
        }
        .btn-demo-pause:hover   { opacity:.88; color:#fff; }
        .btn-demo-pause:disabled { opacity:.35; cursor:not-allowed; }

        .btn-demo-stop {
            background:rgba(239,68,68,.12);
            border:1px solid rgba(239,68,68,.4);
            color:#ef4444; font-weight:700;
            border-radius:0.6rem; transition:all .25s;
        }
        .btn-demo-stop:hover   { background:rgba(239,68,68,.22); color:#dc2626; }
        .btn-demo-stop:disabled { opacity:.35; cursor:not-allowed; }

        .demo-badge-status {
            font-size:.65rem; font-weight:700; letter-spacing:.1rem;
            padding:.3rem .75rem; border-radius:999px;
            display:inline-flex; align-items:center; gap:.35rem;
        }
        .demo-badge-status.idle    { background:rgba(100,116,139,.15); color:#64748b; border:1px solid rgba(100,116,139,.3); }
        .demo-badge-status.running { background:rgba(16,185,129,.15);  color:#10b981; border:1px solid rgba(16,185,129,.35); }
        .demo-badge-status.paused  { background:rgba(245,158,11,.15);  color:#f59e0b; border:1px solid rgba(245,158,11,.35); }
        .demo-dot {
            width:6px; height:6px; border-radius:50%;
            background:currentColor; display:inline-block;
        }
        @keyframes demo-train-glow {
            0%   { box-shadow: 0 0 0 0   rgba(16,185,129,.8); }
            100% { box-shadow: 0 0 0 12px rgba(16,185,129,0); }
        }
        .demo-glow { animation: demo-train-glow 1.3s ease-out infinite; }
    </style>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        let trackingMap = null;
        let trackingMarker = null;
        let trackingRouteLine = null;
        let trackingIntervalId = null;

        /* ══════════════════════════════════════════════════════════
           DEMO — Hardcoded station coordinates for all rail lines
           ══════════════════════════════════════════════════════════ */

        // Coastal line: Colombo Fort → Matara
        const COASTAL_LINE = [
            { name: 'Colombo Fort',  lat: 6.9344, lng: 79.8501 },
            { name: 'Dehiwala',      lat: 6.8612, lng: 79.8636 },
            { name: 'Mount Lavinia', lat: 6.8391, lng: 79.8670 },
            { name: 'Moratuwa',      lat: 6.7776, lng: 79.8820 },
            { name: 'Panadura',      lat: 6.7138, lng: 79.9068 },
            { name: 'Kalutara',      lat: 6.5854, lng: 79.9640 },
            { name: 'Aluthgama',     lat: 6.4275, lng: 80.0004 },
            { name: 'Hikkaduwa',     lat: 6.1378, lng: 80.0966 },
            { name: 'Galle',         lat: 6.0329, lng: 80.2170 },
            { name: 'Weligama',      lat: 5.9693, lng: 80.4298 },
            { name: 'Matara',        lat: 5.9488, lng: 80.5353 },
        ];

        // Up-country line: Colombo Fort → Nuwara Eliya
        const UPCOUNTRY_LINE = [
            { name: 'Colombo Fort',   lat: 6.9344, lng: 79.8501 },
            { name: 'Maradana',       lat: 6.9272, lng: 79.8653 },
            { name: 'Ragama',         lat: 7.0317, lng: 79.9234 },
            { name: 'Gampaha',        lat: 7.0905, lng: 79.9950 },
            { name: 'Veyangoda',      lat: 7.1569, lng: 80.0592 },
            { name: 'Polgahawela',    lat: 7.3323, lng: 80.2974 },
            { name: 'Rambukkana',     lat: 7.3315, lng: 80.3951 },
            { name: 'Peradeniya Jct', lat: 7.2714, lng: 80.5956 },
            { name: 'Kandy',          lat: 7.2896, lng: 80.6333 },
            { name: 'Gampola',        lat: 7.1648, lng: 80.5737 },
            { name: 'Nawalapitiya',   lat: 7.0560, lng: 80.5347 },
            { name: 'Hatton',         lat: 6.8918, lng: 80.5941 },
            { name: 'Nanu Oya',       lat: 6.9074, lng: 80.7303 },
            { name: 'Nuwara Eliya',   lat: 6.9497, lng: 80.7891 },
        ];

        /**
         * Find the correct station sub-array for a given from/to booking.
         * Tries coastal line first, then up-country. Returns null if not found.
         */
        function getRouteStations(fromName, toName) {
            function findSlice(line, a, b) {
                const norm = s => s.toLowerCase().replace(/[^a-z]/g, '');
                const na = norm(a), nb = norm(b);
                let fi = -1, ti = -1;
                line.forEach((st, i) => {
                    const ns = norm(st.name);
                    // partial match so 'Colombo' matches 'Colombo Fort'
                    if (fi === -1 && (ns.includes(na) || na.includes(ns))) fi = i;
                    if (ti === -1 && (ns.includes(nb) || nb.includes(ns))) ti = i;
                });
                if (fi === -1 || ti === -1) return null;
                if (fi <= ti) return line.slice(fi, ti + 1);
                // reverse direction
                return line.slice(ti, fi + 1).slice().reverse();
            }

            return findSlice(COASTAL_LINE, fromName, toName)
                || findSlice(UPCOUNTRY_LINE, fromName, toName)
                || null;
        }

        /** Generate a bell-curve speed profile for N legs (50–100 km/h) */
        function buildSpeeds(n) {
            return Array.from({ length: n }, (_, i) => {
                const t    = n > 1 ? i / (n - 1) : 0.5;
                const bell = Math.sin(t * Math.PI);
                return Math.round(50 + 50 * bell);
            });
        }

        // Active demo route — populated when Track button is clicked
        let demoStations = [];
        let demoSpeeds   = [];

        const LEG_DURATION_MS  = 4000; // ms per station-to-station leg
        const TICK_MS          = 50;   // animation tick

        let demoState        = 'IDLE'; // IDLE | RUNNING | PAUSED
        let demoInterval     = null;
        let demoRunning      = false;  // guard for real IoT pan
        let demoLegIndex     = 0;
        let demoLegProgress  = 0;
        let demoRouteLine    = null;
        let demoStationDots  = [];
        let demoTrainMarker  = null;

        /* ── Demo Helpers ── */
        function demoLerp(a, b, t) { return a + (b - a) * t; }

        function buildDemoTrainIcon() {
            return L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="demo-glow" style="
                    background:#10b981;width:22px;height:22px;border-radius:50%;
                    border:3px solid #fff;box-shadow:0 0 10px #10b981;
                    display:flex;align-items:center;justify-content:center;
                    font-size:12px;line-height:1;">&#x1F686;</div>`,
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            });
        }

        function drawDemoRoute() {
            const latlngs = demoStations.map(s => [s.lat, s.lng]);
            demoRouteLine = L.polyline(latlngs, {
                color: '#38bdf8', weight: 4, opacity: .8, dashArray: '8 5'
            }).addTo(trackingMap);

            demoStations.forEach((s, i) => {
                const isEnd = (i === 0 || i === demoStations.length - 1);
                const dot = L.circleMarker([s.lat, s.lng], {
                    radius: isEnd ? 9 : 6,
                    fillColor: isEnd ? '#f59e0b' : '#38bdf8',
                    color: '#fff', weight: 2, fillOpacity: 1
                }).addTo(trackingMap);
                dot.bindTooltip(s.name, { permanent: true, direction: 'top', offset: [0, -10] });
                demoStationDots.push(dot);
            });

            trackingMap.fitBounds(demoRouteLine.getBounds(), { padding: [30, 30] });
        }

        function removeDemoLayers() {
            if (demoRouteLine)   { trackingMap.removeLayer(demoRouteLine); demoRouteLine = null; }
            if (demoTrainMarker) { trackingMap.removeLayer(demoTrainMarker); demoTrainMarker = null; }
            demoStationDots.forEach(d => trackingMap.removeLayer(d));
            demoStationDots = [];
        }

        function setDemoBadge(state) {
            const b = $('#trackDemoBadge');
            b.removeClass('idle running paused');
            if      (state === 'RUNNING') { b.addClass('running'); b.html('<span class="demo-dot"></span> RUNNING'); }
            else if (state === 'PAUSED')  { b.addClass('paused');  b.html('<span class="demo-dot"></span> PAUSED'); }
            else                          { b.addClass('idle');    b.html('<span class="demo-dot"></span> DEMO IDLE'); }
        }

        function syncDemoButtons() {
            const running = demoState === 'RUNNING';
            const active  = running || demoState === 'PAUSED';
            $('#btn-track-demo-start').prop('disabled', running);
            $('#btn-track-demo-pause').prop('disabled', !running);
            $('#btn-track-demo-stop').prop('disabled',  !active);
        }

        function demoTick() {
            if (demoState !== 'RUNNING') return;
            const totalLegs = demoStations.length - 1;
            if (totalLegs < 1 || demoLegIndex >= totalLegs) { stopDemoFull(true); return; }

            demoLegProgress += TICK_MS / LEG_DURATION_MS;
            if (demoLegProgress >= 1.0) {
                demoLegProgress = 0;
                demoLegIndex++;
                if (demoLegIndex >= totalLegs) { stopDemoFull(true); return; }
            }

            const from = demoStations[demoLegIndex];
            const to   = demoStations[demoLegIndex + 1];
            const lat  = demoLerp(from.lat, to.lat, demoLegProgress);
            const lng  = demoLerp(from.lng, to.lng, demoLegProgress);

            if (demoTrainMarker) demoTrainMarker.setLatLng([lat, lng]);

            const speed = demoSpeeds[demoLegIndex] || 80;
            const prog  = Math.round(((demoLegIndex + demoLegProgress) / totalLegs) * 100);
            $('#trackSpeedLabel').text(speed + ' km/h (Demo)');
            $('#trackUpdatedLabel').text(`${from.name} → ${to.name}  •  ${prog}% complete`);
        }

        function startDemoFull() {
            if (!trackingMap) return;
            if (demoStations.length < 2) {
                alert('Route data not found for this booking. Cannot start demo.');
                return;
            }
            if (demoState === 'IDLE') {
                demoLegIndex = 0; demoLegProgress = 0;
                removeDemoLayers();
                drawDemoRoute();
                demoTrainMarker = L.marker(
                    [demoStations[0].lat, demoStations[0].lng],
                    { icon: buildDemoTrainIcon() }
                ).addTo(trackingMap);
                demoRunning = true;
            }
            demoState = 'RUNNING';
            demoInterval = setInterval(demoTick, TICK_MS);
            setDemoBadge('RUNNING');
            syncDemoButtons();
            const first = demoStations[0].name;
            const last  = demoStations[demoStations.length - 1].name;
            $('#trackRouteLabel').text(`${first} → ${last} (Demo)`);
        }

        function pauseDemoFull() {
            if (demoState !== 'RUNNING') return;
            demoState = 'PAUSED';
            clearInterval(demoInterval); demoInterval = null;
            setDemoBadge('PAUSED');
            syncDemoButtons();
        }

        function stopDemoFull(completed) {
            clearInterval(demoInterval); demoInterval = null;
            if (completed) {
                const dest = demoStations.length ? demoStations[demoStations.length - 1].name : 'Destination';
                $('#trackUpdatedLabel').text(`Journey Complete — Arrived at ${dest} ✓`);
                $('#trackSpeedLabel').text('0 km/h');
            }
            setTimeout(function () {
                removeDemoLayers();
                demoState = 'IDLE'; demoRunning = false;
                setDemoBadge('IDLE');
                syncDemoButtons();
                $('#trackUpdatedLabel').text('Live tracking resumed.');
            }, completed ? 2000 : 0);
            if (!completed) { demoState = 'IDLE'; demoRunning = false; setDemoBadge('IDLE'); syncDemoButtons(); }
        }

        $(document).ready(function () {

            /* ── Demo Button Handlers ── */
            $('#btn-track-demo-start').on('click', function () {
                if (demoState === 'IDLE')   startDemoFull();
                else if (demoState === 'PAUSED') {
                    demoState = 'RUNNING';
                    demoInterval = setInterval(demoTick, TICK_MS);
                    setDemoBadge('RUNNING');
                    syncDemoButtons();
                }
            });
            $('#btn-track-demo-pause').on('click', function () { pauseDemoFull(); });
            $('#btn-track-demo-stop').on('click',  function () { stopDemoFull(false); });

            loadBookings();

            function loadBookings() {
                $.ajax({
                    url: "{{ route('booking.bookings-data') }}",
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            const tbody = $('#bookingsTable tbody');
                            tbody.empty();

                            if (response.bookings.length === 0) {
                                tbody.append('<tr><td colspan="6" class="text-center py-5 text-muted">No bookings found.</td></tr>');
                                return;
                            }

                            response.bookings.forEach(booking => {
                                const statusBadge = booking.status === 'confirmed' ? 'bg-gradient-success' : 'bg-gradient-secondary';

                                tbody.append(`
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">${booking.train}</h6>
                                                <p class="text-xs text-secondary mb-0">${booking.from} → ${booking.to}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">${booking.seat}</p>
                                        <p class="text-xs text-secondary mb-0">Booking ID: #${booking.id}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm ${statusBadge}">${booking.status}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">${booking.departure}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">LKR ${parseFloat(booking.price).toLocaleString()}</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex flex-column flex-sm-row gap-1">
                                            <button class="btn btn-link text-secondary mb-0 p-0 view-details" data-id="${booking.id}">
                                                <i class="material-icons text-sm align-middle">visibility</i>
                                                <span class="ms-1">Details</span>
                                            </button>
                                            <button class="btn btn-link text-primary mb-0 p-0 track-train"
                                                data-id="${booking.id}"
                                                data-from="${booking.from}"
                                                data-to="${booking.to}">
                                                <i class="material-icons text-sm align-middle">train</i>
                                                <span class="ms-1">Track</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `);
                            });
                        }
                    }
                });
            }

            $(document).on('click', '.view-details', function () {
                const id = $(this).data('id');
                const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));

                $('#modalContent').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');
                modal.show();

                $.ajax({
                    url: `/booking/${id}`,
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            const b = response.booking;
                            $('#modalContent').html(`
                            <div class="text-center mb-4">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${b.reference}" alt="QR" class="img-thumbnail mb-3" style="width: 150px;">
                                <h6 class="fw-bold mb-0">${b.reference}</h6>
                                <p class="text-muted small">Scan for verification</p>
                            </div>
                            <div class="bg-light p-3 rounded-3 border border-dashed">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Train:</span>
                                    <span class="fw-bold">${b.train.name} (${b.train.number})</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Route:</span>
                                    <span class="fw-bold">${b.schedule.from} → ${b.schedule.to}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Departure:</span>
                                    <span class="fw-bold text-primary">${b.schedule.departure}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Seat:</span>
                                    <span class="fw-bold">${b.seat.number} (${b.seat.class})</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Total Paid:</span>
                                    <span class="fw-bold text-success">LKR ${parseFloat(b.price).toLocaleString()}</span>
                                </div>
                            </div>
                            <div class="mt-4 d-grid">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        `);
                        }
                    }
                });
            });

            $(document).on('click', '.track-train', function () {
                const id = $(this).data('id');
                const modalElement = document.getElementById('trackingModal');
                const modal = new bootstrap.Modal(modalElement);

                $('#trackRouteLabel').text('-');
                $('#trackDepartureLabel').text('-');
                $('#trackArrivalLabel').text('-');
                $('#trackUpdatedLabel').text('Waiting for live data...');
                $('#trackSpeedLabel').text('-');
                $('#trackDistanceLabel').text('-');

                // ── Resolve demo route from booking's from/to ──
                const bookingFrom = $(this).data('from') || '';
                const bookingTo   = $(this).data('to')   || '';

                // Stop any running demo and reset stations for this booking
                stopDemoFull(false);
                const resolvedStations = getRouteStations(bookingFrom, bookingTo);
                if (resolvedStations && resolvedStations.length >= 2) {
                    demoStations = resolvedStations;
                    demoSpeeds   = buildSpeeds(demoStations.length - 1);
                } else {
                    demoStations = [];
                    demoSpeeds   = [];
                }

                modal.show();

                // Clear any existing polling when opening
                if (trackingIntervalId) {
                    clearInterval(trackingIntervalId);
                    trackingIntervalId = null;
                }

                function fetchTrackingData() {
                    $.ajax({
                        url: `/booking/tracking/${id}`,
                        method: 'GET',
                        success: function (response) {
                            if (!response.success) {
                                $('#trackUpdatedLabel').text(response.message || 'Unable to load tracking data.');
                                return;
                            }

                            const booking = response.booking;
                            const route = response.route || [];
                            const location = response.location || null;

                            $('#trackRouteLabel').text(`${booking.from} → ${booking.to}`);
                            $('#trackDepartureLabel').text(booking.departure);
                            if (booking.estimated_arrival) {
                                $('#trackArrivalLabel').text(`${booking.arrival} (ETA: ${booking.estimated_arrival})`);
                            } else {
                                $('#trackArrivalLabel').text(booking.arrival);
                            }

                            // Initialize map once with route
                            if (!trackingMap) {
                                const initialLatLng = route.length
                                    ? [route[0].latitude, route[0].longitude]
                                    : [6.9271, 79.8612]; // Fallback to Colombo area

                                trackingMap = L.map('trackingMap', {
                                    zoomControl: true,
                                    attributionControl: false
                                }).setView(initialLatLng, 11);

                                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                                    maxZoom: 18
                                }).addTo(trackingMap);
                            }

                            // Route polyline is drawn by the demo only when Start Demo is clicked.

                            // Update train marker using latest IoT data (single train)
                            if (location && location.latitude && location.longitude) {
                                const latlng = [location.latitude, location.longitude];

                                if (!trackingMarker) {
                                    const trainIcon = L.divIcon({
                                        className: 'custom-div-icon',
                                        html: "<div style='display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:rgba(15,23,42,0.9);border:2px solid #f97316;box-shadow:0 0 14px rgba(249,115,22,0.9);'><span class='material-icons' style='font-size:18px;color:#facc15;'>train</span></div>",
                                        iconSize: [30, 30],
                                        iconAnchor: [15, 15]
                                    });

                                    trackingMarker = L.marker(latlng, { icon: trainIcon }).addTo(trackingMap);
                                } else {
                                    trackingMarker.setLatLng(latlng);
                                }

                                // Only pan if demo is not taking over the map
                                if (!demoRunning) trackingMap.panTo(latlng);

                                $('#trackUpdatedLabel').text(location.updated_at);
                                $('#trackSpeedLabel').text(location.speed !== null ? `${parseFloat(location.speed).toFixed(1)} km/h` : '-');
                                if (location.distance_from_departure_km !== null && location.distance_to_destination_km !== null) {
                                    const fromDep = parseFloat(location.distance_from_departure_km).toFixed(1);
                                    const toDest = parseFloat(location.distance_to_destination_km).toFixed(1);
                                    $('#trackDistanceLabel').text(`${fromDep} km from start • ${toDest} km to destination`);
                                } else {
                                    $('#trackDistanceLabel').text('-');
                                }
                            } else {
                                $('#trackUpdatedLabel').text('Live location not available yet.');
                                $('#trackSpeedLabel').text('-');
                            }
                        },
                        error: function () {
                            $('#trackUpdatedLabel').text('Error loading tracking data.');
                        }
                    });
                }

                // Initial fetch and then poll every 3 seconds
                fetchTrackingData();
                trackingIntervalId = setInterval(fetchTrackingData, 3000);

                // Stop polling when modal is hidden
                modalElement.addEventListener('hidden.bs.modal', function () {
                    if (trackingIntervalId) {
                        clearInterval(trackingIntervalId);
                        trackingIntervalId = null;
                    }

                    // Do not destroy the map instance; just keep as-is for next open
                }, { once: true });
            });
        });
    </script>

    <style>
        .bg-gradient-primary {
            background-image: linear-gradient(195deg, #EC407A 0%, #D81B60 100%);
        }

        .shadow-primary {
            box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(233, 30, 99, 0.4);
        }

        .border-radius-lg {
            border-radius: 0.75rem;
        }
    </style>
@endsection