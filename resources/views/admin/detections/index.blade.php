@extends('layouts.app')

@section('title', 'Detection History - RailFlow')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 400px;
            width: 100%;
            border-radius: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold mb-1">
                                    <i class="material-icons align-middle me-2">visibility</i>Detection History
                                </h4>
                                <p class="text-muted mb-0 text-sm">Review historical object detection logs and their
                                    locations</p>
                            </div>
                        </div>

                        <!-- Filters -->
                        <form action="{{ route('detection-history.index') }}" method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label text-xs fw-bold">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control form-control-sm"
                                    value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label text-xs fw-bold">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control form-control-sm"
                                    value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="type" class="form-label text-xs fw-bold">Object Type</label>
                                <input type="text" name="type" id="type" class="form-control form-control-sm"
                                    placeholder="Search by type..." value="{{ request('type') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="material-icons text-white align-middle me-1">filter_list</i>Filter
                                </button>
                                <a href="{{ route('detection-history.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="material-icons align-middle me-1">refresh</i>Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Image</th>
                                        <th class="text-uppercase text-xs font-weight-bolder opacity-7 ps-2">Type</th>
                                        <th class="text-uppercase text-xs font-weight-bolder opacity-7 ps-2">Distance</th>
                                        <th class="text-uppercase text-xs font-weight-bolder opacity-7 ps-2">Size</th>
                                        <th class="text-uppercase text-xs font-weight-bolder opacity-7 ps-2">Coordinates
                                        </th>
                                        <th class="text-center text-uppercase text-xs font-weight-bolder opacity-7">Date
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($history as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <img style="width : 100px;" src="{{ asset('storage/' . $item->image) }}"
                                                        class="avatar avatar-sm me-3 border-radius-lg cursor-pointer detection-image"
                                                        alt="detection" data-bs-toggle="modal" data-bs-target="#imageModal"
                                                        data-src="{{ asset('storage/' . $item->image) }}">
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $item->type }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $item->distance }} m</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $item->size }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">
                                                    @if($item->latitude && $item->longitude)
                                                        <button type="button"
                                                            class="btn btn-link p-0 text-xs font-weight-bold view-map-btn"
                                                            data-lat="{{ $item->latitude }}" data-lng="{{ $item->longitude }}"
                                                            data-type="{{ $item->type }}" data-bs-toggle="modal"
                                                            data-bs-target="#mapModal">
                                                            <i class="material-icons text-xs me-1">location_on</i>
                                                            {{ $item->latitude }}, {{ $item->longitude }}
                                                        </button>
                                                    @else
                                                        <span class="text-secondary">N/A</span>
                                                    @endif
                                                </p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span
                                                    class="text-secondary text-xs font-weight-bold">{{ $item->created_at->format('Y-m-d H:i') }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <p class="text-sm">No detection history found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="px-3 py-2">
                            {{ $history->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Detection Image Preview</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" id="modalImage" class="img-fluid border-radius-lg" alt="Detection Preview">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Modal -->
    <div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mapModalLabel">Detection Location Map</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $(document).ready(function () {
            // Image Modal Logic
            const imageModal = document.getElementById('imageModal');
            if (imageModal) {
                imageModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const src = button.getAttribute('data-src');
                    const modalImage = document.getElementById('modalImage');
                    if (modalImage) {
                        modalImage.src = src;
                    }
                });
            }

            // Map Modal Logic
            let map;
            let marker;

            $('#mapModal').on('shown.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const lat = parseFloat(button.data('lat'));
                const lng = parseFloat(button.data('lng'));
                const type = button.data('type');

                if (!map) {
                    map = L.map('map').setView([lat, lng], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);
                } else {
                    map.setView([lat, lng], 13);
                }

                if (marker) {
                    marker.setLatLng([lat, lng]).bindPopup(`<b>${type} Detected</b>`).openPopup();
                } else {
                    marker = L.marker([lat, lng]).addTo(map).bindPopup(`<b>${type} Detected</b>`).openPopup();
                }

                // Invalide size to fix map loading issues in modal
                setTimeout(function () {
                    map.invalidateSize();
                }, 100);
            });
        });
    </script>
@endsection