@extends('layouts.app')

@section('title', 'Risk Areas - RailFlow')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #heatmap {
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
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-1">
                                    <i class="material-icons align-middle me-2">map</i>Risk Areas Heat Map
                                </h4>
                                <p class="text-muted mb-0 text-sm">Visual density of risk zones across the network</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="px-3">
                            <div id="heatmap"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-1">
                                    <i class="material-icons align-middle me-2">report_problem</i>Risk Areas Management
                                </h4>
                                <p class="text-muted mb-0 text-sm">Define and monitor high-risk geographic locations</p>
                            </div>
                            @can('risk_areas.create')
                                <a href="{{ route('risk-areas.create') }}" class="btn btn-primary">
                                    <i class="material-icons align-middle me-2">add_circle</i>Add New Area
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Heading</th>
                                        <th class="text-uppercase text-xs font-weight-bolder opacity-7 ps-2">Status</th>
                                        <th class="text-uppercase text-xs font-weight-bolder opacity-7 ps-2">Coordinates
                                        </th>
                                        <th class="text-center text-uppercase text-xs font-weight-bolder opacity-7">Created
                                        </th>
                                        <th class="text-end text-uppercase text-xs font-weight-bolder opacity-7 pe-3">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($riskAreas as $area)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $area->heading }}</h6>
                                                        <p class="text-xs text-secondary mb-0">
                                                            {{ Str::limit($area->description, 50) }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-sm bg-{{ $area->status === 'High' ? 'danger' : ($area->status === 'Medium' ? 'warning' : 'info') }}">
                                                    {{ $area->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $area->latitude }},
                                                    {{ $area->longitude }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span
                                                    class="text-secondary text-xs font-weight-bold">{{ $area->created_at->format('Y-m-d') }}</span>
                                            </td>
                                            <td class="align-middle text-end pe-3">
                                                @can('risk_areas.edit')
                                                    <a href="{{ route('risk-areas.edit', $area->id) }}"
                                                        class="text-secondary font-weight-bold text-xs me-3" data-toggle="tooltip"
                                                        data-original-title="Edit area">
                                                        Edit
                                                    </a>
                                                @endcan
                                                @can('risk_areas.delete')
                                                    <form action="{{ route('risk-areas.destroy', $area->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <a href="javascript:;"
                                                            onclick="if(confirm('Are you sure?')) this.parentElement.submit();"
                                                            class="text-danger font-weight-bold text-xs" data-toggle="tooltip"
                                                            data-original-title="Delete area">
                                                            Delete
                                                        </a>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <p class="text-sm">No risk areas found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://leaflet.github.io/Leaflet.heat/dist/leaflet-heat.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize Map
            const map = L.map('heatmap').setView([7.8731, 80.7718], 7); // Centered on Sri Lanka

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Prepare Heat Map Data
            const riskPoints = [
                @foreach($riskAreas as $area)
                    [{{ $area->latitude }}, {{ $area->longitude }}, {{ $area->status === 'High' ? 1.0 : ($area->status === 'Medium' ? 0.6 : 0.3) }}],
                @endforeach
                ];

            if (riskPoints.length > 0) {
                const heat = L.heatLayer(riskPoints, {
                    radius: 25,
                    blur: 15,
                    maxZoom: 10,
                    gradient: { 0.4: 'blue', 0.65: 'lime', 1: 'red' }
                }).addTo(map);
            }
        });
    </script>
@endsection