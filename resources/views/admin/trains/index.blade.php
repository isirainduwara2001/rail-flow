@extends('layouts.app')

@section('title', 'Trains Management - RailFlow')

@section('content')

    <div class="card">
        
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="material-icons align-middle me-2">train</i>Train Management
                    </h4>
                    <p class="text-muted mb-0">Manage and organize all trains in your fleet</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#trainModal">
                    <i class="material-icons align-middle me-2">add_circle</i>Add New Train
                </button>
            </div>
        </div>

        <div class="card-body shadow-sm">
            <div class="align-items-center">
                <div class="col-md-12">
                    <h5 class="mb-0 fw-bold">
                        <i class="material-icons align-middle me-2">list</i>All Trains
                    </h5>
                </div>
            </div>
            <br>
            <table class="table table-hover mb-0" id="trainsTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Train Name</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Train Number</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Total Seats</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include Modal -->
    @include('admin.trains.modals.form')

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#trainsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('trains.data') }}",
                columns: [{
                        data: 'name'
                    },
                    {
                        data: 'train_number'
                    },
                    {
                        data: 'total_seats',
                        render: function(data) {
                            return `<span class="badge bg-info">${data} Seats</span>`;
                        }
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                    <div class="d-flex justify-content-end">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="/admin/trains/${data}/prices" class="btn btn-outline-info" title="Manage Ticket Prices">
                                <i class="material-icons">local_offer</i>
                            </a>
                            <button class="btn btn-outline-primary edit-train mx-1 rounded" data-id="${data}" data-name="${row.name}" data-train_number="${row.train_number}" data-total_seats="${row.total_seats}" title="Edit">
                                <i class="material-icons">edit</i>
                            </button>
                            <button class="btn btn-outline-danger delete-train mx-1 rounded" data-id="${data}" title="Delete">
                                <i class="material-icons">delete</i>
                            </button>
                        </div>
                    </div>
                `;
                        }
                    }
                ],
                drawCallback: function() {
                    updateStats();
                }
            });

            // Search functionality
            $('#trainSearch').on('keyup', function() {
                table.search($(this).val()).draw(false);
            });

            // Add Train Form
            $('#trainForm').submit(function(e) {
                e.preventDefault();
                const formData = new FormData();
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                formData.append('name', $('#trainName').val());
                formData.append('train_number', $('#trainNumber').val());
                formData.append('total_seats', $('#trainCapacity').val());

                $.ajax({
                    url: "{{ route('trains.store') }}",
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#trainModal').modal('hide');
                        $('#trainForm')[0].reset();
                        table.ajax.reload();
                        showNotification('Success', 'Train added successfully!', 'success');
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            const errorMsg = Object.values(errors).flat().join(', ');
                            showNotification('Validation Error', errorMsg, 'error');
                        } else {
                            showNotification('Error', xhr.responseJSON?.message || 'Error adding train', 'error');
                        }
                    }
                });
            });
            $(document).on('click', '.edit-train', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const trainNumber = $(this).data('train_number');
                const totalSeats = $(this).data('total_seats');

                // Populate form with current values
                $('#trainName').val(name);
                $('#trainNumber').val(trainNumber);
                $('#trainCapacity').val(totalSeats);

                // Change form action to update
                $('#trainForm').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData();
                    formData.append('_method', 'PUT');
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                    formData.append('name', $('#trainName').val());
                    formData.append('train_number', $('#trainNumber').val());
                    formData.append('total_seats', $('#trainCapacity').val());

                    $.ajax({
                        url: `{{ route('trains.update', ':id') }}`.replace(':id', id),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#trainModal').modal('hide');
                            $('#trainForm')[0].reset();
                            table.ajax.reload();
                            showNotification('Success', 'Train updated successfully!', 'success');
                        },
                        error: function(xhr) {
                            const errors = xhr.responseJSON?.errors;
                            if (errors) {
                                const errorMsg = Object.values(errors).flat().join(', ');
                                showNotification('Validation Error', errorMsg, 'error');
                            } else {
                                showNotification('Error', xhr.responseJSON?.message || 'Error updating train', 'error');
                            }
                        }
                    });
                });

                // Change modal title
                $('#trainModal .modal-title').html('<i class="material-icons align-middle me-2">edit</i>Edit Train');

                // Show modal
                $('#trainModal').modal('show');
            });

            // Reset form for create when modal is closed
            $('#trainModal').on('hidden.bs.modal', function() {
                $('#trainForm')[0].reset();
                $('#trainForm').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData();
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                    formData.append('name', $('#trainName').val());
                    formData.append('train_number', $('#trainNumber').val());
                    formData.append('total_seats', $('#trainCapacity').val());

                    $.ajax({
                        url: "{{ route('trains.store') }}",
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#trainModal').modal('hide');
                            $('#trainForm')[0].reset();
                            table.ajax.reload();
                            showNotification('Success', 'Train added successfully!', 'success');
                        },
                        error: function(xhr) {
                            const errors = xhr.responseJSON?.errors;
                            if (errors) {
                                const errorMsg = Object.values(errors).flat().join(', ');
                                showNotification('Validation Error', errorMsg, 'error');
                            } else {
                                showNotification('Error', xhr.responseJSON?.message || 'Error adding train', 'error');
                            }
                        }
                    });
                });
                $('#trainModal .modal-title').html('<i class="material-icons align-middle me-2">train</i><span>Add New Train</span>');
            });

            // Delete Train
            $(document).on('click', '.delete-train', function() {
                if (confirm('Are you sure you want to delete this train? This action cannot be undone.')) {
                    let id = $(this).data('id');
                    $.ajax({
                        url: `{{ route('trains.destroy', ':id') }}`.replace(':id', id),
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            table.ajax.reload();
                            showNotification('Success', 'Train deleted successfully!', 'success');
                        },
                        error: function() {
                            showNotification('Error', 'Error deleting train', 'error');
                        }
                    });
                }
            });

            function updateStats() {
                const data = table.data().count();
                $('#totalTrains').text(data);
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
