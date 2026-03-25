{{-- resources/views/cape/logs.blade.php — CAPE Research Logs (Admin) --}}
@extends('layouts.app')

@section('title', 'CAPE Risk Assessment Logs - RailFlow')

@section('styles')
    <style>
        .cape-logs-container {
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
        }

        .metric-label {
            text-transform: uppercase;
            letter-spacing: 0.15rem;
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 700;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .badge-risk-Low { background-color: #10b981 !important; color: #fff; }
        .badge-risk-Medium { background-color: #f59e0b !important; color: #000; }
        .badge-risk-High { background-color: #ef4444 !important; color: #fff; }

        .badge-source-llm { background-color: #8b5cf6 !important; color: #fff; }
        .badge-source-fallback { background-color: #6b7280 !important; color: #fff; }

        .cape-detail-row {
            background: rgba(15, 23, 42, 0.6);
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-top: 0.5rem;
        }

        .cape-detail-row pre {
            background: #0f172a;
            color: #94a3b8;
            padding: 1rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            max-height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }

        table.dataTable {
            color: #e2e8f0 !important;
        }

        table.dataTable thead th {
            color: #94a3b8 !important;
            border-bottom-color: rgba(255,255,255,0.1) !important;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.1rem;
        }

        table.dataTable tbody tr {
            background: transparent !important;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        table.dataTable tbody tr:hover {
            background: rgba(30, 41, 59, 0.5) !important;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #94a3b8 !important;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            background: #1e293b !important;
            color: #e2e8f0 !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            border-radius: 0.5rem !important;
            padding: 0.35rem 0.75rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #94a3b8 !important;
            background: transparent !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            border-radius: 0.35rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: rgba(96, 165, 250, 0.2) !important;
            color: #60a5fa !important;
            border-color: rgba(96, 165, 250, 0.3) !important;
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
    </style>
@endsection

@section('content')
    <div class="cape-logs-container">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="fw-black mb-1 display-6" style="letter-spacing: -2px;">CAPE RESEARCH LOGS</h1>
                <p class="text-slate-400 mb-0">Context-Aware Prompt Engine — Assessment History & Analytics</p>
            </div>
            <div>
                <a href="{{ route('admin.iot-dashboard') }}" class="btn btn-glass">
                    <i class="material-icons align-middle me-1">arrow_back</i> Back to Dashboard
                </a>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="glass-card p-4 text-center">
                    <div class="metric-label mb-2">Total Assessments</div>
                    <div class="stat-value text-white">{{ number_format($totalAssessments) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-4 text-center">
                    <div class="metric-label mb-2">LLM Powered</div>
                    <div class="stat-value" style="color: #8b5cf6;">{{ $llmPercentage }}%</div>
                    <div class="text-slate-500 text-xs">{{ number_format($llmCount) }} assessments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-4 text-center">
                    <div class="metric-label mb-2">Fallback Used</div>
                    <div class="stat-value" style="color: #6b7280;">{{ $fallbackPercentage }}%</div>
                    <div class="text-slate-500 text-xs">{{ number_format($fallbackCount) }} assessments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-4 text-center">
                    <div class="metric-label mb-2">Most Common Risk</div>
                    @if($mostCommonRisk)
                        <div class="stat-value">
                            <span class="badge badge-risk-{{ $mostCommonRisk->risk_level }} fs-5 px-3 py-2">
                                {{ $mostCommonRisk->risk_level }}
                            </span>
                        </div>
                        <div class="text-slate-500 text-xs mt-1">{{ $mostCommonRisk->count }} occurrences</div>
                    @else
                        <div class="stat-value text-slate-500">N/A</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="glass-card p-4">
            <table id="cape-logs-table" class="table table-borderless w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date/Time</th>
                        <th>Risk Level</th>
                        <th>Reasons</th>
                        <th>Source</th>
                        <th>Response Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td class="text-slate-400">#{{ $log->id }}</td>
                            <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                            <td>
                                <span class="badge badge-risk-{{ $log->risk_level }} px-3 py-1">
                                    {{ $log->risk_level }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary px-2 py-1">
                                    {{ is_array($log->reasons_json) ? count($log->reasons_json) : 0 }} reasons
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-source-{{ $log->source }} px-2 py-1">
                                    {{ strtoupper($log->source) }}
                                </span>
                            </td>
                            <td class="text-slate-400">{{ $log->response_time_ms }}ms</td>
                            <td>
                                <button class="btn btn-sm btn-glass cape-detail-toggle"
                                        data-log-id="{{ $log->id }}">
                                    <i class="material-icons align-middle" style="font-size: 18px;">visibility</i>
                                    View Details
                                </button>
                            </td>
                        </tr>
                        <tr class="cape-detail-container" id="cape-detail-{{ $log->id }}" style="display: none;">
                            <td colspan="7">
                                <div class="cape-detail-row">
                                    <div class="row g-3">
                                        {{-- Context Labels --}}
                                        <div class="col-md-4">
                                            <h6 class="text-white fw-bold mb-2">
                                                <i class="material-icons align-middle me-1" style="font-size: 16px;">label</i>
                                                Context Labels
                                            </h6>
                                            <ul class="list-unstyled text-slate-300 small">
                                                @if(is_array($log->context_json))
                                                    @foreach($log->context_json as $key => $value)
                                                        @if($value)
                                                            <li class="mb-1">
                                                                <span class="text-slate-500">{{ $key }}:</span>
                                                                {{ $value }}
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </ul>
                                        </div>

                                        {{-- Reasons & Actions --}}
                                        <div class="col-md-4">
                                            <h6 class="text-white fw-bold mb-2">
                                                <i class="material-icons align-middle me-1" style="font-size: 16px;">warning</i>
                                                Reasons
                                            </h6>
                                            <ul class="text-slate-300 small">
                                                @if(is_array($log->reasons_json))
                                                    @foreach($log->reasons_json as $reason)
                                                        <li class="mb-1">{{ $reason }}</li>
                                                    @endforeach
                                                @endif
                                            </ul>

                                            <h6 class="text-white fw-bold mb-2 mt-3">
                                                <i class="material-icons align-middle me-1" style="font-size: 16px;">task</i>
                                                Recommended Actions
                                            </h6>
                                            <ol class="text-slate-300 small">
                                                @if(is_array($log->actions_json))
                                                    @foreach($log->actions_json as $action)
                                                        <li class="mb-1">{{ $action }}</li>
                                                    @endforeach
                                                @endif
                                            </ol>

                                            @if($log->prediction)
                                                <h6 class="text-white fw-bold mb-2 mt-3">
                                                    <i class="material-icons align-middle me-1" style="font-size: 16px;">trending_up</i>
                                                    Prediction
                                                </h6>
                                                <p class="text-slate-300 small fst-italic">{{ $log->prediction }}</p>
                                            @endif
                                        </div>

                                        {{-- Full Prompt --}}
                                        <div class="col-md-4">
                                            <h6 class="text-white fw-bold mb-2">
                                                <i class="material-icons align-middle me-1" style="font-size: 16px;">code</i>
                                                Generated Prompt
                                            </h6>
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
            <div class="d-flex justify-content-center mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Toggle detail rows
            $(document).on('click', '.cape-detail-toggle', function() {
                const logId = $(this).data('log-id');
                const detailRow = $('#cape-detail-' + logId);

                if (detailRow.is(':visible')) {
                    detailRow.slideUp(200);
                    $(this).html('<i class="material-icons align-middle" style="font-size: 18px;">visibility</i> View Details');
                } else {
                    detailRow.slideDown(200);
                    $(this).html('<i class="material-icons align-middle" style="font-size: 18px;">visibility_off</i> Hide Details');
                }
            });
        });
    </script>
@endsection
