@extends('layouts.app')

@section('title', 'Bookings Management - RailFlow')

@section('content')

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="material-icons align-middle me-2">confirmation_number</i>Bookings Management
                    </h4>
                    <p class="text-muted mb-0">Monitor and manage all train bookings</p>
                </div>
                <button type="button" class="btn btn-outline-secondary" id="exportBookings">
                    <i class="material-icons align-middle me-1">download</i>Export
                </button>
            </div>
        </div>

        <div class="row px-4 mt-3 mb-2">
            <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                <div class="card bg-gradient-info shadow-info border-radius-xl">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold text-white">Total Bookings</p>
                                    <h5 class="font-weight-bolder mb-0 text-white" id="totalBookings">0</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end text-white">
                                <i class="material-icons opacity-10">confirmation_number</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                <div class="card bg-gradient-success shadow-success border-radius-xl">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold text-white">Confirmed</p>
                                    <h5 class="font-weight-bolder mb-0 text-white" id="confirmedCount">0</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end text-white">
                                <i class="material-icons opacity-10">check_circle</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                <div class="card bg-gradient-danger shadow-danger border-radius-xl">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold text-white">Cancelled</p>
                                    <h5 class="font-weight-bolder mb-0 text-white" id="cancelledCount">0</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end text-white">
                                <i class="material-icons opacity-10">cancel</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="card-body shadow-sm">
            <div class="mb-3">
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm" id="bookingSearch"
                            placeholder="Search booking ID, passenger...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="trainFilter">
                            <option value="">All Trains</option>
                        </select>
                    </div>
                </div>
            </div>
            <table class="table table-hover mb-0" id="bookingsTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Booking ID</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Passenger</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Train</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Route</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Seat</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Amount</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Status</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Booked At</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include Modal -->
    @include('admin.bookings.modals.details')

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            const table = $('#bookingsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('bookings.data') }}",
                    data: function (d) {
                        d.status = $('#statusFilter').val();
                        d.train_id = $('#trainFilter').val();
                    }
                },
                columns: [
                    {
                        data: 'id', render: function (data) {
                            return `<span class="badge bg-info">#${data}</span>`;
                        }
                    },
                    {
                        data: 'user_name', render: function (data, type, row) {
                            return `<strong>${data}</strong>`;
                        }
                    },
                    {
                        data: 'train_name', render: function (data) {
                            return `<span class="badge bg-primary">${data}</span>`;
                        }
                    },
                    {
                        data: 'route', render: function (data) {
                            return data;
                        }
                    },
                    {
                        data: 'seat_number', render: function (data) {
                            return `<span class="fw-bold">${data}</span>`;
                        }
                    },
                    {
                        data: 'amount', render: function (data) {
                            return `<span class="fw-bold">${data}</span>`;
                        }
                    },
                    { data: 'status' },
                    {
                        data: 'created_at', render: function (data) {
                            return new Date(data).toLocaleDateString('en-IN');
                        }
                    },
                    {
                        data: 'action', render: function (data) {
                            return data;
                        }
                    }
                ],
                drawCallback: function () {
                    updateStats();
                }
            });

            // Load trains for filter
            $.ajax({
                url: "{{ route('trains.list') }}",
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log('Trains response:', response);
                    if (response.success && response.data && response.data.length > 0) {
                        response.data.forEach(train => {
                            $('#trainFilter').append(`<option value="${train.id}">${train.name}</option>`);
                        });
                    } else {
                        console.warn('No trains data received or empty array');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error loading trains:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                }
            });

            // Search functionality
            $('#bookingSearch').on('keyup', function () {
                table.search($(this).val()).draw(false);
            });

            $('#statusFilter').on('change', function () {
                table.ajax.reload();
            });

            $('#trainFilter').on('change', function () {
                table.ajax.reload();
            });

            // View Booking Details
            $(document).on('click', '.view-booking', function () {
                let id = $(this).data('id');
                $.get(`{{ route('bookings.show', ':id') }}`.replace(':id', id), function (data) {
                    $('#bookingDetails').html(`
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Passenger Information</h6>
                                <p><strong>Name:</strong> ${data.user.name}</p>
                                <p><strong>Email:</strong> ${data.user.email}</p>
                                <p><strong>Phone:</strong> ${data.user.phone || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Journey Details</h6>
                                <p><strong>Train:</strong> ${data.schedule.train.name}</p>
                                <p><strong>Route:</strong> ${data.schedule.from_station} → ${data.schedule.to_station}</p>
                                <p><strong>Departure:</strong> ${new Date(data.schedule.departure_time).toLocaleString()}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Booking Details</h6>
                                <p><strong>Seat Number:</strong> ${data.seat.seat_number}</p>
                                <p><strong>Amount:</strong> LKR ${parseInt(data.amount).toLocaleString()}</p>
                                <p><strong>Status:</strong> <span class="badge ${data.status === 'confirmed' ? 'bg-success' : 'bg-danger'}">${data.status}</span></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Booking Dates</h6>
                                <p><strong>Booked On:</strong> ${new Date(data.created_at).toLocaleDateString()}</p>
                                <p><strong>Booked At:</strong> ${new Date(data.created_at).toLocaleTimeString()}</p>
                            </div>
                        </div>
                    `);
                    $('#bookingModal').modal('show');
                });
            });

            // Delete cancelled booking
            $(document).on('click', '.delete-booking', function () {
                if (confirm('Are you sure you want to permanently delete this cancelled booking?')) {
                    let id = $(this).data('id');
                    $.ajax({
                        url: `{{ route('bookings.delete', ':id') }}`.replace(':id', id),
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function () {
                            table.ajax.reload();
                            showAlert('Booking deleted successfully!', 'success');
                        },
                        error: function () {
                            showAlert('Error deleting booking', 'danger');
                        }
                    });
                }
            });

            // Export bookings
            $('#exportBookings').click(function () {
                window.open("{{ route('bookings.export') }}", '_blank');
            });

            function updateStats() {
                const json = table.ajax.json();
                if (json && json.stats) {
                    $('#totalBookings').text(json.stats.total);
                    $('#confirmedCount').text(json.stats.confirmed);
                    $('#cancelledCount').text(json.stats.cancelled);
                }
            }
        });
    </script>

    <style>
        .badge {
            font-weight: 500;
            padding: 8px 12px;
        }

        .btn-group-sm .btn {
            padding: 4px 8px;
        }

        .card.bg-gradient-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
            color: #fff !important;
        }

        .card.bg-gradient-success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
            color: #fff !important;
        }

        .card.bg-gradient-danger {
            background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%) !important;
            color: #fff !important;
        }

        .card.bg-gradient-info .text-white,
        .card.bg-gradient-success .text-white,
        .card.bg-gradient-danger .text-white {
            color: #fff !important;
        }

        .shadow-info {
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.35) !important;
        }

        .shadow-success {
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.35) !important;
        }

        .shadow-danger {
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.35) !important;
        }
    </style>
@endsection