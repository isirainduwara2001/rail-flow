@extends('layouts.app')

@section('title', 'Select Seats - Train Booking')

@section('content')
    <style>
        .train-coach {
            background: linear-gradient(to bottom, #1e293b 0%, #0f172a 100%);
            border: 3px solid #334155;
            border-radius: 20px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .train-front {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .seat-grid-wrapper {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            margin: 20px 0;
        }

        .seat-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            justify-content: center;
            margin-bottom: 10px;
        }

        .row-label {
            font-weight: bold;
            color: #94a3b8;
            font-size: 16px;
            width: 25px;
            text-align: center;
            text-transform: uppercase;
        }

        .seat-btn {
            width: 45px;
            height: 45px;
            border: 2px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            font-weight: bold;
            transition: all 0.2s ease;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .seat-btn:hover:not(:disabled) {
            transform: scale(1.15);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
            z-index: 10;
        }

        .seat-btn.selected {
            border-width: 3px;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.4), inset 0 0 10px rgba(0, 0, 0, 0.2);
            transform: scale(1.05);
        }

        .seat-btn:disabled {
            cursor: not-allowed;
            opacity: 0.7;
            background: #6b7280 !important;
            border-color: #4b5563 !important;
            color: #374151 !important;
        }

        /* Seat class colors */
        .seat-economy {
            background: #10b981;
            color: white;
            border-color: #059669;
        }

        .seat-economy:hover:not(:disabled) {
            background: #059669;
        }

        .seat-business {
            background: #3b82f6;
            color: white;
            border-color: #1d4ed8;
        }

        .seat-business:hover:not(:disabled) {
            background: #1d4ed8;
        }

        .seat-first-class {
            background: #f59e0b;
            color: white;
            border-color: #d97706;
        }

        .seat-first-class:hover:not(:disabled) {
            background: #d97706;
        }

        .seat-premium {
            background: #ec4899;
            color: white;
            border-color: #be185d;
        }

        .seat-premium:hover:not(:disabled) {
            background: #be185d;
        }

        .aisle-label {
            color: #64748b;
            font-size: 12px;
            font-weight: bold;
            margin: 0 8px;
        }

        /* Assigned/Booked seat styling */
        .seat-assigned {
            background: #6b7280 !important;
            color: #374151 !important;
            border-color: #4b5563 !important;
            opacity: 0.7;
            cursor: not-allowed !important;
        }

        .seat-assigned:hover {
            background: #6b7280 !important;
        }

        .seat-legend {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .legend-box {
            width: 35px;
            height: 35px;
            border-radius: 6px;
            border: 2px solid #ddd;
            flex-shrink: 0;
        }

        .schedule-info {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1px solid #38bdf8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .schedule-info p {
            margin: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .schedule-info strong {
            color: #0369a1;
        }
    </style>

    <div class="container-fluid mt-4 mb-5">
        <div class="row">
            <div class="col-lg-9">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-gradient"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h5 class="mb-0"><i class="material-icons align-middle">event_seat</i> Select Your Seats</h5>
                    </div>
                    <div class="card-body">
                        <!-- Schedule Details -->
                        <div class="schedule-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><i class="material-icons"
                                            style="font-size: 20px; color: #0369a1;">location_on</i><span><strong>From:</strong>
                                            <span id="fromStation" class="text-primary">-</span></span></p>
                                    <p><i class="material-icons"
                                            style="font-size: 20px; color: #0369a1;">location_on</i><span><strong>To:</strong>
                                            <span id="toStation" class="text-primary">-</span></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><i class="material-icons"
                                            style="font-size: 20px; color: #0369a1;">schedule</i><span><strong>Departure:</strong>
                                            <span id="departure">-</span></span></p>
                                    <p><i class="material-icons"
                                            style="font-size: 20px; color: #0369a1;">access_time</i><span><strong>Arrival:</strong>
                                            <span id="arrival">-</span></span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Seat Legend -->
                        <div class="mb-4">
                            <h6 class="mb-3" style="color: #1f2937;"><strong>Seat Classes & Pricing</strong></h6>
                            <div class="seat-legend" id="classLegend">
                                <div class="text-muted">Loading seat classes...</div>
                            </div>
                        </div>

                        <!-- Train Header -->
                        <div class="train-coach">
                            <div class="train-front">🚄 TRAIN COACH</div>
                        </div>

                        <!-- Train Seat Layout -->
                        <div class="seat-grid-wrapper">
                            <p class="text-center text-muted mb-4">
                                <i class="material-icons" style="font-size: 18px; vertical-align: middle;">info</i>
                                <small>Click seats to select | Green = Available | Gray = Booked</small>
                            </p>
                            <div id="seatLayout">
                                <p class="text-center text-muted">Loading seat layout...</p>
                            </div>
                        </div>

                        <!-- Train Footer -->
                        <div class="train-coach">
                            <div style="font-size: 20px;">🚪 EXIT</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Summary Sidebar -->
            <div class="col-lg-3">
                <div class="card shadow-lg border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-gradient"
                        style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                        <h5 class="mb-0"><i class="material-icons align-middle">shopping_cart</i> Booking Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 p-3"
                            style="background: #f0fdf4; border-radius: 8px; border-left: 4px solid #10b981;">
                            <p class="mb-2"><strong style="color: #065f46;">Selected Seats</strong></p>
                            <p class="h3 text-success" id="selectedCount">0</p>
                        </div>

                        <div class="mb-4">
                            <h6 class="mb-3" style="color: #374151;"><strong>Seat Breakdown</strong></h6>
                            <div id="seatClassSummary" style="max-height: 250px; overflow-y: auto;">
                                <small class="text-muted">No seats selected yet</small>
                            </div>
                        </div>

                        <hr />

                        <div class="mb-4 p-3"
                            style="background: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b;">
                            <p class="mb-2"><strong style="color: #92400e;">Total Price</strong></p>
                            <p class="h4 text-warning" id="totalPrice">LKR 0</p>
                        </div>

                        <button type="button" id="proceedBtn" class="btn btn-success w-100 py-3 fw-bold" disabled>
                            <i class="material-icons align-middle">arrow_forward</i> Proceed to Checkout
                        </button>

                        <button type="button" class="btn btn-outline-secondary w-100 mt-2" onclick="window.history.back()">
                            <i class="material-icons align-middle">arrow_back</i> Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
            const scheduleId = {{ $schedule->id }};
            let selectedSeats = {};
            let seatClassPrices = {};

            $(document).ready(function () {
                // Handle "Proceed to Checkout" click
                $('#proceedBtn').click(function () {
                    const seatIds = Object.keys(selectedSeats).join(',');
                    if (seatIds) {
                        window.location.href = `/payment/checkout?schedule_id=${scheduleId}&seat_ids=${seatIds}`;
                    }
                });

                // Get CSS class for seat color based on class type and assignment status
                function getSeatColorClass(seatClass, isAssigned = false) {
                    if (isAssigned) return 'seat-assigned';
                    const classMap = {
                        'Economy': 'seat-economy',
                        'Business': 'seat-business',
                        'First': 'seat-first-class',
                        'Premium': 'seat-premium'
                    };
                    return classMap[seatClass] || 'seat-economy';
                }

                // Update booking summary
                function updateSummary() {
                    const count = Object.keys(selectedSeats).length;
                    let total = 0;
                    const classCount = {};

                    Object.values(selectedSeats).forEach(seat => {
                        total += parseFloat(seat.price);
                        classCount[seat.class] = (classCount[seat.class] || 0) + 1;
                    });

                    $('#selectedCount').text(count);
                    $('#totalPrice').text(`LKR ${total.toLocaleString('en-US', { minimumFractionDigits: 0 })}`);

                    let classHtml = '';
                    Object.entries(classCount).forEach(([cls, cnt]) => {
                        const price = seatClassPrices[cls] || 0;
                        const totalPrice = price * cnt;
                        classHtml += `
                                    <div class="mb-3 p-2" style="background: white; border-radius: 6px; border-left: 3px solid #10b981;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small><strong>${cls}</strong></small>
                                            <small class="badge bg-info">${cnt}</small>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2 pt-2" style="border-top: 1px solid #e5e7eb;">
                                            <small>LKR ${price.toLocaleString('en-US', { minimumFractionDigits: 0 })} × ${cnt}</small>
                                            <strong class="text-success">LKR ${totalPrice.toLocaleString('en-US', { minimumFractionDigits: 0 })}</strong>
                                        </div>
                                    </div>
                                `;
                    });
                    $('#seatClassSummary').html(classHtml || '<small class="text-muted">No seats selected yet</small>');
                    $('#proceedBtn').prop('disabled', count === 0);
                }

                // Fetch seat data and build layout
                $.ajax({
                    url: `/booking/schedule/${scheduleId}/seat-data`,
                    method: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.success && response.seats && response.seats.length > 0) {
                            const schedule = response.schedule;
                            const seats = response.seats;
                            const seatClasses = response.seat_classes;

                            // Update schedule info
                            $('#fromStation').text(schedule.from);
                            $('#toStation').text(schedule.to);
                            $('#departure').text(new Date(schedule.departure).toLocaleString('en-US', { dateStyle: 'short', timeStyle: 'short' }));
                            $('#arrival').text(new Date(schedule.arrival).toLocaleString('en-US', { dateStyle: 'short', timeStyle: 'short' }));

                            // Build legend
                            let legendHtml = '';
                            seatClasses.forEach(cls => {
                                seatClassPrices[cls.class_name] = parseFloat(cls.price);
                                legendHtml += `
                                            <div class="legend-item">
                                                <div class="legend-box ${getSeatColorClass(cls.class_name)}"></div>
                                                <small><strong>${cls.class_name}</strong><br/>LKR ${parseInt(cls.price).toLocaleString('en-US')}</small>
                                            </div>
                                        `;
                            });
                            legendHtml += `
                                        <div class="legend-item"><div class="legend-box" style="background: white; border: 2px solid #10b981;"></div><small><strong>Available</strong></small></div>
                                        <div class="legend-item"><div class="legend-box" style="background: #d1d5db; border: 2px solid #9ca3af;"></div><small><strong>Booked</strong></small></div>
                                    `;
                            $('#classLegend').html(legendHtml);

                            // Build seat grid
                            let seatHtml = '';
                            const seatsPerRow = 4;
                            for (let i = 0; i < seats.length; i += seatsPerRow) {
                                seatHtml += '<div class="seat-row">';
                                seatHtml += `<div class="row-label">${String.fromCharCode(65 + (i / seatsPerRow))}</div>`;

                                // Left 2 seats
                                seatHtml += '<div style="display: flex; gap: 8px;">';
                                for (let j = i; j < i + 2 && j < seats.length; j++) {
                                    const seat = seats[j];
                                    const isBooked = !seat.available;
                                    const colorClass = getSeatColorClass(seat.class, isBooked);
                                    seatHtml += `
                                                <button type="button" class="seat-btn ${colorClass}" data-id="${seat.id}" data-number="${seat.number}" data-class="${seat.class}" data-price="${seat.price}" ${isBooked ? 'disabled' : ''} title="${seat.number} - LKR ${parseInt(seat.price).toLocaleString()}">
                                                    ${seat.number}
                                                </button>`;
                                }
                                seatHtml += '</div>';

                                seatHtml += '<div class="aisle-label" style="width: 30px; border-right: 3px dashed #475569; border-left: 3px dashed #475569; height: 40px;"></div>';

                                // Right 2 seats
                                seatHtml += '<div style="display: flex; gap: 8px;">';
                                for (let j = i + 2; j < i + 4 && j < seats.length; j++) {
                                    const seat = seats[j];
                                    const isBooked = !seat.available;
                                    const colorClass = getSeatColorClass(seat.class, isBooked);
                                    seatHtml += `
                                                <button type="button" class="seat-btn ${colorClass}" data-id="${seat.id}" data-number="${seat.number}" data-class="${seat.class}" data-price="${seat.price}" ${isBooked ? 'disabled' : ''} title="${seat.number} - LKR ${parseInt(seat.price).toLocaleString()}">
                                                    ${seat.number}
                                                </button>`;
                                }
                                seatHtml += '</div></div>';
                            }
                            $('#seatLayout').html(seatHtml);

                            // handlers
                            $(document).off('click', '.seat-btn:not(:disabled)').on('click', '.seat-btn:not(:disabled)', function () {
                                const seatId = $(this).data('id');
                                if (selectedSeats[seatId]) {
                                    delete selectedSeats[seatId];
                                    $(this).removeClass('selected');
                                } else {
                                    selectedSeats[seatId] = { number: $(this).data('number'), class: $(this).data('class'), price: $(this).data('price') };
                                    $(this).addClass('selected');
                                }
                                updateSummary();
                            });
                        } else {
                            $('#seatLayout').html('<p class="text-danger text-center"><strong>No seats available for this schedule</strong></p>');
                        }
                    },
                    error: function () {
                        $('#seatLayout').html('<p class="text-danger text-center"><strong>Error loading seat data</strong></p>');
                    }
                });
            });
        </script>
    @endsection

@endsection