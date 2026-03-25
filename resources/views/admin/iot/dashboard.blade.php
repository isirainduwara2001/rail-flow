@extends('layouts.app')

@section('title', 'IoT Live Command Center - RailFlow')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        :root {
            --bg-deep: #020617;
            --bg-card: rgba(30, 41, 59, 0.45);
            --bg-card-hover: rgba(30, 41, 59, 0.65);
            --border-subtle: rgba(255, 255, 255, 0.06);
            --border-accent: rgba(96, 165, 250, 0.25);
            --text-dim: #64748b;
            --text-muted: #94a3b8;
            --text-light: #e2e8f0;
            --accent-blue: #60a5fa;
            --accent-violet: #a78bfa;
            --accent-emerald: #34d399;
            --accent-amber: #fbbf24;
            --accent-rose: #fb7185;
            --radius-lg: 1.25rem;
            --radius-md: 0.875rem;
            --radius-sm: 0.625rem;
        }

        .iot-dashboard-container {
            background: var(--bg-deep);
            color: #f8fafc;
            border-radius: 1.5rem;
            padding: 2rem 2.5rem 3rem;
            min-height: 90vh;
            font-family: 'Inter', sans-serif;
        }

        /* ── Glass Card System ─────────────────────────────── */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            background: var(--bg-card-hover);
            border-color: var(--border-accent);
            transform: translateY(-2px);
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.25);
        }

        /* ── Metric Cards ──────────────────────────────────── */
        .metric-value {
            font-size: 2.25rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            line-height: 1;
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-violet) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .metric-label {
            text-transform: uppercase;
            letter-spacing: 0.12rem;
            font-size: 0.65rem;
            color: var(--text-dim);
            font-weight: 700;
        }
        .metric-unit {
            font-size: 0.7rem;
            color: var(--text-dim);
            font-weight: 600;
        }

        /* ── Section Headers ───────────────────────────────── */
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .section-header h5 {
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.15rem;
            text-transform: uppercase;
            color: var(--text-light);
            margin: 0;
        }
        .section-header .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .section-badge {
            font-size: 0.55rem;
            font-weight: 700;
            letter-spacing: 0.1rem;
            color: var(--text-dim);
            text-transform: uppercase;
        }

        /* ── Risk Banner ───────────────────────────────────── */
        .risk-banner {
            border-radius: var(--radius-lg);
            padding: 1.25rem 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            transition: all 0.4s ease;
        }
        .risk-low    { background: linear-gradient(135deg, rgba(52,211,153,0.12) 0%, rgba(52,211,153,0.03) 100%); border: 1px solid rgba(52,211,153,0.2); }
        .risk-medium { background: linear-gradient(135deg, rgba(251,191,36,0.12) 0%, rgba(251,191,36,0.03) 100%); border: 1px solid rgba(251,191,36,0.2); }
        .risk-high   { background: linear-gradient(135deg, rgba(251,113,133,0.15) 0%, rgba(251,113,133,0.04) 100%); border: 1px solid rgba(251,113,133,0.25); }
        .risk-pending { background: linear-gradient(135deg, rgba(100,116,139,0.1) 0%, rgba(100,116,139,0.02) 100%); border: 1px solid rgba(100,116,139,0.15); }

        .risk-level-badge {
            font-size: 1.25rem;
            font-weight: 900;
            letter-spacing: 0.15rem;
            padding: 0.5rem 1.5rem;
            border-radius: 0.75rem;
        }

        /* ── Map ───────────────────────────────────────────── */
        #live-map {
            height: 380px;
            width: 100%;
            border-radius: var(--radius-md);
        }
        .mini-map {
            height: 160px;
            width: 100%;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-subtle);
            background: #0f172a;
        }

        /* ── Video Stream ──────────────────────────────────── */
        .stream-container {
            position: relative;
            width: 100%;
            padding-top: 56.25%;
            background: #000;
            border-radius: var(--radius-md);
            overflow: hidden;
        }
        .stream-container iframe {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            border: 0;
        }

        /* ── Progress Bars ─────────────────────────────────── */
        .sensor-progress {
            height: 5px;
            border-radius: 3px;
            background: rgba(15, 23, 42, 0.8);
        }
        .sensor-progress .progress-bar {
            border-radius: 3px;
            transition: width 0.6s ease;
        }

        /* ── Context Chips ─────────────────────────────────── */
        .ctx-chip {
            padding: 0.625rem 0.875rem;
            border-radius: var(--radius-sm);
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-subtle);
            text-align: center;
            transition: all 0.3s ease;
        }
        .ctx-chip:hover {
            border-color: var(--border-accent);
        }
        .ctx-chip-label {
            font-size: 0.55rem;
            font-weight: 700;
            letter-spacing: 0.1rem;
            text-transform: uppercase;
            color: var(--text-dim);
            margin-bottom: 0.25rem;
        }
        .ctx-chip-value {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-light);
            line-height: 1.3;
        }

        /* ── Buttons ───────────────────────────────────────── */
        .btn-glass {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #fff;
            backdrop-filter: blur(8px);
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            transition: all 0.3s ease;
        }
        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        /* ── Pulse Animations ──────────────────────────────── */
        .pulse { animation: pulse-ring 2s infinite; }
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
            100% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
        }
        .status-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
            background: #10b981;
        }

        /* ── Data Detail Row ───────────────────────────────── */
        .data-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .data-row:last-child { border-bottom: none; }
        .data-row-label { font-size: 0.7rem; color: var(--text-dim); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05rem; }
        .data-row-value { font-size: 0.85rem; color: var(--text-light); font-weight: 700; }

        /* ── Weather Badge Row ─────────────────────────────── */
        .weather-stat {
            text-align: center;
            padding: 0.75rem 0;
        }
        .weather-stat-label {
            font-size: 0.55rem;
            font-weight: 700;
            letter-spacing: 0.1rem;
            text-transform: uppercase;
            color: var(--text-dim);
            margin-bottom: 0.25rem;
        }
        .weather-stat-value {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--text-light);
        }

        /* ── Collapse Prompt ───────────────────────────────── */
        .prompt-viewer {
            background: #0f172a;
            color: var(--text-muted);
            padding: 1rem;
            border-radius: var(--radius-sm);
            font-size: 0.7rem;
            max-height: 250px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
            border: 1px solid var(--border-subtle);
        }

        /* ── Responsive tweaks ─────────────────────────────── */
        @media (max-width: 768px) {
            .iot-dashboard-container { padding: 1rem; }
            .metric-value { font-size: 1.75rem; }
            #live-map { height: 250px; }
        }

        .spin { animation: spin-anim 1.2s linear infinite; }
        @keyframes spin-anim { 0% { transform: rotate(0); } 100% { transform: rotate(360deg); } }
    </style>
@endsection

@section('content')
    <div class="iot-dashboard-container">

        {{-- ╔══════════════════════════════════════════════════════════╗
             HEADER
             ╚══════════════════════════════════════════════════════════╝ --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="d-flex align-items-center mb-1">
                    <span class="status-dot pulse"></span>
                    <span style="font-size: 0.65rem; font-weight: 700; color: #10b981; text-transform: uppercase; letter-spacing: 0.15rem;">System Operational</span>
                </div>
                <h1 class="fw-black mb-0" style="font-size: 2rem; letter-spacing: -1.5px;">IOT COMMAND CENTER</h1>
            </div>
            <div class="text-end">
                <h3 class="mb-0 fw-bold" id="current-time" style="font-size: 1.5rem; letter-spacing: -0.5px;">--:--:--</h3>
                <button class="btn btn-sm btn-glass mt-1 px-3" data-bs-toggle="modal" data-bs-target="#settingsModal">
                    <i class="material-icons align-middle me-1" style="font-size: 14px;">settings</i> Config
                </button>
            </div>
        </div>

        {{-- ╔══════════════════════════════════════════════════════════╗
             PRIORITY 1 — CAPE RISK ASSESSMENT (HERO BANNER)
             ╚══════════════════════════════════════════════════════════╝ --}}
        <div class="mb-4">
            <div class="risk-banner risk-pending" id="cape-risk-banner">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: center;">
                        <i class="material-icons" style="font-size: 24px; color: var(--accent-blue);">psychology</i>
                    </div>
                    <div>
                        <div style="font-size: 0.6rem; font-weight: 700; letter-spacing: 0.15rem; text-transform: uppercase; color: var(--text-dim);">CAPE Risk Assessment</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);" id="cape-last-updated">Awaiting first assessment…</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end d-none d-md-block">
                        <div style="font-size: 0.6rem; color: var(--text-dim); font-weight: 600;" id="cape-response-time">—</div>
                    </div>
                    <span id="cape-risk-badge" class="risk-level-badge badge bg-secondary">ANALYZING…</span>
                    <a href="/admin/cape-logs" class="btn btn-sm btn-glass px-3 ms-2 d-none d-md-inline-flex">
                        <i class="material-icons align-middle me-1" style="font-size: 14px;">history</i> Logs
                    </a>
                </div>
            </div>
        </div>

        {{-- CAPE Detail Grid (reasons, actions, prediction + context chips) --}}
        <div class="row g-3 mb-4">
            {{-- Context Chips --}}
            <div class="col-lg-5">
                <div class="glass-card p-3 h-100">
                    <div class="section-header">
                        <div class="section-icon" style="background: rgba(96,165,250,0.1); color: var(--accent-blue);">
                            <i class="material-icons" style="font-size: 16px;">sensors</i>
                        </div>
                        <h5>Context Analysis</h5>
                    </div>
                    <div class="row g-2" id="cape-context-grid">
                        <div class="col-6 col-md-4">
                            <div class="ctx-chip"><div class="ctx-chip-label">Speed</div><div class="ctx-chip-value" id="cape-ctx-speed">—</div></div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="ctx-chip"><div class="ctx-chip-label">Light</div><div class="ctx-chip-value" id="cape-ctx-light">—</div></div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="ctx-chip"><div class="ctx-chip-label">Obstacle</div><div class="ctx-chip-value" id="cape-ctx-obstacle">—</div></div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="ctx-chip"><div class="ctx-chip-label">Flood Risk</div><div class="ctx-chip-value" id="cape-ctx-flood">—</div></div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="ctx-chip"><div class="ctx-chip-label">Weather</div><div class="ctx-chip-value" id="cape-ctx-weather">—</div></div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="ctx-chip"><div class="ctx-chip-label">Proximity</div><div class="ctx-chip-value" id="cape-ctx-proximity">—</div></div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Reasons + Actions + Prediction --}}
            <div class="col-lg-7">
                <div class="glass-card p-3 h-100">
                    <div class="row g-3 h-100">
                        <div class="col-md-4">
                            <div class="section-header">
                                <i class="material-icons" style="font-size: 16px; color: var(--accent-amber);">warning</i>
                                <h5 style="font-size: 0.7rem;">Risk Reasons</h5>
                            </div>
                            <ul class="list-unstyled small mb-0" id="cape-reasons-list" style="color: var(--text-muted);">
                                <li style="color: var(--text-dim);">Awaiting assessment…</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="section-header">
                                <i class="material-icons" style="font-size: 16px; color: var(--accent-emerald);">task_alt</i>
                                <h5 style="font-size: 0.7rem;">Actions</h5>
                            </div>
                            <ol class="small mb-0 ps-3" id="cape-actions-list" style="color: var(--text-muted);">
                                <li style="color: var(--text-dim);">Awaiting assessment…</li>
                            </ol>
                        </div>
                        <div class="col-md-4">
                            <div class="section-header">
                                <i class="material-icons" style="font-size: 16px; color: var(--accent-violet);">trending_up</i>
                                <h5 style="font-size: 0.7rem;">Prediction</h5>
                            </div>
                            <p class="small fst-italic mb-0" id="cape-prediction" style="color: var(--text-muted);">Awaiting assessment…</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ╔══════════════════════════════════════════════════════════╗
             PRIORITY 2 — LIVE SENSOR METRICS + MAP
             ╚══════════════════════════════════════════════════════════╝ --}}
        <div class="row g-3 mb-4">
            {{-- Sensor Cards (Left 4 cols) --}}
            <div class="col-lg-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="glass-card p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="metric-label">Speed</div>
                                <i class="material-icons" style="font-size: 18px; color: var(--accent-blue);">speed</i>
                            </div>
                            <div class="metric-value" id="wind_speed">{{ number_format($latest->speed ?? 0, 1) }}</div>
                            <div class="metric-unit">km/h</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="glass-card p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="metric-label">Temperature</div>
                                <i class="material-icons" style="font-size: 18px; color: var(--accent-rose);">thermostat</i>
                            </div>
                            <div class="metric-value" id="temperature">{{ number_format($latest->temperature ?? 0, 1) }}</div>
                            <div class="metric-unit">°C</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="glass-card p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="metric-label">Light</div>
                                <i class="material-icons" style="font-size: 18px; color: var(--accent-amber);">light_mode</i>
                            </div>
                            <div class="metric-value" style="font-size: 1.75rem;" id="lux">{{ $latest->lux ?? 0 }}</div>
                            <div class="metric-unit">Lux</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="glass-card p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="metric-label">Rain</div>
                                <i class="material-icons" style="font-size: 18px; color: var(--accent-blue);">water</i>
                            </div>
                            <div class="metric-value" style="font-size: 1.75rem;" id="rain_percentage">{{ $latest->rain_percentage ?? 0 }}</div>
                            <div class="metric-unit">%</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="glass-card p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="metric-label">Humidity</div>
                                <i class="material-icons" style="font-size: 18px; color: var(--accent-emerald);">water_drop</i>
                            </div>
                            <div class="metric-value" style="font-size: 1.75rem;" id="humidity">{{ $latest->humidity ?? 0 }}</div>
                            <div class="metric-unit">%</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="glass-card p-3" id="flood-monitor-card" style="cursor: pointer;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="metric-label">Flood Monitor</div>
                                <i class="material-icons" style="font-size: 18px; color: var(--accent-violet);">flood</i>
                            </div>
                            <div class="metric-value" style="font-size: 1.75rem;">Stats</div>
                            <div class="metric-unit">Click to open →</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Live Tracking Map (Right 8 cols) --}}
            <div class="col-lg-8">
                <div class="glass-card p-2 h-100">
                    <div id="live-map"></div>
                </div>
            </div>
        </div>

        {{-- ╔══════════════════════════════════════════════════════════╗
             PRIORITY 3 — PROXIMITY, WEATHER, VIDEO FEED
             ╚══════════════════════════════════════════════════════════╝ --}}
        <div class="row g-3 mb-4">
            {{-- Proximity + Weather --}}
            <div class="col-lg-5">
                {{-- Sensor Proximity --}}
                <div class="glass-card p-3 mb-3">
                    <div class="section-header">
                        <div class="section-icon" style="background: rgba(251,191,36,0.1); color: var(--accent-amber);">
                            <i class="material-icons" style="font-size: 16px;">radar</i>
                        </div>
                        <h5>Proximity Sensors</h5>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="metric-label">Frontal Distance</span>
                            <span class="data-row-value" style="font-size: 0.8rem;" id="val-front">{{ number_format($latest->sf_front_distance ?? 0, 2) }}m</span>
                        </div>
                        <div class="sensor-progress">
                            <div id="bar-front" class="progress-bar" style="width: {{ min(($latest->sf_front_distance ?? 0) * 10, 100) }}%; background: var(--accent-blue);"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="metric-label">Lateral Distance</span>
                            <span class="data-row-value" style="font-size: 0.8rem;" id="val-side">{{ number_format($latest->sf_side_distance ?? 0, 2) }}m</span>
                        </div>
                        <div class="sensor-progress">
                            <div id="bar-side" class="progress-bar" style="width: {{ min(($latest->sf_side_distance ?? 0) * 10, 100) }}%; background: var(--accent-violet);"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="metric-label">Precipitation Factor</span>
                            <span class="data-row-value" style="font-size: 0.8rem;" id="val-rain">{{ $latest->rain_percentage ?? 0 }}%</span>
                        </div>
                        <div class="sensor-progress">
                            <div id="bar-rain" class="progress-bar" style="width: {{ $latest->rain_percentage ?? 0 }}%; background: var(--accent-amber);"></div>
                        </div>
                    </div>
                </div>

                {{-- Weather Intelligence --}}
                <div class="glass-card p-3">
                    <div class="section-header">
                        <div class="section-icon" style="background: rgba(96,165,250,0.1); color: var(--accent-blue);">
                            <i class="material-icons" style="font-size: 16px;">cloud</i>
                        </div>
                        <h5>Weather Intelligence</h5>
                        <span class="section-badge ms-auto">API</span>
                    </div>
                    <div class="row g-0 text-center">
                        <div class="col-4 border-end" style="border-color: var(--border-subtle) !important;">
                            <div class="weather-stat">
                                <div class="weather-stat-label">Feels Like</div>
                                <div class="weather-stat-value" id="val-feels-like">{{ number_format($weather['main']['feels_like'] ?? 0, 1) }}°C</div>
                            </div>
                        </div>
                        <div class="col-4 border-end" style="border-color: var(--border-subtle) !important;">
                            <div class="weather-stat">
                                <div class="weather-stat-label">Visibility</div>
                                <div class="weather-stat-value" id="val-visibility">{{ number_format(($weather['visibility'] ?? 10000) / 1000, 1) }}km</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="weather-stat">
                                <div class="weather-stat-label">Humidity</div>
                                <div class="weather-stat-value" id="val-humidity-api">{{ $weather['main']['humidity'] ?? 0 }}%</div>
                            </div>
                        </div>
                        <div class="col-4 border-end border-top" style="border-color: var(--border-subtle) !important;">
                            <div class="weather-stat">
                                <div class="weather-stat-label">Min / Max</div>
                                <div class="weather-stat-value" id="val-temp-range" style="font-size: 0.95rem;">{{ number_format($weather['main']['temp_min'] ?? 0, 1) }} / {{ number_format($weather['main']['temp_max'] ?? 0, 1) }}°C</div>
                            </div>
                        </div>
                        <div class="col-4 border-end border-top" style="border-color: var(--border-subtle) !important;">
                            <div class="weather-stat">
                                <div class="weather-stat-label">Sunrise</div>
                                <div class="weather-stat-value" id="val-sunrise">{{ isset($weather['sys']['sunrise']) ? date('H:i', $weather['sys']['sunrise']) : '--:--' }}</div>
                            </div>
                        </div>
                        <div class="col-4 border-top" style="border-color: var(--border-subtle) !important;">
                            <div class="weather-stat">
                                <div class="weather-stat-label">Sunset</div>
                                <div class="weather-stat-value" id="val-sunset">{{ isset($weather['sys']['sunset']) ? date('H:i', $weather['sys']['sunset']) : '--:--' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Video Feed --}}
            <div class="col-lg-7">
                <div class="glass-card p-3 h-100">
                    <div class="section-header">
                        <div class="section-icon" style="background: rgba(251,113,133,0.1); color: var(--accent-rose);">
                            <i class="material-icons" style="font-size: 16px;">videocam</i>
                        </div>
                        <h5>Tactical Video Feed</h5>
                        <span class="badge bg-danger ms-auto pulse" style="font-size: 0.6rem;">LIVE</span>
                    </div>
                    <div class="stream-container shadow-lg">
                        <iframe id="stream-iframe" src="{{ $streamUrl }}" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>

        {{-- ╔══════════════════════════════════════════════════════════╗
             PRIORITY 4 — DETECTION + DISASTER REPORTS (COMPACT)
             ╚══════════════════════════════════════════════════════════╝ --}}
        <div class="row g-3 mb-4">
            {{-- Latest Object Detection --}}
            <div class="col-lg-7">
                <div class="glass-card p-3 h-100">
                    <div class="section-header">
                        <div class="section-icon" style="background: rgba(96,165,250,0.1); color: var(--accent-blue);">
                            <i class="material-icons" style="font-size: 16px;">visibility</i>
                        </div>
                        <h5>Latest Object Detection</h5>
                        <span class="section-badge ms-auto">Real-Time</span>
                    </div>

                    @if($latestDetection)
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                @if($latestDetection->image)
                                    <img src="{{ asset('storage/' . $latestDetection->image) }}" class="w-100 rounded-3" alt="Detection" onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'; this.onerror=null;">
                                @else
                                    <div class="rounded-3 d-flex flex-column align-items-center justify-content-center" style="background: #0f172a; height: 180px; border: 1px solid var(--border-subtle);">
                                        <i class="material-icons mb-1" style="font-size: 32px; color: var(--text-dim);">image_not_supported</i>
                                        <span class="small" style="color: var(--text-dim);">No Evidence Image</span>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-5 mt-3 mt-md-0">
                                <div class="data-row">
                                    <span class="data-row-label">Type</span>
                                    <span class="data-row-value" style="color: var(--accent-amber);">{{ $latestDetection->type }}</span>
                                </div>
                                <div class="data-row">
                                    <span class="data-row-label">Distance</span>
                                    <span class="data-row-value" style="color: var(--accent-blue);">{{ number_format($latestDetection->distance, 1) }}m</span>
                                </div>
                                <div class="data-row">
                                    <span class="data-row-label">Size</span>
                                    <span class="data-row-value">{{ $latestDetection->size }}</span>
                                </div>
                                <div class="data-row">
                                    <span class="data-row-label">Location</span>
                                    <span class="data-row-value" id="detection-location" style="font-size: 0.75rem;">{{ number_format($latestDetection->latitude, 4) }}, {{ number_format($latestDetection->longitude, 4) }}</span>
                                </div>
                                <div class="data-row">
                                    <span class="data-row-label">Detected</span>
                                    <span class="data-row-value" style="font-size: 0.75rem; color: var(--text-muted);">{{ $latestDetection->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-2">
                                    <div id="detection-map" class="mini-map"></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4" style="color: var(--text-dim);">
                            <i class="material-icons d-block mb-1" style="font-size: 28px;">sensors_off</i>
                            <span class="small">No detection data available</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Latest Disaster Report --}}
            <div class="col-lg-5">
                <div class="glass-card p-3 h-100">
                    <div class="section-header">
                        <div class="section-icon" style="background: rgba(251,113,133,0.1); color: var(--accent-rose);">
                            <i class="material-icons" style="font-size: 16px;">warning</i>
                        </div>
                        <h5>Disaster Report</h5>
                        <span class="section-badge ms-auto">Risk Assessment</span>
                    </div>

                    @if($latestDisaster)
                        <div class="text-center mb-3">
                            <h4 class="fw-black mb-1" id="disaster-city" style="letter-spacing: -0.5px;">{{ strtoupper($latestDisaster->city) }}</h4>
                            <span class="badge {{ $latestDisaster->risk_level === 'High' ? 'bg-danger' : ($latestDisaster->risk_level === 'Moderate' ? 'bg-warning' : 'bg-info') }} px-3 py-1" id="disaster-risk" style="font-size: 0.75rem;">
                                {{ $latestDisaster->risk_level }} RISK
                            </span>
                        </div>
                        <div id="disaster-map" class="mini-map mb-3"></div>
                        <div class="data-row">
                            <span class="data-row-label">Coordinates</span>
                            <span class="data-row-value" id="disaster-location" style="font-size: 0.75rem;">{{ number_format($latestDisaster->latitude, 4) }}N, {{ number_format($latestDisaster->longitude, 4) }}E</span>
                        </div>
                        <div class="data-row">
                            <span class="data-row-label">Status</span>
                            <span class="data-row-value" style="color: var(--accent-emerald); font-size: 0.8rem;"><span class="status-dot" style="width: 6px; height: 6px;"></span>Monitored</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2" style="border-top: 1px solid var(--border-subtle);">
                            <span style="font-size: 0.65rem; color: var(--text-dim); font-style: italic;">{{ $latestDisaster->created_at->diffForHumans() }}</span>
                            <a href="{{ route('disaster-history.index') }}" class="btn btn-sm btn-glass px-3">Archive</a>
                        </div>
                    @else
                        <div class="text-center py-4" style="color: var(--text-dim);">
                            <i class="material-icons d-block mb-1" style="font-size: 28px; color: var(--accent-emerald);">check_circle_outline</i>
                            <span class="small">No active risk reports</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ╔══════════════════════════════════════════════════════════╗
             PRIORITY 5 — ASK CAPE + PROMPT VIEWER
             ╚══════════════════════════════════════════════════════════╝ --}}
        <div class="row g-3">
            {{-- Ask CAPE Chat --}}
            <div class="col-lg-6">
                <div class="glass-card p-3">
                    <div class="section-header">
                        <div class="section-icon" style="background: rgba(167,139,250,0.1); color: var(--accent-violet);">
                            <i class="material-icons" style="font-size: 16px;">chat</i>
                        </div>
                        <h5>Ask CAPE</h5>
                    </div>
                    <div class="d-flex gap-2">
                        <input type="text" id="cape-chat-input"
                               class="form-control form-control-sm"
                               style="background: #0f172a; border-color: var(--border-subtle); color: var(--text-light); border-radius: var(--radius-sm);"
                               placeholder="Ask about the current risk assessment…"
                               maxlength="500">
                        <button id="cape-chat-send" class="btn btn-sm btn-primary px-3" style="border-radius: var(--radius-sm);">
                            <i class="material-icons align-middle" style="font-size: 16px;">send</i>
                        </button>
                    </div>
                    <div id="cape-chat-response" class="mt-2 small" style="display: none;">
                        <div class="p-2 rounded-2" style="background: rgba(96,165,250,0.06); border: 1px solid rgba(96,165,250,0.12);">
                            <i class="material-icons align-middle me-1" style="font-size: 14px; color: var(--accent-blue);">smart_toy</i>
                            <span id="cape-chat-answer" style="color: var(--text-muted);"></span>
                        </div>
                    </div>
                    <div id="cape-chat-loading" class="mt-2 small" style="display: none; color: var(--text-dim);">
                        <i class="material-icons align-middle me-1 spin" style="font-size: 14px;">hourglass_empty</i>
                        CAPE is thinking…
                    </div>
                </div>
            </div>

            {{-- Prompt Viewer --}}
            <div class="col-lg-6">
                <div class="glass-card p-3">
                    <div class="section-header">
                        <div class="section-icon" style="background: rgba(100,116,139,0.1); color: var(--text-dim);">
                            <i class="material-icons" style="font-size: 16px;">code</i>
                        </div>
                        <h5>Generated Prompt</h5>
                        <span class="section-badge ms-auto">Research Transparency</span>
                    </div>
                    <button class="btn btn-sm btn-glass w-100 text-start mb-2" type="button"
                            data-bs-toggle="collapse" data-bs-target="#cape-prompt-collapse"
                            aria-expanded="false" aria-controls="cape-prompt-collapse">
                        <i class="material-icons align-middle me-1" style="font-size: 14px;">expand_more</i>
                        Toggle Prompt View
                    </button>
                    <div class="collapse" id="cape-prompt-collapse">
                        <pre id="cape-prompt-text" class="prompt-viewer">Awaiting first assessment…</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card border-0" style="background: #1e293b;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-white fw-bold">Dashboard Config</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="metric-label mb-2 d-block">Video Stream URL</label>
                        <div class="input-group">
                            <input type="text" id="stream-url-input" class="form-control" value="{{ $streamUrl }}">
                            <button class="btn btn-primary" id="save-stream-url">Apply</button>
                        </div>
                        <small class="mt-2 d-block" style="color: var(--text-dim);">Supports YouTube embed links, RTSP converters, and static feeds.</small>
                    </div>
                    <div class="mb-0">
                        <label class="metric-label mb-2 d-block">API Status</label>
                        <div class="p-3 rounded" style="background: #0f172a; border: 1px solid var(--border-subtle);">
                            @if(env('OPENWEATHER_API_KEY'))
                                <div class="d-flex align-items-center" style="color: var(--accent-emerald);">
                                    <i class="material-icons me-2" style="font-size: 16px;">check_circle</i> Weather API Connected
                                </div>
                            @else
                                <div class="d-flex align-items-center" style="color: var(--accent-amber);">
                                    <i class="material-icons me-2" style="font-size: 16px;">warning</i> API Key Required
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-glass w-100" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        let map, marker;
        let detectionMap, detectionMarker;
        let disasterMap, disasterMarker;

        function initMap(lat, lng) {
            map = L.map('live-map', { zoomControl: true, attributionControl: false }).setView([lat, lng], 15);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 }).addTo(map);
            const trainIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<div style='background-color:#60a5fa; width:14px; height:14px; border-radius:50%; border:3px solid #fff; box-shadow: 0 0 10px #60a5fa;' class='pulse'></div>",
                iconSize: [18, 18], iconAnchor: [9, 9]
            });
            marker = L.marker([lat, lng], { icon: trainIcon }).addTo(map);
        }

        function updateDashboard() {
            $.get('/api/iot/latest', function(response) {
                const data = response.latest;
                const weather = response.weather;
                if (!data) return;

                $('#wind_speed').text(parseFloat(data.speed).toFixed(1));
                $('#temperature').text(parseFloat(data.temperature).toFixed(1));
                $('#humidity').text(data.humidity);
                $('#lux').text(data.lux);
                $('#rain_percentage').text(data.rain_percentage);

                // Proximity bars
                $('#val-front').text(parseFloat(data.sf_front_distance).toFixed(2) + 'm');
                $('#bar-front').css('width', Math.min(data.sf_front_distance * 10, 100) + '%');
                $('#val-side').text(parseFloat(data.sf_side_distance).toFixed(2) + 'm');
                $('#bar-side').css('width', Math.min(data.sf_side_distance * 10, 100) + '%');
                $('#val-rain').text(data.rain_percentage + '%');
                $('#bar-rain').css('width', data.rain_percentage + '%');

                if (weather) {
                    $('#val-feels-like').text(parseFloat(weather.main.feels_like).toFixed(1) + '°C');
                    $('#val-temp-range').text(parseFloat(weather.main.temp_min).toFixed(1) + ' / ' + parseFloat(weather.main.temp_max).toFixed(1) + '°C');
                    $('#val-humidity-api').text(weather.main.humidity + '%');
                    $('#val-visibility').text(parseFloat(weather.visibility / 1000).toFixed(1) + 'km');
                    if (weather.sys.sunrise) $('#val-sunrise').text(new Date(weather.sys.sunrise * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
                    if (weather.sys.sunset) $('#val-sunset').text(new Date(weather.sys.sunset * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
                }

                if (response.latestDetection) {
                    const det = response.latestDetection;
                    $('#detection-location').text(parseFloat(det.latitude).toFixed(4) + ', ' + parseFloat(det.longitude).toFixed(4));
                    if (detectionMap && det.latitude) {
                        detectionMarker.setLatLng([det.latitude, det.longitude]);
                        detectionMap.panTo([det.latitude, det.longitude]);
                    }
                }

                if (response.latestDisaster) {
                    const dis = response.latestDisaster;
                    $('#disaster-city').text(dis.city.toUpperCase());
                    $('#disaster-risk').text(dis.risk_level + ' RISK');
                    $('#disaster-location').text(parseFloat(dis.latitude).toFixed(4) + 'N, ' + parseFloat(dis.longitude).toFixed(4) + 'E');
                    if (disasterMap && dis.latitude) {
                        disasterMarker.setLatLng([dis.latitude, dis.longitude]);
                        disasterMap.panTo([dis.latitude, dis.longitude]);
                    }
                }

                if (data.latitude && data.longitude) {
                    marker.setLatLng([data.latitude, data.longitude]);
                    map.panTo([data.latitude, data.longitude]);
                }
            });
        }

        $(document).ready(function() {
            $('#flood-monitor-card').on('click', function() { window.location.href = 'https://rivernet.lk/'; });

            initMap({{ $latest->latitude ?? 6.9271 }}, {{ $latest->longitude ?? 79.8612 }});

            @if($latestDetection)
                detectionMap = L.map('detection-map', { zoomControl: false, attributionControl: false }).setView([{{ $latestDetection->latitude }}, {{ $latestDetection->longitude }}], 14);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(detectionMap);
                detectionMarker = L.marker([{{ $latestDetection->latitude }}, {{ $latestDetection->longitude }}]).addTo(detectionMap);
            @endif

            @if($latestDisaster)
                disasterMap = L.map('disaster-map', { zoomControl: false, attributionControl: false }).setView([{{ $latestDisaster->latitude }}, {{ $latestDisaster->longitude }}], 12);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(disasterMap);
                disasterMarker = L.marker([{{ $latestDisaster->latitude }}, {{ $latestDisaster->longitude }}]).addTo(disasterMap);
            @endif

            setInterval(() => { $('#current-time').text(new Date().toLocaleTimeString()); }, 1000);
            setInterval(updateDashboard, 3000);

            $('#save-stream-url').on('click', function() {
                const newUrl = $('#stream-url-input').val();
                $(this).prop('disabled', true).text('Applying...');
                $.post('/api/settings/update', { key: 'iot_stream_url', value: newUrl, _token: '{{ csrf_token() }}' }, function(res) {
                    if (res.success) { $('#stream-iframe').attr('src', newUrl); $('#settingsModal').modal('hide'); }
                    $('#save-stream-url').prop('disabled', false).text('Apply');
                });
            });
        });

        // =============================================================
        // CAPE POLLING — every 20s
        // =============================================================
        function updateCapePanel() {
            fetch('/api/cape/assess')
                .then(r => r.json())
                .then(data => {
                    if (data.error) { console.warn('CAPE:', data.error); return; }

                    // Update risk banner
                    const banner = document.getElementById('cape-risk-banner');
                    if (banner) {
                        banner.className = 'risk-banner';
                        const bannerClasses = { Low: 'risk-low', Medium: 'risk-medium', High: 'risk-high' };
                        banner.classList.add(bannerClasses[data.risk_level] || 'risk-pending');
                    }

                    // Update risk badge
                    const badge = document.getElementById('cape-risk-badge');
                    if (badge) {
                        badge.textContent = data.risk_level;
                        badge.className = 'risk-level-badge badge';
                        const riskColors = { Low: 'bg-success', Medium: 'bg-warning', High: 'bg-danger' };
                        badge.classList.add(riskColors[data.risk_level] || 'bg-secondary');
                    }

                    // Response time
                    const rtEl = document.getElementById('cape-response-time');
                    if (rtEl) rtEl.textContent = data.response_time_ms + 'ms · ' + data.source;

                    // Context chips
                    const ctxMap = {
                        'cape-ctx-speed': data.context?.speed_status,
                        'cape-ctx-light': data.context?.light_condition,
                        'cape-ctx-obstacle': data.context?.obstacle || 'No obstacle',
                        'cape-ctx-flood': data.context?.flood_context || 'No flood risk',
                        'cape-ctx-weather': data.context?.weather_context || 'N/A',
                        'cape-ctx-proximity': data.context?.proximity_status
                    };
                    for (const [id, val] of Object.entries(ctxMap)) {
                        const el = document.getElementById(id);
                        if (el) el.textContent = val || '—';
                    }

                    // Reasons
                    const reasonsList = document.getElementById('cape-reasons-list');
                    if (reasonsList && data.reasons) {
                        reasonsList.innerHTML = data.reasons.map(
                            r => '<li class="mb-1"><i class="material-icons align-middle me-1" style="font-size:13px;color:var(--accent-amber);">warning</i>' + r + '</li>'
                        ).join('');
                    }

                    // Actions
                    const actionsList = document.getElementById('cape-actions-list');
                    if (actionsList && data.actions) {
                        actionsList.innerHTML = data.actions.map(a => '<li class="mb-1">' + a + '</li>').join('');
                    }

                    // Prediction
                    const predEl = document.getElementById('cape-prediction');
                    if (predEl) predEl.textContent = data.prediction || 'No prediction available.';

                    // Prompt
                    const promptEl = document.getElementById('cape-prompt-text');
                    if (promptEl) promptEl.textContent = data.prompt || '';

                    // Timestamp
                    const tsEl = document.getElementById('cape-last-updated');
                    if (tsEl) {
                        const ts = data.assessed_at ? new Date(data.assessed_at).toLocaleTimeString() : new Date().toLocaleTimeString();
                        tsEl.textContent = 'Last assessed: ' + ts;
                    }
                })
                .catch(err => console.error('CAPE poll error:', err));
        }

        // CAPE chat
        $(document).ready(function() {
            updateCapePanel();
            setInterval(updateCapePanel, {{ config('cape.poll_interval', 20000) }});

            $('#cape-chat-send').on('click', function() {
                const question = $('#cape-chat-input').val().trim();
                if (!question) return;
                $('#cape-chat-loading').show();
                $('#cape-chat-response').hide();
                fetch('/api/cape/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ question })
                })
                .then(r => r.json())
                .then(data => { $('#cape-chat-loading').hide(); $('#cape-chat-answer').text(data.answer || 'No response received.'); $('#cape-chat-response').show(); })
                .catch(() => { $('#cape-chat-loading').hide(); $('#cape-chat-answer').text('Error communicating with CAPE.'); $('#cape-chat-response').show(); });
            });

            $('#cape-chat-input').on('keypress', function(e) { if (e.which === 13) { e.preventDefault(); $('#cape-chat-send').trigger('click'); } });
        });
    </script>
@endsection