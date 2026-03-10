@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1">
                                    <i class="material-icons align-middle me-2">train</i>Passenger Informs
                                </h4>
                                <p class="text-muted mb-0">Manage and organize all passenger informs</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-3 mb-3">
                        <form action="{{ route('passenger-informs.index') }}" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label text-xs">Train</label>
                                <select name="train_id" class="form-select border px-2">
                                    <option value="">All Trains</option>
                                    @foreach($trains as $train)
                                        <option value="{{ $train->id }}" {{ request('train_id') == $train->id ? 'selected' : '' }}>
                                            {{ $train->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-xs">From Station</label>
                                <input type="text" name="from" value="{{ request('from') }}" class="form-control border px-2" placeholder="Station name...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-xs">To Station</label>
                                <input type="text" name="to" value="{{ request('to') }}" class="form-control border px-2" placeholder="Station name...">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mb-0 me-2 btn-sm">Filter</button>
                                <a href="{{ route('passenger-informs.index') }}" class="btn btn-outline-secondary mb-0 btn-sm">Clear</a>
                            </div>
                        </form>
                    </div>

                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Train</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Schedule</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Passengers</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Current Status</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $schedule)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex px-3 py-1">
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <h6 class="mb-0 text-sm">{{ $schedule->train->name }}</h6>
                                                                        <p class="text-xs text-secondary mb-0">
                                                                            {{ $schedule->train->train_number }}</p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ $schedule->from }} <i
                                                                        class="material-icons text-xs">arrow_forward</i> {{ $schedule->to }}</p>
                                                                <p class="text-xs text-secondary mb-0">
                                                                    {{ $schedule->departure->format('M d, H:i') }}</p>
                                                            </td>
                                                            <td class="align-middle text-center text-sm">
                                                                <span
                                                                    class="badge badge-sm bg-info">{{ $schedule->passenger_count }}</span>
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                @php
                                                                    $colors = ['scheduled' => 'info', 'delayed' => 'warning', 'departed' => 'primary', 'arrived' => 'success', 'cancelled' => 'danger'];
                                                                    $color = $colors[$schedule->status] ?? 'secondary';
                                                                @endphp
                                          <span
                                                                    class="badge badge-sm bg-{{ $color }}">{{ strtoupper($schedule->status) }}</span>
                                                            </td>
                                                            <td class="align-middle">
                                                                <button
                                                                    class="btn btn-link text-primary font-weight-bold text-xs mb-0 inform-btn"
                                                                    data-id="{{ $schedule->id }}" data-train="{{ $schedule->train->name }}"
                                                                    data-from="{{ $schedule->from }}" data-to="{{ $schedule->to }}"
                                                                    data-departure="{{ $schedule->departure->format('H:i') }}"
                                                                    data-passengers="{{ $schedule->passenger_count }}">
                                                                    Inform
                                                                </button>
                                                            </td>
                                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-normal" id="notificationModalLabel">Send Passenger Notification</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="notificationForm">
                        <input type="hidden" id="schedule_id" name="schedule_id">
                        <div class="mb-3">
                            <label class="form-label">Message Template</label>
                            <select class="form-select border px-2" id="msgTemplate">
                                <option value="">Custom Message</option>
                                <option value="delay">Train Delayed Alert</option>
                                <option value="ontime">Train On Track</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message Body</label>
                            <textarea class="form-control border px-2" id="message" name="message" rows="4"
                                placeholder="Type your message here..." required maxlength="160"></textarea>
                            <div class="text-end">
                                <small id="charCount" class="text-secondary">0/160</small>
                            </div>
                        </div>
                        <div class="alert alert-light border mb-0 text-sm">
                            <i class="material-icons align-middle me-2">info</i>
                            This message will be sent to <strong id="passengerCountText">0</strong> passengers via SMS.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn bg-gradient-primary" id="sendBtn">Send Message</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            const modal = new bootstrap.Modal(document.getElementById('notificationModal'));

            $('.inform-btn').click(function () {
                const data = $(this).data();
                $('#schedule_id').val(data.id);
                $('#passengerCountText').text(data.passengers);
                $('#message').val('');
                $('#charCount').text('0/160');
                modal.show();
            });

            $('#msgTemplate').change(function () {
                const template = $(this).val();
                const btn = $(`.inform-btn[data-id="${$('#schedule_id').val()}"]`);
                const data = btn.data();

                let msg = '';
                if (template === 'delay') {
                    msg = `RailFlow Alert: Your train ${data.train} (${data.departure}) from ${data.from} to ${data.to} has been DELAYED. We apologize for the inconvenience.`;
                } else if (template === 'ontime') {
                    msg = `RailFlow Info: Train ${data.train} is on schedule. Departure at ${data.departure} from ${data.from}. Thank you for choosing RailFlow.`;
                }

                $('#message').val(msg);
                $('#charCount').text(`${msg.length}/160`);
            });

            $('#message').on('input', function () {
                $('#charCount').text(`${$(this).val().length}/160`);
            });

            $('#sendBtn').click(function () {
                const message = $('#message').val();
                if (!message) {
                    alert('Please enter a message.');
                    return;
                }

                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');

                $.ajax({
                    url: "{{ route('passenger-informs.send') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        schedule_id: $('#schedule_id').val(),
                        message: message
                    },
                    success: function (response) {
                        modal.hide();
                        showNotification('Success', response.message, 'success');
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error sending notification';
                        showNotification('Error', msg, 'error');
                    },
                    complete: function () {
                        $('#sendBtn').prop('disabled', false).text('Send Message');
                    }
                });
            });

            function showNotification(title, message, type) {
                // Assuming global notify function exists, otherwise alert
                if (typeof Swal !== 'undefined') {
                    Swal.fire(title, message, type);
                } else {
                    alert(message);
                }
            }
        });
    </script>
@endsection