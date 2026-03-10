@extends('layouts.app')
@section('title', 'IoT History - RailFlow')
@section('styles')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
        }
    </style>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="material-icons align-middle me-2">history</i>IoT History Records
                    </h4>
                    <p class="text-muted mb-0">Monitor and review all historical sensor data from your trains</p>
                </div>
            </div>
        </div>

        <div class="card-body shadow-sm">
            <div class="px-3 mb-4">
                <form action="{{ route('admin.history') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label text-xs">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="form-control border px-2">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-xs">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control border px-2">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mb-0 me-2 btn-sm">Filter</button>
                        <a href="{{ route('admin.history') }}" class="btn btn-outline-secondary mb-0 btn-sm">Clear</a>
                    </div>
                </form>
            </div>

            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Time
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                Distance (F/S/T)</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                Temp/Hum</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                Lux/Rain</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                Location/Speed</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $data)
                            <tr>
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $data->created_at->format('Y-m-d') }}</h6>
                                            <p class="text-xs text-secondary mb-0">
                                                {{ $data->created_at->format('H:i:s') }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">F:
                                        {{ $data->sf_front_distance ?? '0.00' }}m
                                    </p>
                                    <p class="text-xs text-secondary mb-0">S:
                                        {{ $data->sf_side_distance ?? '0.00' }}m | T:
                                        {{ $data->t_front_distance ?? '0.00' }}m
                                    </p>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $data->temperature ?? '0.0' }}°C</p>
                                    <p class="text-xs text-secondary mb-0">{{ $data->humidity ?? '0' }}%</p>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $data->lux ?? '0' }} lux</p>
                                    <p class="text-xs text-secondary mb-0">{{ $data->rain_percentage ?? '0' }}% rain
                                    </p>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $data->latitude ?? '0.0' }},
                                        {{ $data->longitude ?? '0.0' }}
                                    </p>
                                    <p class="text-xs text-secondary mb-0">{{ $data->speed ?? '0.0' }} km/h</p>
                                </td>
                                <td class="text-end pe-3">
                                    @if($data->latitude && $data->longitude)
                                        <button class="btn btn-link text-primary p-0 view-map"
                                            data-lat="{{ $data->latitude }}" data-lng="{{ $data->longitude }}"
                                            title="View on Map">
                                            <i class="material-icons" style="font-size: 1.2rem;">map</i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No historical data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-3">
                {{ $history->links() }}
            </div>
        </div>
    </div>

    <!-- Map Modal -->
    <div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mapModalLabel">Train Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="map"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

        $(document).ready(function () {
            $('.view-map').on('click', function () {
                const lat = $(this).data('lat');
                const lng = $(this).data('lng');

                $('#mapModal').modal('show');

                setTimeout(() => {
                    if (!map) {
                        map = L.map('map').setView([lat, lng], 15);
                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                        }).addTo(map);
                        marker = L.marker([lat, lng]).addTo(map);
                    } else {
                        map.setView([lat, lng], 15);
                        marker.setLatLng([lat, lng]);
                    }

                    // Essential for Leaflet markers and tiles to render correctly in modals
                    map.invalidateSize();
                }, 300);
            });
        });
    </script>
@endsection
