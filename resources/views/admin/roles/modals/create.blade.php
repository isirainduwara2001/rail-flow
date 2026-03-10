<!-- Create Role Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="material-icons align-middle me-2">add_circle</i>Create New Role
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    <div class="mb-3">
                        <label for="createRoleName" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="createRoleName"
                            placeholder="Enter role name (e.g., moderator, operator)" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div id="createPermissions" style="max-height: 400px; overflow-y: auto;">
                            <span class="spinner-border spinner-border-sm me-2"></span>Loading permissions...
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="createForm" class="btn btn-primary">Create Role</button>
            </div>
        </div>
    </div>
</div>
