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
                                    <div class="invalid-feedback" id="nameError"></div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small text-muted">Card Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i
                                                class="material-icons text-muted">credit_card</i></span>
                                        <input type="text" name="card_number" class="form-control"
                                            placeholder="0000 0000 0000 0000" maxlength="19" required>
                                    </div>
                                    <div class="invalid-feedback" id="cardError"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Expiry Date</label>
                                    <input type="text" name="expiry_date" class="form-control" placeholder="MM/YY" maxlength="5" required>
                                    <div class="invalid-feedback" id="expiryError"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small text-muted">CVV</label>
                                    <input type="password" name="cvv" class="form-control" placeholder="***" maxlength="3" required>
                                    <div class="invalid-feedback" id="cvvError"></div>
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
            // Card Number formatting - auto-add spaces and limit to 16 digits
            $('input[name="card_number"]').on('input', function(e) {
                let value = $(this).val().replace(/\D/g, '');
                value = value.substring(0, 16);
                let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
                $(this).val(formatted);
            });

            // Expiry Date formatting - auto-add slash and limit to MM/YY
            $('input[name="expiry_date"]').on('input', function(e) {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                $(this).val(value);
            });

            // CVV - only allow 3 digits
            $('input[name="cvv"]').on('input', function(e) {
                let value = $(this).val().replace(/\D/g, '');
                value = value.substring(0, 3);
                $(this).val(value);
            });

            // Block paste of invalid characters
            $('input[name="card_number"], input[name="cvv"]').on('paste', function(e) {
                e.preventDefault();
                let text = (e.originalEvent?.clipboardData || window.clipboardData).getData('text');
                text = text.replace(/\D/g, '').substring(0, $(this).attr('maxlength') === '3' ? 3 : 16);
                $(this).val(text);
            });

            $('input[name="expiry_date"]').on('paste', function(e) {
                e.preventDefault();
                let text = (e.originalEvent?.clipboardData || window.clipboardData).getData('text');
                text = text.replace(/\D/g, '').substring(0, 4);
                if (text.length >= 2) {
                    text = text.substring(0, 2) + '/' + text.substring(2, 4);
                }
                $(this).val(text);
            });

            // Function to validate the form
            function validateForm() {
                let isValid = true;

                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                // Cardholder Name
                const name = $('input[name="card_name"]').val().trim();
                if (name === '') {
                    $('#nameError').text('Cardholder name is required.');
                    $('input[name="card_name"]').addClass('is-invalid');
                    isValid = false;
                }

                // Card Number
                const cardNumber = $('input[name="card_number"]').val().replace(/\s/g, '');
                if (!/^\d{16}$/.test(cardNumber)) {
                    $('#cardError').text('Card number must be 16 digits.');
                    $('input[name="card_number"]').addClass('is-invalid');
                    isValid = false;
                }

                // Expiry Date
                const expiry = $('input[name="expiry_date"]').val();
                const expiryRegex = /^\d{2}\/\d{2}$/;
                if (!expiryRegex.test(expiry)) {
                    $('#expiryError').text('Expiry date must be in MM/YY format.');
                    $('input[name="expiry_date"]').addClass('is-invalid');
                    isValid = false;
                } else {
                    const [month, year] = expiry.split('/');
                    const monthNum = parseInt(month, 10);
                    const yearNum = parseInt(year, 10) + 2000; // Assuming 20xx
                    const currentYear = new Date().getFullYear();
                    const currentMonth = new Date().getMonth() + 1;
                    if (monthNum < 1 || monthNum > 12) {
                        $('#expiryError').text('Invalid month.');
                        $('input[name="expiry_date"]').addClass('is-invalid');
                        isValid = false;
                    } else if (yearNum < currentYear || (yearNum === currentYear && monthNum < currentMonth)) {
                        $('#expiryError').text('Expiry date must be in the future.');
                        $('input[name="expiry_date"]').addClass('is-invalid');
                        isValid = false;
                    }
                }

                // CVV
                const cvv = $('input[name="cvv"]').val();
                if (!/^\d{3}$/.test(cvv)) {
                    $('#cvvError').text('CVV must be 3 digits.');
                    $('input[name="cvv"]').addClass('is-invalid');
                    isValid = false;
                }

                return isValid;
            }

            $('#paymentForm').on('submit', function (e) {
                e.preventDefault();

                if (!validateForm()) {
                    return; // Stop if validation fails
                }

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