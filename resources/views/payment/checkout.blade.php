@extends('layouts.app')

@section('title', 'Checkout - RailFlow')

@section('content')
    <div class="container mt-4 mb-5">

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-gradient"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h5 class="mb-0"><i class="material-icons align-middle">payment</i> Secure Checkout</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Booking Summary Header -->
                        <div class="mb-4">
                            <h6 class="text-primary text-uppercase small fw-bold mb-3">Booking Summary</h6>
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="row align-items-center">
                                    <div class="col-md-6 border-end">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="material-icons text-muted me-2">train</i>
                                            <span class="fw-bold">{{ $schedule->train->name }}</span>
                                            <span class="ms-2 badge bg-info">{{ $schedule->train->train_number }}</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="material-icons text-muted me-2">calendar_today</i>
                                            <span>{{ $schedule->departure->format('D, M d, Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 ps-md-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">From:</span>
                                            <span class="fw-bold text-end">{{ $schedule->from }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">To:</span>
                                            <span class="fw-bold text-end">{{ $schedule->to }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Seats Table -->
                        <div class="mb-4">
                            <h6 class="text-primary text-uppercase small fw-bold mb-3">Selected Seats</h6>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Seat</th>
                                            <th>Class</th>
                                            <th class="text-end">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($seatSummaries as $seat)
                                            <tr>
                                                <td><span class="badge bg-secondary">{{ $seat['number'] }}</span></td>
                                                <td>{{ $seat['class'] }}</td>
                                                <td class="text-end">LKR {{ number_format($seat['price'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-warning fw-bold fs-5">
                                            <td colspan="2" class="text-end">Grand Total</td>
                                            <td class="text-end text-warning">LKR {{ number_format($totalPrice, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Simulated Payment Form -->
                        <form id="paymentForm">
                            @csrf
                            <input type="hidden" name="schedule_id" value="{{ $scheduleId }}">
                            <input type="hidden" name="seat_ids" value="{{ implode(',', $seatIds) }}">

                            <h6 class="text-primary text-uppercase small fw-bold mb-3">Payment Information (Demo)</h6>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label small text-muted">Cardholder Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i
                                                class="material-icons text-muted">person</i></span>
                                        <input type="text" name="card_name" class="form-control" placeholder="John Doe"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small text-muted">Card Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i
                                                class="material-icons text-muted">credit_card</i></span>
                                        <input type="text" name="card_number" class="form-control"
                                            placeholder="0000 0000 0000 0000" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Expiry Date</label>
                                    <input type="text" class="form-control" placeholder="MM/YY" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small text-muted">CVV</label>
                                    <input type="password" class="form-control" placeholder="***" required>
                                </div>
                            </div>

                            <div class="mt-5">
                                <button type="submit" id="payBtn" class="btn btn-success w-100 py-3 fw-bold shadow-sm">
                                    <i class="material-icons align-middle me-2">lock</i> Confirm & Pay LKR
                                    {{ number_format($totalPrice, 2) }}
                                </button>
                                <a href="{{ route('booking.seats', $scheduleId) }}"
                                    class="btn btn-link w-100 mt-2 text-muted">
                                    Cancel and return to seat selection
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4 text-muted">
                    <small><i class="material-icons align-middle" style="font-size: 14px;">info</i> This is a demonstration
                        payment page. No actual charging will occur.</small>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#paymentForm').on('submit', function (e) {
                e.preventDefault();

                const btn = $('#payBtn');
                const originalText = btn.html();

                // Disable button and show loading
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Processing Payment...');

                // Simulate network delay for realism
                setTimeout(() => {
                    $.ajax({
                        url: "{{ route('payment.process') }}",
                        method: "POST",
                        data: $(this).serialize(),
                        success: function (response) {
                            if (response.success) {
                                window.location.href = response.redirect;
                            } else {
                                alert('Payment failed: ' + response.message);
                                btn.prop('disabled', false).html(originalText);
                            }
                        },
                        error: function (xhr) {
                            let msg = 'An error occurred. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            alert(msg);
                            btn.prop('disabled', false).html(originalText);
                        }
                    });
                }, 1500);
            });
        });
    </script>
@endsection