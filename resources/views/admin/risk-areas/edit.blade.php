@extends('layouts.app')

@section('title', 'Edit Risk Area - RailFlow')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 400px;
            width: 100%;
            border-radius: 0.5rem;
            z-index: 1;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">
        
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card shadow-lg border-0">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold mb-1">
                                    <i class="material-icons align-middle me-2 text-primary">edit_location_alt</i>Edit Risk
                                    Area: {{ $riskArea->heading }}
                                </h4>
                                <p class="text-muted mb-0 text-sm">Update the risk zone details and its geographic position
                                </p>
                            </div>
                            <a href="{{ route('risk-areas.index') }}" class="btn btn-outline-secondary btn-sm mb-0">
                                <i class="material-icons align-middle me-1">arrow_back</i> Back to List
                            </a>
                        </div>
                        <hr class="horizontal dark my-0">
                    </div>
                    <div class="card-body pt-4">
                        <form action="{{ route('risk-areas.update', $riskArea->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <div class="col-md-8">
                                    <div class=" mb-4">
                                        <label class="fw-bold">Area Heading</label>
                                        <input type="text" name="heading" class="form-control form-control-lg"
                                            placeholder="e.g. Landslide Zone - Rambukkana" required
                                            value="{{ old('heading', $riskArea->heading) }}">
                                        @error('heading') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class=" mb-4">
                                        <label class="fw-bold">Detailed Description</label>
                                        <textarea name="description" class="form-control" rows="4"
                                            placeholder="Describe the risk factors and specific instructions for drivers...">{{ old('description', $riskArea->description) }}</textarea>
                                        @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class=" mb-4">
                                        <label class="fw-bold">Risk Level</label>
                                        <select name="status" class="form-control" required>
                                            <option value="Low" {{ old('status', $riskArea->status) == 'Low' ? 'selected' : '' }}>Low (Minor Risk)</option>
                                            <option value="Medium" {{ old('status', $riskArea->status) == 'Medium' ? 'selected' : '' }}>Medium (Caution Advised)</option>
                                            <option value="High" {{ old('status', $riskArea->status) == 'High' ? 'selected' : '' }}>High (Extreme Hazard)</option>
                                        </select>
                                        @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="alert alert-info border-0 text-white text-xs py-2">
                                        <i class="material-icons align-middle me-1">info</i>
                                        Status affects how the area is visualized on the heat map.
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="fw-bold mb-2">Update Geographic Location</label>
                                    <p class="text-muted text-xs mb-3">Click on the map or drag the marker to update the
                                        exact coordinates.</p>
                                    <div id="map" class="border shadow-inner mb-3"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="">
                                        <label class="fw-bold">Latitude</label>
                                        <input type="text" name="latitude" id="latitude" class="form-control" required
                                            readonly value="{{ old('latitude', $riskArea->latitude) }}">
                                        @error('latitude') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="">
                                        <label class="fw-bold">Longitude</label>
                                        <input type="text" name="longitude" id="longitude" class="form-control" required
                                            readonly value="{{ old('longitude', $riskArea->longitude) }}">
                                        @error('longitude') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('risk-areas.index') }}" class="btn btn-light me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary px-5">
                                    <i class="material-icons align-middle me-2">update</i>Update Risk Area
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $(document).ready(function () {
            const initialLat = {{ old('latitude', $riskArea->latitude) }};
            const initialLng = {{ old('longitude', $riskArea->longitude) }};

            const map = L.map('map').setView([initialLat, initialLng], 7);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const marker = L.marker([initialLat, initialLng], {
                draggable: true
            }).addTo(map);

            function updateInputs(lat, lng) {
                $('#latitude').val(lat.toFixed(6));
                $('#longitude').val(lng.toFixed(6));
            }

            marker.on('dragend', function (e) {
                const position = marker.getLatLng();
                updateInputs(position.lat, position.lng);
            });

            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                updateInputs(e.latlng.lat, e.latlng.lng);
            });
        });
    </script>
@endsection