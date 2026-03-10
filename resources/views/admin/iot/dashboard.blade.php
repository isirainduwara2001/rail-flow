@extends('layouts.app')
@section('title', 'IoT Live Command Center - RailFlow')
@section('styles')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .iot-dashboard-container {
            background: #020617;
            color: #f8fafc;
            border-radius: 1.5rem;
            padding: 2.5rem;
            min-height: 90vh;
            font-family: 'Inter', sans-serif;
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.25rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(96, 165, 250, 0.3);
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
        }

        .metric-value {
            font-size: 2.75rem;
            font-weight: 900;
            letter-spacing: -0.05em;
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .weather-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e2e8f0;
        }

        .metric-label {
            text-transform: uppercase;
            letter-spacing: 0.15rem;
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 700;
        }

        #live-map {
            height: 500px;
            width: 100%;
            border-radius: 1rem;
        }

        .status-badge {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.6rem;
        }

        .status-online {
            background: #10b981;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.6);
        }

        .pulse {
            animation: pulse-animation 2s infinite;
        }

        @keyframes pulse-animation {
            0% {
                box-shadow: 0 0 0 0px rgba(16, 185, 129, 0.7);
            }

            100% {
                box-shadow: 0 0 0 12px rgba(16, 185, 129, 0);
            }
        }

        .stream-container {
            position: relative;
            width: 100%;
            padding-top: 56.25%;
            /* 16:9 Aspect Ratio */
            background: #000;
            border-radius: 1rem;
            overflow: hidden;
        }

        .stream-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            backdrop-filter: blur(8px);
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .mini-map {
            height: 180px;
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: #0f172a;
        }
    </style>

@endsection

@section('content')
    <div class="iot-dashboard-container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <div class="d-flex align-items-center mb-1">
                    <span class="status-badge status-online pulse"></span>
                    <span class="text-xs fw-bold text-success text-uppercase tracking-wider">Operational Matrix Live</span>
                </div>
                <h1 class="fw-black mb-0 display-5" style="letter-spacing: -2px;">IOT COMMAND CENTER</h1>
            </div>
            <div class="text-end">
                <h3 class="mb-0 fw-bold" id="current-time">--:--:--</h3>
                <button class="btn btn-sm btn-glass mt-2" data-bs-toggle="modal" data-bs-target="#settingsModal">
                    <i class="material-icons align-middle me-1">settings</i> Config
                </button>
            </div>
        </div>


        <!-- Top Grid: Real-time Sensors & Weather -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="metric-label">Wind</div>
                        <i class="material-icons text-white">speed</i>
                    </div>
                    <div class="metric-value mb-1" id="wind_speed">{{ number_format($latest->speed ?? 0, 1) }}</div>
                    <div class="text-slate-400 text-xs">km/h Stream</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="metric-label">Temperature</div>
                        <i class="material-icons text-white">thermostat</i>
                    </div>
                    <div class="metric-value mb-1" id="temperature">{{ number_format($latest->temperature ?? 0, 1) }}</div>
                    <div class="text-slate-400 text-xs">°C</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="metric-label">Light</div>
                        <i class="material-icons text-white">light_mode</i>
                    </div>
                    <div class="metric-value mb-1 fs-1" id="lux">{{ $latest->lux ?? 0 }}</div>
                    <div class="text-slate-400 text-xs">Lux Units</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="metric-label">Rain</div>
                        <i class="material-icons text-white">cloud</i>
                    </div>
                    <div class="metric-value mb-1 fs-1" id="rain_percentage">{{ $latest->rain_percentage ?? 0 }}</div>
                    <div class="text-slate-400 text-xs">%</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="metric-label">Humidity</div>
                        <i class="material-icons text-white">water_drop</i>
                    </div>
                    <div class="metric-value mb-1 fs-1" id="humidity">{{ $latest->humidity ?? 0 }}</div>
                    <div class="text-slate-400 text-xs">%</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="glass-card p-4" id="flood-monitor-card" style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="metric-label">Flood Monitor</div>
                        <i class="material-icons text-white">flood</i>
                    </div>
                    <div class="metric-value mb-1 fs-1">Stats</div>
                    <div class="text-slate-400 text-xs">Click to open</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="text-white fw-bold mb-0"><i class="material-icons align-middle me-2">cloud_queue</i>
                            WEATHER INTELLIGENCE (API)</h5>
                        <span class="text-xs text-slate-500 tracking-widest">STATION CONTEXT</span>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-2 border-end border-slate-700">
                            <div class="metric-label mb-1">Feels Like</div>
                            <div class="h4 mb-0 text-white" id="val-feels-like">
                                {{ number_format($weather['main']['feels_like'] ?? 0, 1) }}°C
                            </div>
                        </div>
                        <div class="col-md-2 border-end border-slate-700">
                            <div class="metric-label mb-1">Min / Max</div>
                            <div class="h4 mb-0 text-white" id="val-temp-range">
                                {{ number_format($weather['main']['temp_min'] ?? 0, 1) }} /
                                {{ number_format($weather['main']['temp_max'] ?? 0, 1) }}°C
                            </div>
                        </div>
                        <div class="col-md-2 border-end border-slate-700">
                            <div class="metric-label mb-1">Humidity (API)</div>
                            <div class="h4 mb-0 text-white" id="val-humidity-api">{{ $weather['main']['humidity'] ?? 0 }}%
                            </div>
                        </div>
                        <div class="col-md-2 border-end border-slate-700">
                            <div class="metric-label mb-1">Visibility</div>
                            <div class="h4 mb-0 text-white" id="val-visibility">
                                {{ number_format(($weather['visibility'] ?? 10000) / 1000, 1) }}km
                            </div>
                        </div>
                        <div class="col-md-2 border-end border-slate-700">
                            <div class="metric-label mb-1">Sunrise</div>
                            <div class="h4 mb-0 text-white" id="val-sunrise">
                                {{ isset($weather['sys']['sunrise']) ? date('H:i', $weather['sys']['sunrise']) : '--:--' }}
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="metric-label mb-1">Sunset</div>
                            <div class="h4 mb-0 text-white" id="val-sunset">
                                {{ isset($weather['sys']['sunset']) ? date('H:i', $weather['sys']['sunset']) : '--:--' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

        <!-- Middle Grid: Map & Stream -->
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="glass-card p-2">
                    <div id="live-map"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-white text-uppercase tracking-widest mb-0 small">Tactical Video Feed</h6>
                        <span class="badge bg-danger pulse-red">Live</span>
                    </div>
                    <div class="stream-container shadow-2xl">
                        <iframe id="stream-iframe" src="{{ $streamUrl }}" allow="autoplay; encrypted-media"
                            allowfullscreen></iframe>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card p-4">
                    <h6 class="text-white text-uppercase mb-4 small">Sensor Interference Matrix</h6>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="metric-label">Frontal Proximity</span>
                            <span class="text-xs fw-bold"
                                id="val-front">{{ number_format($latest->sf_front_distance ?? 0, 2) }}m</span>
                        </div>
                        <div class="progress bg-slate-800" style="height: 6px;">
                            <div id="bar-front" class="progress-bar bg-primary"
                                style="width: {{ min(($latest->sf_front_distance ?? 0) * 10, 100) }}%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="metric-label">Lateral Proximity</span>
                            <span class="text-xs fw-bold"
                                id="val-side">{{ number_format($latest->sf_side_distance ?? 0, 2) }}m</span>
                        </div>
                        <div class="progress bg-slate-800" style="height: 6px;">
                            <div id="bar-side" class="progress-bar bg-info"
                                style="width: {{ min(($latest->sf_side_distance ?? 0) * 10, 100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="metric-label">Precipitation Factor</span>
                            <span class="text-xs fw-bold" id="val-rain">{{ $latest->rain_percentage ?? 0 }}%</span>
                        </div>
                        <div class="progress bg-slate-800" style="height: 6px;">
                            <div id="bar-rain" class="progress-bar bg-warning"
                                style="width: {{ $latest->rain_percentage ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Bottom Grid: Object Detection & Disaster History Refined -->
        <div class="row g-4 mt-2">
            <!-- Latest Object Detection -->
            <div class="col-lg-7">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="text-white fw-bold mb-0">
                            <i class="material-icons align-middle me-2">visibility</i> LATEST OBJECT DETECTION
                        </h5>
                        <span class="text-xs text-slate-500 tracking-widest">REAL-TIME ANALYSIS</span>
                    </div>
                    
                    @if($latestDetection)
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <center>
                                    @if($latestDetection->image)
                                    <img src="{{ asset('storage/' . $latestDetection->image) }}" class="w-100" alt="Detection Image" onerror="this.src='https://via.placeholder.com/400x300?text=Object+Evidence'; this.onerror=null;">
                                @else
                                    <div class="bg-slate-800 rounded-lg aspect-video d-flex flex-column align-items-center justify-content-center text-slate-500 border border-slate-700">
                                        <i class="material-icons fs-1 mb-2">image_not_supported</i>
                                        <span class="small">No Evidence Image</span>
                                    </div>
                                @endif
                                </center>
                            </div>
                            <div class="col-md-12 mt-3">
                                <div class="ps-md-2 mt-3 mt-md-0">
                                    <div class="d-flex justify-content-between mb-3 border-bottom border-slate-800 pb-2">
                                        <span class="text-slate-400 small">Detection Type</span>
                                        <span class="fw-bold text-white fs-5">{{ $latestDetection->type }}</span>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="bg-slate-800/50 p-3 rounded-lg border border-slate-700/30">
                                                <div class="text-slate-500 text-xs text-uppercase mb-1 tracking-tighter">Proximity</div>
                                                <div class="h4 mb-0 fw-black text-info">{{ number_format($latestDetection->distance, 1) }}m</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-slate-800/50 p-3 rounded-lg border border-slate-700/30">
                                                <div class="text-slate-500 text-xs text-uppercase mb-1 tracking-tighter">Scale Factor</div>
                                                <div class="h4 mb-0 fw-black text-warning">{{ $latestDetection->size }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-slate-900/40 p-3 rounded-lg border border-slate-800">
                                        <div class="d-flex align-items-center text-slate-400 small mb-1">
                                            <i class="material-icons fs-6 me-2">location_on</i>
                                            <span id="detection-location">{{ number_format($latestDetection->latitude, 6) }}, {{ number_format($latestDetection->longitude, 6) }}</span>
                                        </div>
                                        <div class="d-flex align-items-center text-slate-500 small">
                                            <i class="material-icons fs-6 me-2">schedule</i>
                                            Verified: {{ $latestDetection->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div id="detection-map" class="mini-map shadow-lg"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5 text-slate-500">
                            <i class="material-icons d-block mb-2 fs-2">sensors_off</i>
                            No detection metadata available in current uplink
                        </div>
                    @endif
                </div>
            </div>

            <!-- Latest Disaster Report -->
            <div class="col-lg-5">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="text-white fw-bold mb-0">
                            <i class="material-icons align-middle me-2">warning</i> LATEST DISASTER REPORT
                        </h5>
                        <span class="text-xs text-slate-500 tracking-widest">RISK ASSESSMENT</span>
                    </div>
                    
                    @if($latestDisaster)
                        <div class="d-flex flex-column justify-content-center">
                            <div class="text-center mb-4">
                                <div class="d-inline-block p-4 rounded-circle mb-3 {{ $latestDisaster->risk_level === 'High' ? 'bg-danger/20 text-danger shadow-[0_0_30px_rgba(220,38,38,0.3)]' : ($latestDisaster->risk_level === 'Medium' ? 'bg-warning/20 text-warning' : 'bg-info/20 text-info') }}">
                                    <i class="material-icons display-4">warning</i>
                                </div>
                                <h2 class="fw-black text-white mb-1 tracking-tight" id="disaster-city">{{ strtoupper($latestDisaster->city) }}</h2>
                                <span class="badge {{ $latestDisaster->risk_level === 'High' ? 'bg-danger' : ($latestDisaster->risk_level === 'Medium' ? 'bg-warning' : 'bg-info') }} px-4 py-2 fs-6 mb-4" id="disaster-risk">
                                    {{ $latestDisaster->risk_level }} RISK DETECTED
                                </span>
                            </div>

                            <div class="mb-4">
                                <div id="disaster-map" class="mini-map shadow-lg"></div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="metric-label text-slate-500 mb-1">Geographic Origin</div>
                                    <div class="text-white small fw-bold" id="disaster-location">
                                        {{ number_format($latestDisaster->latitude, 4) }}N, {{ number_format($latestDisaster->longitude, 4) }}E
                                    </div>
                                </div>
                                <div class="col-6 text-end">
                                    <div class="metric-label text-slate-500 mb-1">Response Status</div>
                                    <div class="text-success small fw-bold d-flex align-items-center justify-content-end">
                                        <span class="status-badge status-online me-2"></span> Monitored
                                    </div>
                                </div>
                                <div class="col-12 mt-4 border-top border-slate-700 pt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-xs text-slate-500 italic">Report generated {{ $latestDisaster->created_at->diffForHumans() }}</span>
                                        <a href="{{ route('disaster-history.index') }}" class="btn btn-sm btn-glass px-3">View Archive</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5 text-slate-500">
                            <i class="material-icons d-block mb-2 fs-1 text-success">check_circle_outline</i>
                            System Nominal: No active risk reports in vicinity
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-0" style="background: #1e293b;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-white fw-bold">Dashboard Matrix Config</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="metric-label mb-2 d-block">Main Tactical Stream URL</label>
                        <div class="input-group">
                            <input type="text" id="stream-url-input" class="form-control" value="{{ $streamUrl }}">
                            <button class="btn btn-primary" id="save-stream-url">Apply</button>
                        </div>
                        <small class="text-slate-500 mt-2 d-block">Supports YouTube embed links, RTSP converters, and static
                            feeds.</small>
                    </div>
                    <div class="mb-0">
                        <label class="metric-label mb-2 d-block">OpenWeather Matrix Status</label>
                        <div class="p-3 bg-slate-900 rounded border border-slate-700">
                            @if(env('OPENWEATHER_API_KEY'))
                                <div class="text-success d-flex align-items-center">
                                    <i class="material-icons fs-6 me-2">check_circle</i> API Identity Verified
                                </div>
                            @else
                                <div class="text-warning d-flex align-items-center">
                                    <i class="material-icons fs-6 me-2">warning</i> API Key Required in .env
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-glass w-100" data-bs-dismiss="modal">Close Uplink</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        let map;
        let marker;
        let detectionMap, detectionMarker;
        let disasterMap, disasterMarker;

        // Initialize Map
        function initMap(lat, lng) {
            map = L.map('live-map', {
                zoomControl: true,
                attributionControl: false
            }).setView([lat, lng], 15);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 20
            }).addTo(map);

            const trainIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<div style='background-color:#60a5fa; width:16px; height:16px; border-radius:50%; border:3px solid #fff; box-shadow: 0 0 10px #60a5fa;' class='pulse'></div>",
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });

            marker = L.marker([lat, lng], { icon: trainIcon }).addTo(map);
        }

        function updateDashboard() {
            $.get('/api/iot/latest', function (response) {
                const data = response.latest;
                const weather = response.weather;

                if (!data) return;

                // Update Sensors
                $('#wind_speed').text(parseFloat(data.speed).toFixed(1));
                $('#temperature').text(parseFloat(data.temperature).toFixed(1));
                $('#humidity').text(data.humidity);
                $('#lux').text(data.lux);
                $('#rain_percentage').text(data.rain_percentage);

                // Update Weather
                if (weather) {
                    $('#val-weather-main').text(weather.weather[0].main);
                    if (weather.weather[0].icon) {
                        $('#w-icon').attr('src', 'http://openweathermap.org/img/wn/' + weather.weather[0].icon + '.png');
                    }
                    $('#val-wind-speed').text(parseFloat(weather.wind.speed).toFixed(1));
                    $('#val-wind-dir').text(weather.wind.deg);
                    $('#val-clouds').text(weather.clouds.all + '%');
                    $('#val-pressure').text(weather.main.pressure);

                    $('#val-feels-like').text(parseFloat(weather.main.feels_like).toFixed(1) + '°C');
                    $('#val-temp-range').text(parseFloat(weather.main.temp_min).toFixed(1) + ' / ' + parseFloat(weather.main.temp_max).toFixed(1) + '°C');
                    $('#val-humidity-api').text(weather.main.humidity + '%');
                    $('#val-visibility').text(parseFloat(weather.visibility / 1000).toFixed(1) + 'km');

                    if (weather.sys.sunrise) {
                        $('#val-sunrise').text(new Date(weather.sys.sunrise * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
                    }
                    if (weather.sys.sunset) {
                        $('#val-sunset').text(new Date(weather.sys.sunset * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
                    }
                }

                // Update Object Detection Table (Simple Refresh)
                if (response.latestDetection) {
                    const det = response.latestDetection;
                    $('#detection-type').text(det.type);
                    $('#detection-distance').text(parseFloat(det.distance).toFixed(1) + 'm');
                    $('#detection-size').text(det.size);
                    $('#detection-location').html(`<i class="material-icons fs-6 me-1">location_on</i> ${parseFloat(det.latitude).toFixed(4)}, ${parseFloat(det.longitude).toFixed(4)}`);
                    if (det.image) {
                        $('#detection-image').attr('src', '/storage/' + det.image);
                    }
                }

                // Update Disaster Record
                if (response.latestDisaster) {
                    const dis = response.latestDisaster;
                    $('#disaster-city').text(dis.city.toUpperCase());
                    $('#disaster-risk').text(dis.risk_level + ' RISK DETECTED');
                    $('#disaster-location').html(`<i class="material-icons fs-6 me-1">location_on</i> ${parseFloat(dis.latitude).toFixed(4)}, ${parseFloat(dis.longitude).toFixed(4)}`);
                }

                // Update Map
                if (data.latitude && data.longitude) {
                    const latlng = [data.latitude, data.longitude];
                    marker.setLatLng(latlng);
                    map.panTo(latlng);
                }

                // Update Detection Map
                if (response.latestDetection && response.latestDetection.latitude) {
                    const dLat = response.latestDetection.latitude;
                    const dLng = response.latestDetection.longitude;
                    if (detectionMap) {
                        detectionMarker.setLatLng([dLat, dLng]);
                        detectionMap.panTo([dLat, dLng]);
                    }
                }

                // Update Disaster Map
                if (response.latestDisaster && response.latestDisaster.latitude) {
                    const sLat = response.latestDisaster.latitude;
                    const sLng = response.latestDisaster.longitude;
                    if (disasterMap) {
                        disasterMarker.setLatLng([sLat, sLng]);
                        disasterMap.panTo([sLat, sLng]);
                    }
                }
            });
        }

        $(document).ready(function () {

            $('#flood-monitor-card').on('click', function () {
                window.location.href = 'https://rivernet.lk/';
            });

            const initialLat = {{ $latest->latitude ?? 6.9271 }};
            const initialLng = {{ $latest->longitude ?? 79.8612 }};

            initMap(initialLat, initialLng);

            // Init Detection Mini Map
            @if($latestDetection)
                detectionMap = L.map('detection-map', { zoomControl: false, attributionControl: false }).setView([{{ $latestDetection->latitude }}, {{ $latestDetection->longitude }}], 14);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(detectionMap);
                detectionMarker = L.marker([{{ $latestDetection->latitude }}, {{ $latestDetection->longitude }}]).addTo(detectionMap);
            @endif

            // Init Disaster Mini Map
            @if($latestDisaster)
                disasterMap = L.map('disaster-map', { zoomControl: false, attributionControl: false }).setView([{{ $latestDisaster->latitude }}, {{ $latestDisaster->longitude }}], 12);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(disasterMap);
                disasterMarker = L.marker([{{ $latestDisaster->latitude }}, {{ $latestDisaster->longitude }}]).addTo(disasterMap);
            @endif

            // Update time
            setInterval(() => {
                $('#current-time').text(new Date().toLocaleTimeString());
            }, 1000);

            // Poll for data every 3 seconds
            setInterval(updateDashboard, 3000);

            // Save Stream URL
            $('#save-stream-url').on('click', function () {
                const newUrl = $('#stream-url-input').val();
                $(this).prop('disabled', true).text('Matrix Syncing...');

                $.post('/api/settings/update', {
                    key: 'iot_stream_url',
                    value: newUrl,
                    _token: '{{ csrf_token() }}'
                }, function (res) {
                    if (res.success) {
                        $('#stream-iframe').attr('src', newUrl);
                        $('#settingsModal').modal('hide');
                        showNotification('Stream Feed Updated', 'success');
                    }
                    $('#save-stream-url').prop('disabled', false).text('Apply');
                });
            });
        });

        function showNotification(msg, type) {
            // Simple notification check if available
            if (typeof toastr !== 'undefined') {
                toastr[type](msg);
            } else {
                alert(msg);
            }
        }
    </script>
@endsection