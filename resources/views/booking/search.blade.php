@extends('layouts.app')

@section('title', 'Search Trains - RailFlow')

@section('content')
    <!-- Modern Search Section -->
    <div class="search-hero mb-5">
        <div class="search-container glass-morphism p-4 p-md-5">

            <div class="d-flex align-items-center mb-4">
                <div class="icon-circle bg-primary-gradient me-3">
                    <i class="material-icons text-white">search</i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold header-gradient-text">Find Your Train</h3>
                    <p class="text-muted mb-0">Discover your next journey across the rails</p>
                </div>
            </div>

            <form id="searchForm" class="search-form-row">
                
                <div class="search-field">
                    <div class="field-icon"><i class="material-icons">location_on</i></div>
                    <div class="field-content">
                        <label for="fromStation">From</label>
                        <select id="fromStation" name="from_station" required>
                            <option value="">Select Departure</option>
                        </select>
                    </div>
                </div>

                <div class="search-field-separator d-none d-md-flex">
                    <i class="material-icons">sync_alt</i>
                </div>

                <div class="search-field">
                    <div class="field-icon"><i class="material-icons">near_me</i></div>
                    <div class="field-content">
                        <label for="toStation">To</label>
                        <select id="toStation" name="to_station" required>
                            <option value="">Select Destination</option>
                        </select>
                    </div>
                </div>

                <div class="search-field ms-md-3">
                    <div class="field-icon"><i class="material-icons">calendar_today</i></div>
                    <div class="field-content">
                        <label for="departureDate">Travel Date</label>
                        <input type="date" id="departureDate" name="departure_date" required>
                    </div>
                </div>

                <div class="search-action ms-md-3">
                    <button type="submit" class="btn btn-search-premium">
                        <span>Search</span>
                        <i class="material-icons">arrow_forward</i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div id="resultsSection" style="display: none;">
        <h4 class="mb-4 fw-bold">
            <i class="material-icons align-middle me-2">directions_train</i>Available Trains
            <span class="badge bg-primary ms-2" id="trainCount">0</span>
        </h4>

        <div id="trainsList" class="row g-3">
            <!-- Trains will be loaded here -->
        </div>
    </div>

    <!-- No Results Message -->
    <div id="noResultsMessage" class="card border-0 shadow-sm" style="display: none;">
        <div class="card-body text-center py-5">
            <i class="material-icons" style="font-size: 3rem; color: #ccc;">train_outline</i>
            <h5 class="mt-3 text-muted">No trains found</h5>
            <p class="text-muted">Try adjusting your search criteria and try again.</p>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="text-center" style="display: none; padding: 40px 0;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Searching for trains...</p>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            $('#departureDate').attr('min', today);
            $('#departureDate').val(today);

            // Load stations on page load
            loadStations();

            function loadStations() {
                $.ajax({
                    url: "{{ route('booking.stations') }}",
                    method: 'GET',
                    success: function (data) {
                        if (data.success && data.stations && data.stations.length > 0) {
                            const stationsHtml = data.stations.map(station =>
                                `<option value="${station}">${station}</option>`
                            ).join('');

                            $('#fromStation').append(stationsHtml);
                            $('#toStation').append(stationsHtml);
                            console.log('Stations loaded:', data.stations);
                        }
                    },
                    error: function (err) {
                        console.error('Error loading stations:', err);
                        showAlert('Error loading stations', 'danger');
                    }
                });
            }

            // Search form submission
            $('#searchForm').submit(function (e) {
                e.preventDefault();

                const fromStation = $('#fromStation').val();
                const toStation = $('#toStation').val();
                const departureDate = $('#departureDate').val();

                console.log('Form values:', { from: fromStation, to: toStation, departure_date: departureDate });

                // Validate inputs
                if (!fromStation || !toStation || !departureDate) {
                    showAlert('Please fill in all fields', 'warning');
                    return;
                }

                $('#loadingSpinner').show();
                $('#resultsSection').hide();
                $('#noResultsMessage').hide();

                $.ajax({
                    url: "{{ route('booking.search-schedules') }}",
                    method: 'POST',
                    data: {
                        from: fromStation,
                        to: toStation,
                        departure_date: departureDate,
                        passengers: 1,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        $('#loadingSpinner').hide();

                        if (!data.success || !data.schedules || data.schedules.length === 0) {
                            $('#noResultsMessage').show();
                            return;
                        }

                        $('#trainCount').text(data.schedules.length);
                        displayTrains(data.schedules);
                        $('#resultsSection').show();
                    },
                    error: function (xhr) {
                        $('#loadingSpinner').hide();
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            const errorMessages = Object.values(response.errors).flat().join('\n');
                            showAlert('Error: ' + errorMessages, 'danger');
                        } else {
                            showAlert('Error searching trains', 'danger');
                        }
                    }
                });
            });

            function displayTrains(trains) {
                const trainsList = $('#trainsList');
                trainsList.html('');

                trains.forEach(function (schedule) {
                    const departure = new Date(schedule.departure);
                    const arrival = new Date(schedule.arrival);

                    const durationHours = Math.floor((arrival - departure) / (1000 * 60 * 60));
                    const durationMins = Math.floor(((arrival - departure) % (1000 * 60 * 60)) / (1000 * 60));

                    const trainCard = `
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 hover-scale">
                                <div class="card-header bg-gradient border-0 pb-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1 fw-bold text-white">${schedule.train_name}</h5>
                                            <p class="mb-0 text-white-50 small">Train #${schedule.train_number}</p>
                                        </div>
                                        <span class="badge bg-white text-primary">${schedule.train_number}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center mb-3">
                                        <div class="col-4">
                                            <p class="text-muted small mb-1">Departure</p>
                                            <h6 class="fw-bold">${departure.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' })}</h6>
                                            <p class="text-muted small">${schedule.from}</p>
                                        </div>
                                        <div class="col-4">
                                            <p class="text-muted small mb-1">Duration</p>
                                            <h6 class="fw-bold">${durationHours}h ${durationMins}m</h6>
                                            <p class="text-muted small">
                                                <i class="material-icons align-middle">arrow_forward</i>
                                            </p>
                                        </div>
                                        <div class="col-4">
                                            <p class="text-muted small mb-1">Arrival</p>
                                            <h6 class="fw-bold">${arrival.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' })}</h6>
                                            <p class="text-muted small">${schedule.to}</p>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row text-center">
                                        <div class="col-6">
                                            <p class="text-muted small mb-1">Price Range</p>
                                            <h5 class="fw-bold text-primary">LKR ${parseFloat(schedule.min_price).toLocaleString()} - ${parseFloat(schedule.max_price).toLocaleString()}</h5>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted small mb-1">Available Seats</p>
                                            <h5 class="fw-bold text-success">${schedule.available_seats}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-light border-0 pt-0">
                                    <button class="btn btn-primary w-100" onclick="selectSchedule(${schedule.id})">
                                        <i class="material-icons align-middle me-1">event_seat</i>Select Seats
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;

                    trainsList.append(trainCard);
                });
            }

            function showAlert(message, type) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('body').prepend(alertHtml);
                setTimeout(() => $('.alert').fadeOut().remove(), 3000);
            }
        });

        // Select schedule and go to seat selection
        function selectSchedule(scheduleId) {
            window.location.href = `/booking/schedule/${scheduleId}/seats`;
        }
    </script>

    <style>
        /* Modern Search UI Styles */
        .search-hero {
            position: relative;
            z-index: 10;
        }

        .glass-morphism {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        }

        .header-gradient-text {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 15px rgba(102, 126, 234, 0.25);
        }

        .bg-primary-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .search-form-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .search-field {
            flex: 1;
            min-width: 200px;
            background: white;
            padding: 12px 20px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .search-field:focus-within {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .field-icon {
            color: #667eea;
            margin-right: 15px;
        }

        .field-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .field-content label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .field-content select,
        .field-content input {
            border: none;
            outline: none;
            font-weight: 600;
            color: #1e293b;
            width: 100%;
            background: transparent;
            padding: 4px 0;
        }

        .search-field-separator {
            color: #cbd5e1;
            padding: 0 5px;
        }

        .btn-search-premium {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
            height: 100%;
        }

        .btn-search-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-search-premium:active {
            transform: translateY(0);
        }

        .hover-scale {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-scale:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.08) !important;
        }

        #trainsList .card {
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #eef2f7;
        }

        #trainsList .card-header {
            padding: 25px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }

        #trainsList .card-body {
            padding: 25px;
        }

        .material-icons {
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .search-field {
                min-width: 100%;
            }

            .search-action {
                width: 100%;
            }

            .btn-search-premium {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection