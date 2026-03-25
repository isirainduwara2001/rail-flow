<!-- Add/Edit Train Modal -->
<div class="modal fade" id="trainModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient border-0">
                <h5 class="modal-title fw-bold">
                    <i class="material-icons align-middle me-2">train</i><span>Add New Train</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="trainForm">
                @csrf
                <input type="hidden" id="trainId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="trainName" class="form-label fw-5">Train Name</label>
                            <input type="text" class="form-control" id="trainName" name="name" placeholder="e.g., Rajdhani Express" required>
                            <small class="text-muted">Display name for the train</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="trainNumber" class="form-label fw-5">Train Number</label>
                            <input type="text" class="form-control" id="trainNumber" name="train_number" placeholder="e.g., TR001" required>
                            <small class="text-muted">Unique train identifier</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="trainCapacity" class="form-label fw-5">Total Seats</label>
                        <input type="number" class="form-control" id="trainCapacity" name="total_seats" min="10" max="500" placeholder="e.g., 100" required>
                        <small class="text-muted">Total number of seats in the train</small>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="material-icons align-middle">save</i> <span>Save Train</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
