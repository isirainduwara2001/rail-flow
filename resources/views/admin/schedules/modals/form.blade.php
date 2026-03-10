<!-- Add/Edit Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient border-0">
                <h5 class="modal-title fw-bold">
                    <i class="material-icons align-middle me-2">schedule</i>Add New Schedule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="trainId" class="form-label fw-5">Select Train</label>
                        <select class="form-select" id="trainId" name="train_id" required>
                            <option value="">-- Select Train --</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fromStation" class="form-label fw-5">From Station</label>
                            <input type="text" class="form-control" id="fromStation" name="from"
                                placeholder="e.g., Delhi" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="toStation" class="form-label fw-5">To Station</label>
                            <input type="text" class="form-control" id="toStation" name="to" placeholder="e.g., Mumbai"
                                required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="departureTime" class="form-label fw-5">Departure Date & Time</label>
                            <input type="datetime-local" class="form-control" id="departureTime" name="departure"
                                required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="arrivalTime" class="form-label fw-5">Arrival Date & Time</label>
                            <input type="datetime-local" class="form-control" id="arrivalTime" name="arrival" required>
                        </div>
                    </div>

                    <div class="mb-3" id="statusField" style="display: none;">
                        <label for="status" class="form-label fw-5">Schedule Status</label>
                        <select class="form-select border px-2" id="status" name="status">
                            <option value="scheduled">Scheduled</option>
                            <option value="delayed">Delayed</option>
                            <option value="departed">Departed</option>
                            <option value="arrived">Arrived</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <small class="text-info" id="delayWarning" style="display: none;">
                            <i class="material-icons align-middle text-xs">info</i>
                            Changing status to **Delayed** will send SMS notifications to all passengers.
                        </small>
                    </div>

                    <div class="alert alert-info mb-3">
                        <i class="material-icons align-middle me-2">info</i>
                        <strong>Note:</strong> Ticket prices are managed per seat class in the Train Ticket Prices
                        section.
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="material-icons align-middle">save</i> Save Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>