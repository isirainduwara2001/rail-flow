@extends('layouts.app')

@section('title', 'Create Booking for User - RailFlow')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">
        <i class="material-icons align-middle me-2">person_add</i>Create Booking for User
    </h2>

    <div class="row">
        <!-- Step 1: Select User -->
        <div class="col-md-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="material-icons align-middle me-2">step_1</i>Select User by Email
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="userEmail" class="form-label fw-bold">User Email Address</label>
                        <input type="email" class="form-control" id="userEmail" placeholder="Search user by email..." autocomplete="off">
                        <small class="text-muted d-block mt-2">Start typing to search for users</small>
                    </div>

                    <div id="userSearchResults" style="display: none;">
                        <div class="list-group" id="usersList" style="max-height: 300px; overflow-y: auto;">
                            <!-- Users will be populated here -->
                        </div>
                    </div>

                    <div id="selectedUserInfo" style="display: none;" class="mt-3">
                        <div class="alert alert-info" role="alert">
                            <strong>Selected User:</strong>
                            <span id="selectedUserName"></span> (<span id="selectedUserEmail"></span>)
                            <button type="button" class="btn btn-sm btn-outline-secondary float-end" id="changeUserBtn">Change</button>
                        </div>
                        <input type="hidden" id="userId">
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Search Trains -->
        <div class="col-md-12 mb-4" id="step2Container" style="display: none;">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="material-icons align-middle me-2">train</i>Step 2: Search Trains
                    </h5>
                </div>
                <div class="card-body">
                    <form id="searchTrainForm" class="row g-3">
                        <div class="col-md-4">
                            <label for="fromStation" class="form-label fw-bold">From Station</label>
                            <select class="form-select" id="fromStation" required>
                                <option value="">-- Select Departure Station --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="toStation" class="form-label fw-bold">To Station</label>
                            <select class="form-select" id="toStation" required>
                                <option value="">-- Select Destination Station --</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="departureDate" class="form-label fw-bold">Journey Date</label>
                            <input type="date" class="form-control" id="departureDate" required>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="material-icons align-middle">search</i>Search
                            </button>
                        </div>
                    </form>

                    <!-- Train Results -->
                    <div id="trainResults" style="display: none;" class="mt-4">
                        <h5 class="mb-3">Available Trains</h5>
                        <div id="trainsList" class="row g-3">
                            <!-- Trains will be populated here -->
                        </div>
                    </div>

                    <div id="noTrainsMessage" style="display: none;" class="alert alert-warning mt-4">
                        No trains found for the selected route and date.
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Select Seats -->
        <div class="col-md-12 mb-4" id="step3Container" style="display: none;">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="material-icons align-middle me-2">event_seat</i>Step 3: Select Seat
                    </h5>
                </div>
                <div class="card-body">
                    <div id="seatPickerContainer">
                        <!-- Seat picker will be populated here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Confirm Booking -->
        <div class="col-md-12" id="step4Container" style="display: none;">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="material-icons align-middle me-2">check_circle</i>Step 4: Confirm Booking
                    </h5>
                </div>
                <div class="card-body">
                    <div id="bookingSummary" class="mb-4">
                        <!-- Booking summary will be populated here -->
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-outline-secondary" id="cancelBookingBtn">Cancel</button>
                        <button type="button" class="btn btn-success" id="confirmBookingBtn">
                            <i class="material-icons align-middle me-1">check</i>Confirm Booking
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div id="loadingSpinner" class="text-center" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1050;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Processing...</p>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    $('#departureDate').attr('min', today);
    $('#departureDate').val(today);

    let selectedScheduleId = null;
    let selectedSeatId = null;
    let selectedPrice = null;

    // Load stations on page load
    loadStations();

    function loadStations() {
        $.ajax({
            url: "{{ route('staff-booking.stations') }}",
            method: 'GET',
            success: function(data) {
                if (data.success && data.stations.length > 0) {
                    const stationsHtml = data.stations.map(station =>
                        `<option value="${station}">${station}</option>`
                    ).join('');

                    $('#fromStation').append(stationsHtml);
                    $('#toStation').append(stationsHtml);
                }
            },
            error: function() {
                console.error('Error loading stations');
            }
        });
    }

    // User Search
    let searchTimeout;
    $('#userEmail').on('keyup', function() {
        clearTimeout(searchTimeout);
        const email = $(this).val().trim();

        if (email.length < 2) {
            $('#userSearchResults').hide();
            return;
        }

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: "{{ route('staff-booking.search-users') }}",
                method: 'GET',
                data: { email: email },
                success: function(data) {
                    if (data.success && data.users.length > 0) {
                        $('#usersList').html('');
                        data.users.forEach(user => {
                            const userElement = `
                                <button type="button" class="list-group-item list-group-item-action select-user" data-user-id="${user.id}" data-user-name="${user.name}" data-user-email="${user.email}">
                                    <div class="fw-bold">${user.name}</div>
                                    <small class="text-muted">${user.email}</small>
                                </button>
                            `;
                            $('#usersList').append(userElement);
                        });
                        $('#userSearchResults').show();
                    } else {
                        $('#userSearchResults').hide();
                    }
                }
            });
        }, 300);
    });

    // Select User
    $(document).on('click', '.select-user', function() {
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        const userEmail = $(this).data('user-email');

        $('#userId').val(userId);
        $('#selectedUserName').text(userName);
        $('#selectedUserEmail').text(userEmail);
        $('#userEmail').val(userEmail);

        $('#userSearchResults').hide();
        $('#selectedUserInfo').show();
        $('#step2Container').show();

        // Reset other steps
        resetSteps();
    });

    // Change User
    $('#changeUserBtn').on('click', function() {
        $('#userEmail').val('');
        $('#selectedUserInfo').hide();
        $('#step2Container').hide();
        $('#userEmail').focus();
        resetSteps();
    });

    // Search Trains
    $('#searchTrainForm').submit(function(e) {
        e.preventDefault();

        const fromStation = $('#fromStation').val();
        const toStation = $('#toStation').val();
        const departureDate = $('#departureDate').val();

        showLoading();

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
            success: function(data) {
                hideLoading();
                if (data.success && data.schedules.length > 0) {
                    displayTrains(data.schedules);
                    $('#trainResults').show();
                    $('#noTrainsMessage').hide();
                } else {
                    $('#trainResults').hide();
                    $('#noTrainsMessage').show();
                }
            },
            error: function() {
                hideLoading();
                alert('Error searching trains. Please try again.');
            }
        });
    });

    // Display Trains
    function displayTrains(schedules) {
        $('#trainsList').html('');
        schedules.forEach(schedule => {
            const departureTime = new Date(schedule.departure).toLocaleString();
            const arrivalTime = new Date(schedule.arrival).toLocaleString();

            const trainCard = `
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm train-option" data-schedule-id="${schedule.id}">
                        <div class="card-body">
                            <h5 class="card-title">${schedule.train_name} (${schedule.train_number})</h5>
                            <p class="card-text">
                                <strong>${schedule.from} → ${schedule.to}</strong><br>
                                <small class="text-muted">Departure: ${departureTime}</small><br>
                                <small class="text-muted">Arrival: ${arrivalTime}</small><br>
                                <strong>Price Range: LKR ${schedule.min_price.toFixed(2)} - ${schedule.max_price.toFixed(2)}</strong><br>
                                <strong class="text-success">Available Seats: ${schedule.available_seats}/${schedule.total_seats}</strong>
                            </p>
                            <button type="button" class="btn btn-primary btn-sm select-schedule" data-schedule-id="${schedule.id}">Select</button>
                        </div>
                    </div>
                </div>
            `;
            $('#trainsList').append(trainCard);
        });
    }

    // Select Schedule
    $(document).on('click', '.select-schedule', function() {
        selectedScheduleId = $(this).data('schedule-id');
        showLoading();

        $.ajax({
            url: `/booking/schedule/${selectedScheduleId}/seat-data`,
            method: 'GET',
            success: function(data) {
                hideLoading();
                if (data.success) {
                    displaySeats(data);
                    $('#step3Container').show();
                    $('html, body').animate({ scrollTop: $('#step3Container').offset().top - 100 }, 500);
                }
            },
            error: function() {
                hideLoading();
                alert('Error loading seats. Please try again.');
            }
        });
    });

    // Display Seats
    function displaySeats(data) {
        $('#seatPickerContainer').html('');

        const schedule = data.schedule;
        const seatClasses = data.seat_classes;
        const seats = data.seats;

        let seatPickerHTML = `
            <div class="mb-4">
                <h6 class="mb-3">Route: <strong>${schedule.from} → ${schedule.to}</strong></h6>
                <p class="text-muted">
                    Departure: ${new Date(schedule.departure).toLocaleString()}<br>
                    Arrival: ${new Date(schedule.arrival).toLocaleString()}
                </p>
            </div>

            <div class="mb-4">
                <h6 class="mb-3">Seat Classes & Prices:</h6>
                <div class="row">
        `;

        seatClasses.forEach(seatClass => {
            seatPickerHTML += `
                <div class="col-md-4 mb-2">
                    <div class="card">
                        <div class="card-body">
                            <h6>${seatClass.class_name}</h6>
                            <p class="text-primary fw-bold">LKR ${seatClass.price.toFixed(2)}</p>
                            <small class="text-muted">${seatClass.description || ''}</small>
                        </div>
                    </div>
                </div>
            `;
        });

        seatPickerHTML += `
                </div>
            </div>

            <div class="mb-4">
                <h6 class="mb-3">Select a Seat:</h6>
                <div class="row seat-container">
        `;

        seats.forEach(seat => {
            const isAvailable = seat.available;
            const seatPrice = seat.price || 0;
            const seatClass = 'seat-option ' + (isAvailable ? 'available' : 'booked');
            const buttonClass = isAvailable ? 'btn-outline-primary' : 'btn-outline-secondary disabled';
            const disabled = !isAvailable ? 'disabled' : '';

            seatPickerHTML += `
                <div class="col-auto mb-2">
                    <button type="button" class="btn ${buttonClass} seat-btn ${isAvailable ? 'select-seat' : ''}"
                            data-seat-id="${seat.id}"
                            data-seat-number="${seat.number}"
                            data-price="${seatPrice}"
                            ${disabled}
                            title="Seat ${seat.number} - LKR ${seatPrice.toFixed(2)}">
                        ${seat.number}
                    </button>
                </div>
            `;
        });

        seatPickerHTML += `
                </div>
            </div>
        `;

        $('#seatPickerContainer').html(seatPickerHTML);
    }

    // Select Seat
    $(document).on('click', '.select-seat', function() {
        // Remove previous selection
        $('.select-seat').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');

        selectedSeatId = $(this).data('seat-id');
        selectedPrice = parseFloat($(this).data('price'));

        // Show booking summary
        showBookingSummary();
    });

    // Show Booking Summary
    function showBookingSummary() {
        const userName = $('#selectedUserName').text();
        const userEmail = $('#selectedUserEmail').text();

        // Get train info from UI
        const trainCard = $('#trainsList').find('.select-schedule[data-schedule-id="' + selectedScheduleId + '"]').closest('.card');
        const trainName = trainCard.find('.card-title').text();

        const seatBtn = $(`.select-seat[data-seat-id="${selectedSeatId}"]`);
        const seatNumber = seatBtn.data('seat-number');

        const summary = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold">Passenger Details</h6>
                    <p>
                        <strong>Name:</strong> ${userName}<br>
                        <strong>Email:</strong> ${userEmail}
                    </p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Booking Details</h6>
                    <p>
                        <strong>Train:</strong> ${trainName}<br>
                        <strong>Seat Number:</strong> ${seatNumber}<br>
                        <strong>Price:</strong> LKR ${selectedPrice.toFixed(2)}
                    </p>
                </div>
            </div>
        `;

        $('#bookingSummary').html(summary);
        $('#step4Container').show();
        $('html, body').animate({ scrollTop: $('#step4Container').offset().top - 100 }, 500);
    }

    // Confirm Booking
    $('#confirmBookingBtn').on('click', function() {
        showLoading();

        $.ajax({
            url: "{{ route('staff-booking.book') }}",
            method: 'POST',
            data: {
                user_id: $('#userId').val(),
                schedule_id: selectedScheduleId,
                seat_id: selectedSeatId,
                price: selectedPrice,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                hideLoading();
                if (data.success) {
                    showSuccessMessage(data.booking);
                    resetForm();
                } else {
                    alert('Error: ' + data.message);
                }
            },
            error: function(xhr) {
                hideLoading();
                const response = xhr.responseJSON;
                alert('Error: ' + (response?.message || 'Failed to create booking'));
            }
        });
    });

    // Show Success Message
    function showSuccessMessage(booking) {
        const successHTML = `
            <div class="alert alert-success" role="alert">
                <h4 class="alert-heading">
                    <i class="material-icons align-middle me-2">check_circle</i>Booking Confirmed!
                </h4>
                <p>
                    <strong>Booking Reference:</strong> ${booking.reference}<br>
                    <strong>Passenger:</strong> ${booking.user}<br>
                    <strong>Seat:</strong> ${booking.seat}<br>
                    <strong>Price:</strong> LKR ${booking.price.toFixed(2)}<br>
                    <strong>Route:</strong> ${booking.schedule.from} → ${booking.schedule.to}<br>
                    <strong>Departure:</strong> ${booking.schedule.departure}
                </p>
            </div>
        `;

        $('#bookingSummary').html(successHTML);

        setTimeout(function() {
            resetForm();
        }, 3000);
    }

    // Cancel Booking
    $('#cancelBookingBtn').on('click', function() {
        if (confirm('Are you sure you want to cancel this booking creation?')) {
            resetForm();
        }
    });

    // Helper Functions
    function resetSteps() {
        $('#step3Container').hide();
        $('#step4Container').hide();
        selectedScheduleId = null;
        selectedSeatId = null;
        selectedPrice = null;
    }

    function resetForm() {
        $('#userEmail').val('');
        $('#selectedUserInfo').hide();
        $('#step2Container').hide();
        $('#trainResults').hide();
        $('#noTrainsMessage').hide();
        $('#fromStation').val('');
        $('#toStation').val('');
        $('#departureDate').val(today);
        resetSteps();
    }

    function showLoading() {
        $('#loadingSpinner').show();
    }

    function hideLoading() {
        $('#loadingSpinner').hide();
    }
});
</script>
@endsection
