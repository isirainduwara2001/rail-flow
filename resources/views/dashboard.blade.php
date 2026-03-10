@extends('layouts.app')

@section('title', 'Dashboard - RailFlow')

@section('content')
    <!-- Page Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-1">Dashboard Overview</h4>
            <p class="text-muted mb-0">
                @if($is_admin)
                    Strategic monitoring and system-wide performance metrics.
                @else
                    Your personal travel statistics and activity history.
                @endif
            </p>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark border p-2">
                <i class="material-icons align-middle fs-6">calendar_today</i> {{ now()->format('M d, Y') }}
            </span>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <!-- Today's Revenue / Spent -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card kpi-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2 fw-5">{{ $is_admin ? "Today's Revenue" : "Total Spent" }}</h6>
                            <h3 class="mb-2 fw-bold">LKR {{ number_format($total_revenue ?? 0, 0) }}</h3>
                            <small class="text-success fw-5">
                                <i class="fas fa-arrow-up"></i> +{{ $is_admin ? '85%' : '0%' }}
                            </small>
                        </div>
                        <div class="kpi-icon bg-success bg-gradient shadow-success">
                            <i class="material-icons">monetization_on</i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($is_admin)
            <!-- Total Users (Admin Only) -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card kpi-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-2 fw-5">System Users</h6>
                                <h3 class="mb-2 fw-bold">{{ $total_users ?? 0 }}</h3>
                                <small class="text-success fw-5">
                                    <i class="fas fa-arrow-up"></i> +3%
                                </small>
                            </div>
                            <div class="kpi-icon bg-info bg-gradient shadow-info">
                                <i class="material-icons">people</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Trains -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card kpi-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-2 fw-5">Active Trains</h6>
                                <h3 class="mb-2 fw-bold">{{ $total_trains ?? 0 }}</h3>
                                <small class="text-slate-400 fw-5">
                                    Operational
                                </small>
                            </div>
                            <div class="kpi-icon bg-warning bg-gradient shadow-warning">
                                <i class="material-icons">train</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Confirmed Bookings (User) -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card kpi-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-2 fw-5">Confirmed Trips</h6>
                                <h3 class="mb-2 fw-bold">{{ $confirmed_bookings ?? 0 }}</h3>
                                <small class="text-info fw-5">
                                    <i class="fas fa-check"></i> Validated
                                </small>
                            </div>
                            <div class="kpi-icon bg-info bg-gradient shadow-info">
                                <i class="material-icons">check_circle</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Activity (User) -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card kpi-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-muted mb-2 fw-5">Total Interaction</h6>
                                <h3 class="mb-2 fw-bold">{{ $total_bookings ?? 0 }}</h3>
                                <small class="text-slate-400 fw-5">
                                    Lifetime
                                </small>
                            </div>
                            <div class="kpi-icon bg-warning bg-gradient shadow-warning">
                                <i class="material-icons">history</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Total Bookings / Reservations -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card kpi-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2 fw-5">{{ $is_admin ? "Total Bookings" : "My Reservations" }}</h6>
                            <h3 class="mb-2 fw-bold">{{ $total_bookings ?? 0 }}</h3>
                            <small class="text-success fw-5">
                                <i class="fas fa-arrow-up"></i> +5%
                            </small>
                        </div>
                        <div class="kpi-icon bg-primary bg-gradient shadow-primary">
                            <i class="material-icons">receipt</i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Trends -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Booking Volume Trends (7 Days)</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border" type="button">
                                <i class="material-icons fs-6 align-middle me-1">download</i> Export
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div style="height: 350px;">
                        <canvas id="bookingTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent History -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Recent Activity Log</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted">
                                    <th class="ps-4 border-0">Reference</th>
                                    <th class="border-0">Details</th>
                                    <th class="border-0 text-center">Amount</th>
                                    <th class="border-0 text-center">Status</th>
                                    <th class="pe-4 border-0 text-end">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_bookings as $booking)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark">{{ $booking->booking_reference }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="small fw-bold">{{ $booking->schedule->train->name }}</span>
                                                <span class="text-muted text-xs">{{ $booking->schedule->from }} →
                                                    {{ $booking->schedule->to }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">LKR {{ number_format($booking->price, 0) }}</td>
                                        <td class="text-center">
                                            <span
                                                class="badge {{ $booking->status === 'confirmed' ? 'bg-success-soft text-success' : 'bg-warning-soft text-warning' }} rounded-pill px-3">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end text-muted small">
                                            {{ $booking->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="material-icons fs-1 d-block mb-3">history_toggle_off</i>
                                            No recent transactions found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 py-3 text-center">
                    <a href="{{ $is_admin ? route('admin.bookings') : route('booking.my-bookings') }}"
                        class="btn btn-sm btn-link text-primary text-decoration-none">
                        View Full History <i class="material-icons align-middle fs-6">chevron_right</i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($is_admin)
                <!-- IoT & Safety Sidebar (Admin) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <h6 class="fw-bold mb-0">Live Safety Uplink</h6>
                    </div>
                    <div class="card-body">
                        @if($latest_iot)
                            <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                                <div class="bg-primary-soft p-2 rounded me-3 text-primary">
                                    <i class="material-icons">thermostat</i>
                                </div>
                                <div>
                                    <div class="text-xs text-muted text-uppercase">Ambient Temp</div>
                                    <div class="fw-bold">{{ number_format($latest_iot->temperature, 1) }}°C</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="bg-info-soft p-2 rounded me-3 text-info">
                                    <i class="material-icons">water_drop</i>
                                </div>
                                <div>
                                    <div class="text-xs text-muted text-uppercase">Humidity Level</div>
                                    <div class="fw-bold">{{ $latest_iot->humidity }}%</div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted small">
                                <i class="material-icons fs-2 mb-2 d-block text-warning">sensors_off</i>
                                No active IoT telemetry
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                        <a href="{{ route('admin.iot-dashboard') }}" class="btn btn-dark w-100 btn-sm rounded-pill shadow-sm">
                            Open Tactical Command
                        </a>
                    </div>
                </div>
            @else
                <!-- Information Card (Regular User) -->
                <div class="card bg-primary bg-gradient border-0 shadow-sm text-white mb-4">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="material-icons display-4 opacity-50">directions_train</i>
                        </div>
                        <h5 class="fw-bold mb-2">Book Your Next Trip</h5>
                        <p class="small opacity-75 mb-4">Explore our latest schedules and book seats with ease.</p>
                        <a href="{{ route('booking.search') }}"
                            class="btn btn-white btn-sm rounded-pill px-4 text-primary fw-bold">
                            Search Trains
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('bookingTrendChart').getContext('2d');

            // Create Gradient
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(34, 197, 94, 0.4)');
            gradient.addColorStop(1, 'rgba(34, 197, 94, 0.05)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chart_labels) !!},
                    datasets: [{
                        label: '{{ $is_admin ? "Global Bookings" : "My Bookings" }}',
                        data: {!! json_encode($chart_data) !!},
                        borderColor: '#22c55e',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointBackgroundColor: '#22c55e',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 3,
                        pointHoverRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    return context.parsed.y + ' Bookings';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                            ticks: { precision: 0 }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
@endsection