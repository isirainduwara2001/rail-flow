{{-- resources/views/cape/logs.blade.php --}}
@extends('layouts.app')

@section('title', 'CAPE Risk Assessment Logs - RailFlow')

@section('styles')
<style>
    body {
        background: #020617;
    }

    .cape-logs-container {
        color: #f8fafc;
        border-radius: 1.5rem;
        padding: 2rem;
        font-family: 'Inter', sans-serif;
    }

    /* Glass Card */
    .glass-card {
        background: rgba(30, 41, 59, 0.45);
        backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 1.25rem;
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-3px);
        border-color: rgba(96, 165, 250, 0.3);
    }

    /* Stats */
    .metric-label {
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.15rem;
        color: #64748b;
        font-weight: 700;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 900;
    }

    /* Badges */
    .badge-risk-Low { background: #10b981; }
    .badge-risk-Medium { background: #f59e0b; color: #000; }
    .badge-risk-High { background: #ef4444; }

    .badge-source-llm { background: #8b5cf6; }
    .badge-source-fallback { background: #64748b; }

    /* Table */
    table {
        color: #e2e8f0;
    }

    thead th {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #94a3b8;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    tbody tr {
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: 0.2s;
    }

    tbody tr:hover {
        background: rgba(30, 41, 59, 0.5);
    }

    /* Detail Panel */
    .cape-detail-row {
        background: rgba(15, 23, 42, 0.7);
        border-radius: 1rem;
        padding: 1.5rem;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(10px);}
        to {opacity: 1; transform: translateY(0);}
    }

    pre {
        background: #020617;
        padding: 1rem;
        border-radius: 0.5rem;
        font-size: 0.8rem;
        max-height: 260px;
        overflow-y: auto;
        color: #94a3b8;
    }

    /* Buttons */
    .btn-glass {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        color: #fff;
        border-radius: 0.6rem;
        transition: 0.2s;
    }

    .btn-glass:hover {
        background: rgba(96,165,250,0.2);
        border-color: rgba(96,165,250,0.4);
    }

    /* Pagination FIXED */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 6px;
        margin-top: 20px;
    }

    .pagination .page-link {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        color: #94a3b8;
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        border-radius: 0.4rem;
        transition: all 0.2s;
    }

    .pagination .page-link:hover {
        background: rgba(96,165,250,0.2);
        color: #60a5fa;
    }

    .pagination .active .page-link {
        background: rgba(96,165,250,0.25);
        color: #60a5fa;
        border-color: rgba(96,165,250,0.4);
    }

    .pagination .disabled .page-link {
        opacity: 0.3;
    }

    /* Mobile */
    @media(max-width:768px){
        .stat-value { font-size: 1.5rem; }
        .cape-logs-container { padding: 1rem; }
    }
</style>
@endsection

@section('content')
<div class="cape-logs-container">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold">CAPE RESEARCH LOGS</h1>
            <small class="text-secondary">AI Risk Analytics Dashboard</small>
        </div>
        <a href="{{ route('admin.iot-dashboard') }}" class="btn btn-glass">
            ← Back
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="glass-card p-3 text-center">
                <div class="metric-label">Total</div>
                <div class="stat-value">{{ number_format($totalAssessments) }}</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="glass-card p-3 text-center">
                <div class="metric-label">LLM</div>
                <div class="stat-value text-purple-400">{{ $llmPercentage }}%</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="glass-card p-3 text-center">
                <div class="metric-label">Fallback</div>
                <div class="stat-value text-gray-400">{{ $fallbackPercentage }}%</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="glass-card p-3 text-center">
                <div class="metric-label">Top Risk</div>
                @if($mostCommonRisk)
                    <span class="badge badge-risk-{{ $mostCommonRisk->risk_level }} px-3 py-2">
                        {{ $mostCommonRisk->risk_level }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="glass-card p-3">
        <table class="table table-borderless">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Risk</th>
                    <th>Reasons</th>
                    <th>Source</th>
                    <th>Time</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>#{{ $log->id }}</td>
                    <td>{{ $log->created_at->format('M d H:i') }}</td>

                    <td>
                        <span class="badge badge-risk-{{ $log->risk_level }}">
                            {{ $log->risk_level }}
                        </span>
                    </td>

                    <td>{{ is_array($log->reasons_json) ? count($log->reasons_json) : 0 }}</td>

                    <td>
                        <span class="badge badge-source-{{ $log->source }}">
                            {{ strtoupper($log->source) }}
                        </span>
                    </td>

                    <td>{{ $log->response_time_ms }}ms</td>

                    <td>
                        <button class="btn btn-sm btn-glass toggle-btn" data-id="{{ $log->id }}">
                            View
                        </button>
                    </td>
                </tr>

                <tr id="detail-{{ $log->id }}" style="display:none;">
                    <td colspan="7">
                        <div class="cape-detail-row">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Reasons</h6>
                                    <ul>
                                        @foreach($log->reasons_json ?? [] as $r)
                                            <li>{{ $r }}</li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="col-md-6">
                                    <h6>Prompt</h6>
                                    <pre>{{ $log->prompt_text }}</pre>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        <div>
            {{ $logs->links('vendor.pagination.dark') }}
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    $(document).on('click','.toggle-btn',function(){
        let id = $(this).data('id');
        $('#detail-'+id).slideToggle(200);
    });
</script>
@endsection