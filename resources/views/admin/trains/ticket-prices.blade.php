@extends('layouts.app')

@section('title', 'Ticket Prices - ' . $train->name . ' - RailFlow')

@section('content')

    <div class="card">
        <div class="card-header">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="material-icons align-middle me-2">local_offer</i>{{ $train->name }} - Ticket Prices
                    </h4>
                    <p class="text-muted mb-0">Manage seat classes and pricing for this train (Total Seats: {{ $train->total_seats }})</p>
                </div>
                <a href="{{ route('trains.index') }}" class="btn btn-secondary">
                    <i class="material-icons align-middle me-2">arrow_back</i>Back to Trains
                </a>
            </div>
        </div>

        <div class="card-body shadow-sm">
            <div class="mb-3">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#priceModal">
                    <i class="material-icons align-middle me-2">add_circle</i>Add Seat Class
                </button>
            </div>

            <div class="alert alert-info mb-3">
                <i class="material-icons align-middle me-2">info</i>
                <strong>How it works:</strong> Define different seat classes (e.g., Window Seat, 2nd Class, etc.) with their respective seat counts and prices. The sum of all seat counts must not exceed the train's total capacity.
            </div>

            <table class="table table-hover mb-0" id="pricesTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Class Name</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Seats</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Price (LKR)</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Description</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold">Total Seats Allocated</h6>
                            <h4 class="text-primary" id="totalSeats">0</h4>
                            <small class="text-muted">Out of {{ $train->total_seats }} available</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold">Total Classes</h6>
                            <h4 class="text-success" id="totalClasses">0</h4>
                            <small class="text-muted">Seat class types</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Add/Edit Price Modal -->
<div class="modal fade" id="priceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient border-0">
                <h5 class="modal-title fw-bold">
                    <i class="material-icons align-middle me-2">local_offer</i>Add Seat Class
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="priceForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="className" class="form-label fw-5">Class Name</label>
                        <input type="text" class="form-control" id="className" name="class_name" placeholder="e.g., Window Seat, 2nd Class" required>
                    </div>

                    <div class="mb-3">
                        <label for="seatCount" class="form-label fw-5">Number of Seats</label>
                        <input type="number" class="form-control" id="seatCount" name="seat_count" min="1" placeholder="e.g., 50" required>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label fw-5">Price per Ticket (LKR)</label>
                        <input type="number" class="form-control" id="price" name="price" min="0.01" step="0.01" placeholder="e.g., 1500" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-5">Description (Optional)</label>
                        <textarea class="form-control" id="description" name="description" rows="2" placeholder="e.g., Window seats with AC"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="material-icons align-middle">save</i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const trainId = {{ $train->id }};

    // Initialize DataTable
    const table = $('#pricesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('prices.data', $train->id) }}",
        columns: [
            { data: 'class_name' },
            { data: 'seat_count' },
            { data: 'formatted_price' },
            { data: 'description', render: function(data) {
                return data || '<em class="text-muted">-</em>';
            }},
            { data: 'id', render: function(data) {
                return `
                    <div class="d-flex justify-content-end">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-primary edit-price" data-id="${data}" title="Edit">
                                <i class="material-icons">edit</i>
                            </button>
                            <button class="btn btn-outline-danger delete-price" data-id="${data}" title="Delete">
                                <i class="material-icons">delete</i>
                            </button>
                        </div>
                    </div>
                `;
            }}
        ],
        drawCallback: function() {
            updateStats();
        }
    });

    // Add/Edit Price Form
    $('#priceForm').submit(function(e) {
        e.preventDefault();

        let formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            class_name: $('#className').val(),
            seat_count: $('#seatCount').val(),
            price: $('#price').val(),
            description: $('#description').val()
        };

        let url = "{{ route('prices.store', $train->id) }}";
        let method = 'POST';

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                $('#priceModal').modal('hide');
                $('#priceForm')[0].reset();
                table.ajax.reload();
                showNotification('Success', response.message, 'success');
            },
            error: function(xhr) {
                console.error('Error response:', xhr.responseJSON);
                let errorMessage = 'Error saving seat class';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification('Error', errorMessage, 'error');
            }
        });
    });

    // Delete Price
    $(document).on('click', '.delete-price', function() {
        if(confirm('Are you sure you want to delete this seat class?')) {
            let id = $(this).data('id');
            $.ajax({
                url: `{{ route('prices.destroy', [$train->id, ':id']) }}`.replace(':id', id),
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function() {
                    table.ajax.reload();
                    showNotification('Success', 'Seat class deleted successfully!', 'success');
                },
                error: function(xhr) {
                    showNotification('Error', xhr.responseJSON?.message || 'Error deleting seat class', 'error');
                }
            });
        }
    });

    // Edit Price
    $(document).on('click', '.edit-price', function() {
        let id = $(this).data('id');
        let row = table.row($(this).closest('tr')).data();

        $('#className').val(row.class_name);
        $('#seatCount').val(row.seat_count);
        $('#price').val(row.price);
        $('#description').val(row.description);

        $('#priceForm').off('submit').submit(function(e) {
            e.preventDefault();

            let formData = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                class_name: $('#className').val(),
                seat_count: $('#seatCount').val(),
                price: $('#price').val(),
                description: $('#description').val()
            };

            $.ajax({
                url: `{{ route('prices.update', [$train->id, ':id']) }}`.replace(':id', id),
                method: 'PUT',
                data: formData,
                success: function(response) {
                    $('#priceModal').modal('hide');
                    $('#priceForm')[0].reset();
                    table.ajax.reload();
                    showNotification('Success', response.message, 'success');
                },
                error: function(xhr) {
                    console.error('Error response:', xhr.responseJSON);
                    let errorMessage = 'Error updating seat class';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showNotification('Error', errorMessage, 'error');
                }
            });
        });

        $('#priceModal').modal('show');
    });

    // Reset form when modal is closed for new entry
    $('#priceModal').on('hide.bs.modal', function() {
        $('#priceForm')[0].reset();
        $('#priceForm').off('submit').submit(function(e) {
            e.preventDefault();

            let formData = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                class_name: $('#className').val(),
                seat_count: $('#seatCount').val(),
                price: $('#price').val(),
                description: $('#description').val()
            };

            $.ajax({
                url: "{{ route('prices.store', $train->id) }}",
                method: 'POST',
                data: formData,
                success: function(response) {
                    $('#priceModal').modal('hide');
                    $('#priceForm')[0].reset();
                    table.ajax.reload();
                    showNotification('Success', response.message, 'success');
                },
                error: function(xhr) {
                    console.error('Error response:', xhr.responseJSON);
                    let errorMessage = 'Error saving seat class';
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

    function updateStats() {
        let totalSeats = 0;
        let totalClasses = table.data().count();

        table.data().each(function() {
            totalSeats += parseInt(this.seat_count) || 0;
        });

        $('#totalSeats').text(totalSeats);
        $('#totalClasses').text(totalClasses);
    }
});
</script>
@endsection
