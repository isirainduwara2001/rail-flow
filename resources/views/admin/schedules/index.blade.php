@extends('layouts.app')

@section('title', 'Schedules Management - RailFlow')

@section('content')

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="material-icons align-middle me-2">schedule</i>Schedule Management
                    </h4>
                    <p class="text-muted mb-0">View and manage all train schedules</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                    <i class="material-icons align-middle me-2">add_circle</i>Add New Schedule
                </button>
            </div>
        </div>

        <div class="card-body shadow-sm">
            <div class="align-items-center mb-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" id="scheduleSearch"
                            placeholder="Search schedules...">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" id="trainFilter">
                            <option value="">All Trains</option>
                        </select>
                    </div>
                </div>
            </div>
            <table class="table table-hover mb-0" id="schedulesTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Train</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Route</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Departure</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Arrival</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Status</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include Modal -->
    @include('admin.schedules.modals.form')

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            const table = $('#schedulesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('schedules.data') }}",
                columns: [
                    {
                        data: 'train.name', render: function (data, type, row) {
                            return `<span class="badge bg-primary">${data}</span>`;
                        }
                    },
                    {
                        data: null, render: function (data, type, row) {
                            return `${row.from} <i class="material-icons align-middle">arrow_forward</i> ${row.to}`;
                        }
                    },
                    { data: 'departure' },
                    { data: 'arrival' },
                    {
                        data: 'status', render: function (data) {
                            const colors = {
                                'scheduled': 'info',
                                'delayed': 'warning',
                                'departed': 'primary',
                                'arrived': 'success',
                                'cancelled': 'danger'
                            };
                            return `<span class="badge bg-${colors[data] || 'secondary'}">${data.toUpperCase()}</span>`;
                        }
                    },
                    {
                        data: 'id', render: function (data) {
                            return `
                                    <div class="d-flex justify-content-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-primary edit-schedule mx-1 rounded" data-id="${data}" title="Edit">
                                                <i class="material-icons">edit</i>
                                            </button>
                                            <button class="btn btn-outline-danger delete-schedule mx-1 rounded" data-id="${data}" title="Delete">
                                                <i class="material-icons">delete</i>
                                            </button>
                                        </div>
                                    </div>
                                `;
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
                            $('#trainId').append(`<option value="${train.id}">${train.name}</option>`);
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
            $('#scheduleSearch').on('keyup', function () {
                table.search($(this).val()).draw(false);
            });

            $('#trainFilter').on('change', function () {
                table.ajax.reload();
            });

            // Add Schedule Form
            $('#scheduleForm').submit(function (e) {
                e.preventDefault();

                // Convert datetime-local format to the format expected by the server
                let departureValue = $('#departureTime').val();
                let arrivalValue = $('#arrivalTime').val();

                // Convert YYYY-MM-DDTHH:mm to YYYY-MM-DD HH:mm
                let departure = departureValue ? departureValue.replace('T', ' ') : '';
                let arrival = arrivalValue ? arrivalValue.replace('T', ' ') : '';

                let formData = {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    train_id: $('#trainId').val(),
                    from: $('#fromStation').val(),
                    to: $('#toStation').val(),
                    departure: departure,
                    arrival: arrival,
                    status: 'scheduled'
                };

                $.ajax({
                    url: "{{ route('schedules.store') }}",
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        $('#scheduleModal').modal('hide');
                        $('#scheduleForm')[0].reset();
                        table.ajax.reload();
                        showNotification('Success', 'Schedule added successfully!', 'success');
                    },
                    error: function (xhr) {
                        console.error('Error response:', xhr.responseJSON);
                        let errorMessage = 'Error adding schedule';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showNotification('Error', errorMessage, 'error');
                    }
                });
            });

            // Show/hide delay warning
            $('#status').on('change', function () {
                if ($(this).val() === 'delayed') {
                    $('#delayWarning').show();
                } else {
                    $('#delayWarning').hide();
                }
            });

            // Edit Schedule
            $(document).on('click', '.edit-schedule', function () {
                let id = $(this).data('id');

                // Fetch schedule data
                $.get(`{{ route('schedules.details', ':id') }}`.replace(':id', id), function (response) {
                    if (response.success) {
                        const schedule = response.data;

                        // Populate form
                        $('#trainId').val(schedule.train_id);
                        $('#fromStation').val(schedule.from);
                        $('#toStation').val(schedule.to);
                        $('#status').val(schedule.status || 'scheduled');
                        $('#statusField').show();

                        if (schedule.status === 'delayed') {
                            $('#delayWarning').show();
                        } else {
                            $('#delayWarning').hide();
                        }

                        // Convert datetime format for datetime-local input (needs format: YYYY-MM-DDTHH:mm)
                        const departureDateTime = schedule.departure.replace(' ', 'T');
                        const arrivalDateTime = schedule.arrival.replace(' ', 'T');

                        $('#departureTime').val(departureDateTime);
                        $('#arrivalTime').val(arrivalDateTime);

                        // Change modal title and button
                        $('#scheduleModal .modal-title').html('<i class="material-icons align-middle me-2">schedule</i>Edit Schedule');
                        $('#scheduleForm .btn-primary').html('<i class="material-icons align-middle">save</i> Update Schedule');

                        // Change form submission handler
                        $('#scheduleForm').off('submit').submit(function (e) {
                            e.preventDefault();

                            let departureValue = $('#departureTime').val();
                            let arrivalValue = $('#arrivalTime').val();

                            let departure = departureValue ? departureValue.replace('T', ' ') : '';
                            let arrival = arrivalValue ? arrivalValue.replace('T', ' ') : '';

                            let formData = {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                from: $('#fromStation').val(),
                                to: $('#toStation').val(),
                                departure: departure,
                                arrival: arrival,
                                status: $('#status').val()
                            };

                            $.ajax({
                                url: `{{ route('schedules.update', ':id') }}`.replace(':id', id),
                                method: 'PUT',
                                data: formData,
                                success: function (response) {
                                    $('#scheduleModal').modal('hide');
                                    $('#scheduleForm')[0].reset();
                                    table.ajax.reload();
                                    showNotification('Success', response.message, 'success');
                                },
                                error: function (xhr) {
                                    console.error('Error response:', xhr.responseJSON);
                                    let errorMessage = 'Error updating schedule';
                                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMessage = xhr.responseJSON.message;
                                    }
                                    showNotification('Error', errorMessage, 'error');
                                }
                            });
                        });

                        $('#scheduleModal').modal('show');
                    }
                });
            });

            $('#scheduleModal').on('hide.bs.modal', function () {
                $('#scheduleForm')[0].reset();
                $('#scheduleModal .modal-title').html('<i class="material-icons align-middle me-2">schedule</i>Add New Schedule');
                $('#scheduleForm .btn-primary').html('<i class="material-icons align-middle">save</i> Save Schedule');
                $('#statusField').hide();
                $('#delayWarning').hide();

                // Reset to add form handler
                $('#scheduleForm').off('submit').submit(function (e) {
                    e.preventDefault();

                    let departureValue = $('#departureTime').val();
                    let arrivalValue = $('#arrivalTime').val();

                    let departure = departureValue ? departureValue.replace('T', ' ') : '';
                    let arrival = arrivalValue ? arrivalValue.replace('T', ' ') : '';

                    let formData = {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        train_id: $('#trainId').val(),
                        from: $('#fromStation').val(),
                        to: $('#toStation').val(),
                        departure: departure,
                        arrival: arrival
                    };

                    $.ajax({
                        url: "{{ route('schedules.store') }}",
                        method: 'POST',
                        data: formData,
                        success: function (response) {
                            $('#scheduleModal').modal('hide');
                            $('#scheduleForm')[0].reset();
                            table.ajax.reload();
                            showNotification('Success', 'Schedule added successfully!', 'success');
                        },
                        error: function (xhr) {
                            console.error('Error response:', xhr.responseJSON);
                            let errorMessage = 'Error adding schedule';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            showNotification('Error', errorMessage, 'error');
                        }
                    });
                });
            });

            // Delete Schedule
            $(document).on('click', '.delete-schedule', function () {
                if (confirm('Are you sure you want to delete this schedule? Associated bookings will remain.')) {
                    let id = $(this).data('id');
                    $.ajax({
                        url: `{{ route('schedules.destroy', ':id') }}`.replace(':id', id),
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function () {
                            table.ajax.reload();
                            showAlert('Schedule deleted successfully!', 'success');
                        },
                        error: function () {
                            showAlert('Error deleting schedule', 'danger');
                        }
                    });
                }
            });

            function updateStats() {
                const data = table.data().count();
                $('#totalSchedules').text(data);
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
    </style>
@endsection