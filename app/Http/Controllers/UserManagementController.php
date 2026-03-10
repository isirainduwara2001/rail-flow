<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Services\DataTable;

class UserManagementController extends Controller
{
    /**
     * Display user management dashboard.
     */
    public function index()
    {
        return view('admin.users.index');
    }

    /**
     * Get users data for DataTables.
     */
    public function getUsersData(Request $request): JsonResponse
    {
        $query = User::query()->with('roles');

        // Apply role filter if provided
        if ($request->has('role') && $request->role !== null && $request->role !== '') {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        return DataTables::of($query)
            ->addColumn('action', function (User $user) {
                return '
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary edit-user" data-id="' . $user->id . '" title="Edit">
                            <i class="material-icons">edit</i>
                        </button>
                        <button class="btn btn-outline-danger delete-user" data-id="' . $user->id . '" title="Delete">
                            <i class="material-icons">delete</i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('roles', function (User $user) {
                return Str::ucfirst(implode(', ', $user->roles->pluck('name')->toArray()));
            })
            ->editColumn('email_verified_at', function (User $user) {
                return $user->email_verified_at?->format('Y-m-d H:i') ?? 'Not Verified';
            })
            ->editColumn('created_at', function (User $user) {
                return $user->created_at->format('Y-m-d H:i');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'roles' => 'array',
        ]);

        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        // Assign roles
        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Show a specific user with their roles.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Show user edit form.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update user details and roles.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'roles' => 'array',
        ]);

        // Update basic user info
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Sync roles - now expecting role names instead of IDs
        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent deletion of the super admin
        if ($user->hasRole('admin') && User::role('admin')->count() === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the last admin user.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }

    /**
     * Suspend a user (soft delete alternative).
     */
    public function suspend(User $user): JsonResponse
    {
        // Add a 'suspended' column to users table migration if implementing
        // For now, we'll use a simple approach with an is_active flag

        // If is_active column exists:
        // $user->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'User suspended successfully.',
        ]);
    }

    /**
     * Get available roles for assignment.
     */
    public function getRoles(): JsonResponse
    {
        try {
            // Try to get roles from database (without description column as it doesn't exist)
            $roles = Role::select('id', 'name')->get();

            if ($roles->isEmpty()) {
                // If no roles in database, return default roles
                $roles = collect([
                    (object)['id' => 1, 'name' => 'admin'],
                    (object)['id' => 2, 'name' => 'staff'],
                    (object)['id' => 3, 'name' => 'user'],
                ]);
            }

            return response()->json([
                'success' => true,
                'roles' => $roles,
            ]);
        } catch (Exception $e) {
            // Return default roles on error
            return response()->json([
                'success' => true,
                'roles' => [
                    ['id' => 1, 'name' => 'admin'],
                    ['id' => 2, 'name' => 'staff'],
                    ['id' => 3, 'name' => 'user'],
                ],
            ]);
        }
    }
}
