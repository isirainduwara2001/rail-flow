@extends('layouts.app')

@section('title', 'Payment Successful - RailFlow')

@section('content')
    <div class="container mt-5 mb-5">

        <div class="row justify-content-center">
            
            <div class="col-lg-7 text-center">
                <div class="card shadow-lg border-0 py-5">
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-inline-flex p-4 rounded-circle bg-success bg-opacity-10 mb-3">
                                <i class="material-icons text-success" style="font-size: 80px;">check_circle</i>
                            </div>
                            <h2 class="fw-bold text-dark">Payment Successful!</h2>
                            <p class="text-muted fs-5">Your train tickets have been confirmed.</p>
                        </div>

                        <div class="row justify-content-center mb-5">
                            <div class="col-md-10">
                                <div class="p-4 bg-light rounded-4 border border-dashed border-success">
                                    <h6 class="text-start text-success text-uppercase small fw-bold mb-3">Tickets Details
                                    </h6>

                                    @foreach($bookings as $booking)
                                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom text-start">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ $booking->booking_reference }}" alt="QR Code" class="img-thumbnail" style="width: 100px; height: 100px;">
                                                </div>
                                                <div>
                                                    <div class="fw-bold fs-5 text-dark">{{ $booking->schedule->train->name }}</div>
                                                    <div class="text-muted">{{ $booking->schedule->from }} → {{ $booking->schedule->to }}</div>
                                                    <div class="small fw-bold text-primary mt-1">
                                                        <i class="material-icons align-middle" style="font-size: 16px;">event</i>
                                                        {{ $booking->schedule->departure->format('M d, Y') }}
                                                        <i class="material-icons align-middle ms-2" style="font-size: 16px;">access_time</i>
                                                        {{ $booking->schedule->departure->format('H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success mb-2">Confirmed</span>
                                                <div class="fw-bold text-dark">Seat: {{ $booking->seat->seat_number }}</div>
                                                <div class="text-muted small">{{ $booking->seat->class }} Class</div>
                                                <div class="mt-2 text-primary font-monospace small">#{{ $booking->booking_reference }}</div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="d-flex justify-content-between pt-2">
                                        <span class="fw-bold">Total Paid</span>
                                        <span class="fw-bold text-success">LKR
                                            {{ number_format($bookings->sum('price'), 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-center">
                            <a href="{{ route('booking.my-bookings') }}" class="btn btn-success px-4 py-2 fw-bold">
                                <i class="material-icons align-middle me-1">history</i> View My Bookings
                            </a>
                            <a href="{{ route('booking.search') }}" class="btn btn-outline-primary px-4 py-2">
                                <i class="material-icons align-middle me-1">search</i> Book More Tickets
                            </a>
                        </div>
                    </div>
                </div>

                <p class="mt-4 text-muted">A confirmation email has been sent to {{ auth()->user()->email }}.</p>
            </div>
        </div>
    </div>
@endsection