<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class RoleManagementController extends Controller
{
    /**
     * Display roles management dashboard.
     */
    public function index()
    {
        return view('admin.roles.index');
    }

    /**
     * Get roles data for DataTables.
     */
    public function getRolesData(Request $request)
    {
        try {
            $roles = Role::with(['permissions', 'users'])
                ->select('roles.id', 'roles.name', 'roles.created_at');

            return DataTables::of($roles)
                ->addColumn('permissions_count', function (Role $role) {
                    return $role->permissions->count();
                })
                ->addColumn('users_count', function (Role $role) {
                    return $role->users->count();
                })
                ->addColumn('action', function (Role $role) {
                    // Don't allow deleting system roles
                    $deleteBtn = '';
                    if (!in_array($role->name, ['admin', 'staff', 'user'])) {
                        $deleteBtn = '<button class="btn btn-outline-danger delete-role" data-id="' . $role->id . '" title="Delete">
                            <i class="material-icons">delete</i>
                        </button>';
                    }

                    return '
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-primary edit-role" data-id="' . $role->id . '" title="Edit">
                                <i class="material-icons">edit</i>
                            </button>
                            ' . $deleteBtn . '
                        </div>
                    ';
                })
                ->editColumn('created_at', function (Role $role) {
                    return $role->created_at->format('Y-m-d H:i');
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('getRolesData error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error loading roles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new role.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
        ]);

        // Create the role
        $role = Role::create([
            'name' => $validated['name'],
        ]);

        // Assign permissions
        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.',
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Show a specific role with its permissions.
     */
    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'success' => true,
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Update role details and permissions.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        // Update role name
        $role->update([
            'name' => $validated['name'],
        ]);

        // Sync permissions
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Delete a role.
     */
    public function destroy(Role $role): JsonResponse
    {
        // Prevent deletion of system roles
        if (in_array($role->name, ['admin', 'staff', 'user'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete system roles.',
            ], 422);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a role that has users assigned to it.',
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.',
        ]);
    }

    /**
     * Get available permissions for assignment.
     */
    public function getPermissions(): JsonResponse
    {
        try {
            // Get all permissions grouped by category
            $permissionsQuery = Permission::select('id', 'name')
                ->orderBy('name')
                ->get();

            if ($permissionsQuery->isEmpty()) {
                throw new Exception('No permissions found');
            }

            // Group by module and convert to array
            $grouped = [];
            foreach ($permissionsQuery as $perm) {
                $module = explode('.', $perm->name)[0];
                if (!isset($grouped[$module])) {
                    $grouped[$module] = [];
                }
                $grouped[$module][] = [
                    'id' => $perm->id,
                    'name' => $perm->name
                ];
            }

            return response()->json([
                'success' => true,
                'permissions' => $grouped,
            ]);
        } catch (Exception $e) {
            // Return default permissions on error
            $defaultPermissions = [
                'users' => [
                    ['id' => 1, 'name' => 'users.view'],
                    ['id' => 2, 'name' => 'users.create'],
                    ['id' => 3, 'name' => 'users.edit'],
                    ['id' => 4, 'name' => 'users.delete'],
                ],
                'roles' => [
                    ['id' => 5, 'name' => 'roles.view'],
                    ['id' => 6, 'name' => 'roles.create'],
                    ['id' => 7, 'name' => 'roles.edit'],
                    ['id' => 8, 'name' => 'roles.delete'],
                ],
                'trains' => [
                    ['id' => 9, 'name' => 'trains.view'],
                    ['id' => 10, 'name' => 'trains.create'],
                    ['id' => 11, 'name' => 'trains.edit'],
                    ['id' => 12, 'name' => 'trains.delete'],
                ],
                'schedules' => [
                    ['id' => 13, 'name' => 'schedules.view'],
                    ['id' => 14, 'name' => 'schedules.create'],
                    ['id' => 15, 'name' => 'schedules.edit'],
                    ['id' => 16, 'name' => 'schedules.delete'],
                ],
                'bookings' => [
                    ['id' => 17, 'name' => 'bookings.view'],
                    ['id' => 18, 'name' => 'bookings.create'],
                    ['id' => 19, 'name' => 'bookings.edit'],
                    ['id' => 20, 'name' => 'bookings.delete'],
                ],
            ];

            return response()->json([
                'success' => true,
                'permissions' => $defaultPermissions,
            ]);
        }
    }
}
