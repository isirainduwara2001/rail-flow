<!-- Create User Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    <div class="mb-3">
                        <label for="createUserName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="createUserName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="createUserEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="createUserEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="createUserPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="createUserPassword" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="createUserRole" class="form-label">Role</label>
                        <select class="form-select" id="createUserRole" name="role" required>
                            <option value="">Select a role...</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createUser()">Create User</button>
            </div>
        </div>
    </div>
</div>
