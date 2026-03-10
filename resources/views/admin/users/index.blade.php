@extends('layouts.app')

@section('title', 'User Management - RailFlow')

@section('content')

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="material-icons align-middle me-2">people</i>User Management
                    </h4>
                    <p class="text-muted mb-0">Manage and organize all system users</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal" onclick="loadRolesForCreate()">
                    <i class="material-icons align-middle me-2">add_circle</i>Add New User
                </button>
            </div>
        </div>

        <div class="card-body shadow-sm">
            <div class="align-items-center mb-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" id="roleFilter" onchange="reloadTable()">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff (Booking Manager)</option>
                            <option value="user">Registered User</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- DataTable -->
            <table id="usersTable" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Name</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Email</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Roles</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Status</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Joined</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

<!-- Include Modals -->
@include('admin.users.modals.create')
@include('admin.users.modals.edit')
@endsection

@section('scripts')
<script>
let currentUserId;
let usersTable;

$(document).ready(function () {
    initializeDataTable();
    loadRolesForCreate();

    // Load roles when create modal is shown
    $('#createModal').on('show.bs.modal', function () {
        loadRolesForCreate();
    });
});

function initializeDataTable() {
    usersTable = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('users.data') }}",
            data: function (d) {
                d.role = $('#roleFilter').val();
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'roles', name: 'roles' },
            {
                data: 'email_verified_at',
                render: function(data) {
                    return data ? '<span class="badge badge-success">Verified</span>' : '<span class="badge badge-warning">Unverified</span>';
                }
            },
            { data: 'created_at', name: 'created_at' },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="d-flex justify-content-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary edit-user mx-1 rounded" onclick="editUser(${row.id}, '${row.name}', '${row.email}')">
                                    <i class="material-icons">edit</i>
                                </button>
                                <button class="btn btn-outline-danger delete-user mx-1 rounded" onclick="deleteUser(${row.id})">
                                    <i class="material-icons">delete</i>
                                </button>
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });
}

function reloadTable() {
    usersTable.ajax.reload();
}

function loadRolesForCreate() {
    $.ajax({
        url: "{{ route('users.roles') }}",
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Roles loaded:', response);
            let options = '<option value="">Select a role...</option>';

            if (response && response.roles) {
                response.roles.forEach(role => {
                    options += `<option value="${role.name}">${role.name.charAt(0).toUpperCase() + role.name.slice(1)}</option>`;
                });
            }

            $('#createUserRole').html(options);
        },
        error: function(xhr, status, error) {
            console.error('Error loading roles:', error, xhr);
            $('#createUserRole').html('<option value="">Error loading roles</option>');
        }
    });
}

function createUser() {
    let role = $('#createUserRole').val();

    if (!role) {
        showNotification('Validation Error', 'Please select a role', 'error');
        return;
    }

    $.ajax({
        url: "{{ route('users.store') }}",
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            name: $('#createUserName').val(),
            email: $('#createUserEmail').val(),
            password: $('#createUserPassword').val(),
            roles: [role]
        },
        success: function(response) {
            if (response.success) {
                showNotification('Success', 'User created successfully', 'success');
                $('#createModal').modal('hide');
                $('#createForm')[0].reset();
                usersTable.ajax.reload();
            }
        },
        error: function(xhr) {
            showNotification('Error', 'Failed to create user: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
        }
    });
}

function editUser(id, name, email) {
    currentUserId = id;
    $('#userName').val(name);
    $('#userEmail').val(email);

    // Load roles for edit
    loadRolesForEdit();
    $('#editModal').modal('show');
}

function loadRolesForEdit() {
    $.ajax({
        url: "{{ route('users.roles') }}",
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            let options = '<option value="">Select a role...</option>';

            if (response && response.roles) {
                response.roles.forEach(role => {
                    options += `<option value="${role.name}">${role.name.charAt(0).toUpperCase() + role.name.slice(1)}</option>`;
                });
            }

            $('#userRole').html(options);

            // Load current user role and select it
            $.ajax({
                url: `/admin/users/${currentUserId}/show`,
                method: 'GET',
                success: function(userData) {
                    if (userData.user.roles && userData.user.roles.length > 0) {
                        $('#userRole').val(userData.user.roles[0].name);
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Error loading roles:', error);
            $('#userRole').html('<option value="">Error loading roles</option>');
        }
    });
}

function saveUser() {
    let role = $('#userRole').val();

    if (!role) {
        showNotification('Validation Error', 'Please select a role', 'error');
        return;
    }

    $.ajax({
        url: `/admin/users/${currentUserId}`,
        method: 'PUT',
        data: {
            _token: '{{ csrf_token() }}',
            name: $('#userName').val(),
            email: $('#userEmail').val(),
            roles: [role]
        },
        success: function(response) {
            if (response.success) {
                showNotification('Success', 'User updated successfully', 'success');
                $('#editModal').modal('hide');
                usersTable.ajax.reload();
            }
        },
        error: function(xhr) {
            showNotification('Error', 'Failed to update user: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
        }
    });
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        $.ajax({
            url: `/admin/users/${id}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Success', 'User deleted successfully', 'success');
                    usersTable.ajax.reload();
                }
            },
            error: function(xhr) {
                showNotification('Error', 'Failed to delete user: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
            }
        });
    }
}
</script>
@endsection
