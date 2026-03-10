<!-- Edit Role Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="material-icons align-middle me-2">edit</i>Edit Role
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <div class="mb-3">
                        <label for="roleName" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="roleName" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div id="editPermissions" style="max-height: 400px; overflow-y: auto;">
                            <span class="spinner-border spinner-border-sm me-2"></span>Loading permissions...
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editForm" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>
