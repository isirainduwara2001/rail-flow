@extends('layouts.app')

@section('title', 'Role Management - RailFlow')

@section('content')

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="material-icons align-middle me-2">security</i>Role Management
                    </h4>
                    <p class="text-muted mb-0">Manage roles and permissions for all users</p>
                </div>
                <button class="btn btn-primary" id="createRoleBtn" onclick="createRoleClick()">
                    <i class="material-icons align-middle me-2">add_circle</i>Add New Role
                </button>
            </div>
        </div>

        <div class="card-body shadow-sm">
            <!-- DataTable -->
            <table id="rolesTable" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Role Name</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">
                            <i class="material-icons align-middle" style="font-size: 1rem;">verified_user</i>Permissions
                        </th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">
                            <i class="material-icons align-middle" style="font-size: 1rem;">people</i>Users
                        </th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7">Created Date</th>
                        <th class="text-uppercase text-xs font-weight-bolder opacity-7 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

<!-- Include Modals -->
@include('admin.roles.modals.create')
@include('admin.roles.modals.edit')

@endsection

@section('scripts')
<script>
let currentRoleId;
let rolesTable;

$(document).ready(function () {
    initializeDataTable();
});

function initializeDataTable() {
    rolesTable = $('#rolesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('roles.data') }}",
            type: 'GET',
            dataSrc: 'data',
            error: function(xhr, status, error) {
                console.error('DataTable AJAX error:', error);
                console.error('Response:', xhr.responseText);
                showNotification('Error', 'Failed to load roles', 'error');
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'permissions_count', name: 'permissions_count' },
            { data: 'users_count', name: 'users_count' },
            { data: 'created_at', name: 'created_at' },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-sm btn-outline-primary edit-role" data-id="${row.id}">
                                <i class="material-icons">edit</i>
                            </button>
                            ${!['admin', 'staff', 'user'].includes(row.name) ? `
                            <button class="btn btn-sm btn-outline-danger delete-role ms-2" data-id="${row.id}">
                                <i class="material-icons">delete</i>
                            </button>
                            ` : ''}
                        </div>
                    `;
                }
            }
        ],
        columnDefs: [
            {
                targets: 1,
                render: function(data) {
                    return `<span class="badge bg-info">${data}</span>`;
                }
            },
            {
                targets: 2,
                render: function(data) {
                    return `<span class="badge bg-success">${data}</span>`;
                }
            }
        ]
    });
}

function createRoleClick() {
    currentRoleId = null;
    $('#createForm')[0].reset();
    loadPermissionsForCreate();
    $('#createModal').modal('show');
}

$(document).on('click', '.edit-role', function() {
    currentRoleId = $(this).data('id');
    loadPermissionsForEdit();
    loadRoleForEdit();
    $('#editModal').modal('show');
});

$(document).on('click', '.delete-role', function() {
    let roleId = $(this).data('id');
    let roleName = $(this).closest('tr').find('td:first').text();

    if (confirm(`Are you sure you want to delete the role "${roleName}"? This action cannot be undone.`)) {
        deleteRole(roleId);
    }
});

$('#createForm').on('submit', function(e) {
    e.preventDefault();
    createRole();
});

$('#editForm').on('submit', function(e) {
    e.preventDefault();
    saveRole();
});

function loadPermissionsForCreate() {
    $.ajax({
        url: "{{ route('roles.permissions') }}",
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            let html = '';

            if (response.permissions) {
                for (const [module, perms] of Object.entries(response.permissions)) {
                    html += `<div class="mb-3">
                        <h6 class="text-uppercase mt-3 mb-2">${module}</h6>`;

                    perms.forEach(permission => {
                        html += `<div class="form-check">
                            <input class="form-check-input" type="checkbox" value="${permission.name}"
                                id="createPerm${permission.id}" name="permissions[]">
                            <label class="form-check-label" for="createPerm${permission.id}">
                                ${permission.name}
                            </label>
                        </div>`;
                    });

                    html += '</div>';
                }
            }

            $('#createPermissions').html(html);
        },
        error: function() {
            showNotification('Error', 'Failed to load permissions', 'error');
        }
    });
}

function loadPermissionsForEdit() {
    $.ajax({
        url: "{{ route('roles.permissions') }}",
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            let html = '';

            if (response.permissions) {
                for (const [module, perms] of Object.entries(response.permissions)) {
                    html += `<div class="mb-3">
                        <h6 class="text-uppercase mt-3 mb-2">${module}</h6>`;

                    perms.forEach(permission => {
                        html += `<div class="form-check">
                            <input class="form-check-input" type="checkbox" value="${permission.name}"
                                id="editPerm${permission.id}" name="permissions[]">
                            <label class="form-check-label" for="editPerm${permission.id}">
                                ${permission.name}
                            </label>
                        </div>`;
                    });

                    html += '</div>';
                }
            }

            $('#editPermissions').html(html);
        },
        error: function() {
            showNotification('Error', 'Failed to load permissions', 'error');
        }
    });
}

function loadRoleForEdit() {
    $.ajax({
        url: `/admin/roles/${currentRoleId}/show`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const role = response.role;
                $('#roleName').val(role.name);

                $('#editForm input[name="permissions[]"]').prop('checked', false);

                if (role.permissions && role.permissions.length > 0) {
                    role.permissions.forEach(permission => {
                        $(`#editForm input[value="${permission.name}"]`).prop('checked', true);
                    });
                }
            }
        },
        error: function(xhr) {
            showNotification('Error', 'Failed to load role: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
        }
    });
}

function createRole() {
    let roleName = $('#createRoleName').val();
    let permissions = $('#createForm input[name="permissions[]"]:checked').map(function() {
        return this.value;
    }).get();

    if (!roleName) {
        showNotification('Validation Error', 'Please enter a role name', 'error');
        return;
    }

    $.ajax({
        url: '{{ route("roles.store") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            name: roleName,
            permissions: permissions
        },
        success: function(response) {
            if (response.success) {
                showNotification('Success', `Role "${roleName}" created successfully`, 'success');
                $('#createModal').modal('hide');
                rolesTable.ajax.reload();
            }
        },
        error: function(xhr) {
            if (xhr.responseJSON?.message) {
                showNotification('Error', xhr.responseJSON.message, 'error');
            } else {
                showNotification('Error', 'Failed to create role', 'error');
            }
        }
    });
}

function saveRole() {
    let roleName = $('#roleName').val();
    let permissions = $('#editForm input[name="permissions[]"]:checked').map(function() {
        return this.value;
    }).get();

    if (!roleName) {
        showNotification('Validation Error', 'Please enter a role name', 'error');
        return;
    }

    $.ajax({
        url: `/admin/roles/${currentRoleId}`,
        method: 'PUT',
        data: {
            _token: '{{ csrf_token() }}',
            name: roleName,
            permissions: permissions
        },
        success: function(response) {
            if (response.success) {
                showNotification('Success', `Role "${roleName}" updated successfully`, 'success');
                $('#editModal').modal('hide');
                rolesTable.ajax.reload();
            }
        },
        error: function(xhr) {
            showNotification('Error', 'Failed to update role: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
        }
    });
}

function deleteRole(roleId) {
    $.ajax({
        url: `/admin/roles/${roleId}`,
        method: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showNotification('Success', 'Role deleted successfully', 'success');
                rolesTable.ajax.reload();
            }
        },
        error: function(xhr) {
            showNotification('Error', 'Failed to delete role: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
        }
    });
}
</script>
@endsection
