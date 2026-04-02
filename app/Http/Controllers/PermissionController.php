<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\View\View;
use App\Services\PermissionLayoutService;
use App\Http\Requests\UserRequest;
use App\Enums\FormMode;
use App\Enums\Roles;
use App\Models\Role;
use App\Models\User;
use Storage;

class PermissionController extends Controller
{
    protected PermissionLayoutService $permissionService;

    public function __construct(PermissionLayoutService $permissionService)
    {
        $this->middleware('auth');
        $this->permissionService = $permissionService;
    }

    public function indexRoles(): View
    {        
        $roles = $this->getRoles();
        return view('masters.permissions.roles', compact('roles'));
    }

    public function storeRole(Request $request): JsonResponse
    {        
        try {
            // Keep original for display_name
            $displayName = trim($request->name);

            // Convert to snake_case for DB name
            $name = Str::snake($displayName);

            // Validate uniqueness using the transformed value
            $request->merge(['name' => $name]);

            $request->validate([
                'name' => 'bail|required|string|max:255|unique:roles,name',
            ]);

            Role::create([
                'name'          => $name,
                'display_name'  => $displayName,
                'guard_name'    => 'web',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role has been added successfully!'
            ]);
        } 
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function editRole(Role $role): JsonResponse
    {
        try {
            if ($role->name === Roles::MASTER_ADMIN) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot edit the master_admin role.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data'    => $role->only(['id', 'display_name']),
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch role.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateRole(Request $request, Role $role): JsonResponse
    {
        if ($role->name === Roles::MASTER_ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Editing the master_admin role is not allowed.',
            ], 403);
        }        

        try {
            $displayName = trim($request->name);
            $name = Str::snake($displayName);

            $request->merge(['name' => $name]);

            $request->validate([
                'name' => 'bail|required|string|max:255|unique:roles,name,' . $role->id,
            ]);

            $role->update([
                'name' => $name,
                'display_name' => $displayName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role has been updated successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroyRole(Role $role): JsonResponse
    {
        try {
            if ($role->name === Roles::MASTER_ADMIN) {
                return response()->json([
                    'success' => false,
                    'message' => 'This role cannot be deleted.',
                ], 403);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role has been deleted successfully!'
            ]);
        } 
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function indexUsers(): View
    {
        $users = $this->getUsers();

        return view('masters.permissions.users.index', compact('users'));
    }

    public function createUser(): View
    {
        $roles = $this->getRoles();

        return view('masters.permissions.users.manage', [
            'form_mode'   => FormMode::CREATE,
            'form_action' => route('permissions.users.store'),
            'page_title'  => 'Add User',
            'user'        => null,
            'roles'       => $roles,
        ]);
    }
    
    public function storeUser(UserRequest $request): RedirectResponse
    {
        // Validate request
        $validated = $request->validated();
        
        try {
            // Create user
            $user = User::create([
                'name'      => $validated['name'],
                'role_id'   => $validated['role_id'],
                'email'     => $validated['email'] ?? null,
                'user_name' => $validated['user_name'],
                'password'  => Hash::make($validated['password']),
            ]);

            // Handle photo
            $imageName = $user->id . '.jpg';
            $photoPath = 'public/users/';

            if ($request->hasFile('photo')) {
                $extension = $request->photo->extension();
                $imageName = $user->id . '.' . $extension;
                $request->photo->storeAs($photoPath, $imageName);
            }
            else {
                Storage::copy('public/avatar2.jpg', $photoPath . $imageName);
            }

            // Update user photo
            $user->update(['photo' => $imageName]);

            // Assign permissions based on role
            $role = Role::findOrFail($user->role_id);
            $permissions = $role->getPermissionNames();
            $user->syncPermissions($permissions);
            \Artisan::call('permission:cache-reset');

            return back()->with('success', 'User has been added successfully!');
        }
        catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function showUser(User $user): View
    {
        return view('masters.permissions.users.show', compact('user'));
    }

    public function editUser(User $user): View
    {
        $roles = $this->getRoles();

        return view('masters.permissions.users.manage', [
            'form_mode'   => FormMode::EDIT,
            'form_action' => route('permissions.users.update', $user),
            'page_title'  => 'Edit User',
            'user'        => $user,
            'roles'       => $roles,
        ]);
    }

    public function updateUser(UserRequest $request, User $user): RedirectResponse
    {
        // Validate request
        $validated = $request->validated();
        
        try {
            // Setup data
            $data = [
                'name'      => $validated['name'],
                'role_id'   => $validated['role_id'],
                'email'     => $validated['email'] ?? null,
                'user_name' => $validated['user_name'],
            ];

            // Set password if given
            if ($request->filled('password')) {
                $data['password'] = Hash::make($validated['password']);
            }

            // Update user
            $user->update($data);

            // Update user photo if changed
            if ($request->hasFile('photo')) {
                $extension = $request->photo->extension();
                $imageName = $user->id . '.' . $extension;

                // Delete old photo
                Storage::delete('public/users/' . $user->photo);

                // Create new photo
                $request->photo->storeAs('public/users', $imageName);

                $user->update(['photo' => $imageName]);
            }

            return back()->with('success', 'User has been updated successfully!');
        } 
        catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroyUser(User $user): JsonResponse
    {
        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User has been deleted successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateUserStatus(Request $request, User $user)
    {
        $user->status = $user->status === 'Active' ? 'Inactive' : 'Active';
        $user->save();

        return back()->with('success', "User is now {$user->status}");
    }

    public function indexRolePermissions(): View
    {
        $roles = $this->getRoles();
        $permissions = $this->permissionService->getAllPermissions();
        return view('masters.permissions.permissions.roles', compact('roles', 'permissions'));
    }

    public function showRolePermissions(Role $role): JsonResponse
    {
        try {
            $permissions = $role->getPermissionNames();

            return response()->json([
                'success' => true,
                'data'    => $permissions,
            ]);
        } 
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load permissions.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateRolePermissions(Role $role, Request $request): JsonResponse
    {
        $request->validate([
            'permissions'   => 'array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        try {
            $permissions = $request->input('permissions', []);
            $role->syncPermissions($permissions);
            \Artisan::call('permission:cache-reset');

            $action  = !empty($permissions) ? 'saved' : 'revoked';
            $message = "The permissions for {$role->display_name} have been {$action} successfully!";

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save permissions.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function indexUserPermissions(): View
    {
        $users = $this->getUsers();
        $permissions = $this->permissionService->getAllPermissions();
        return view('masters.permissions.permissions.users', compact('users', 'permissions'));
    }

    public function showUserPermissions(User $user): JsonResponse
    {
        try {
            $permissions = $user->getPermissionNames();

            return response()->json([
                'success' => true,
                'data'    => $permissions,
            ]);
        } 
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load permissions.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateUserPermissions(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'permissions'   => 'array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        try {
            $permissions = $request->input('permissions', []);
            $user->syncPermissions($permissions);
            \Artisan::call('permission:cache-reset');
            
            $action  = !empty($permissions) ? 'saved' : 'revoked';
            $message = "The permissions for {$user->name} have been {$action} successfully!";

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save permissions.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    private function getRoles(): Collection
    {
        $roles = Role::select('id', 'name', 'display_name')
            ->where('name', '!=', Roles::MASTER_ADMIN)
            ->where('guard_name', 'web')
            ->where('status', 'Active')
            ->get();
        return $roles;
    }

    private function getUsers(): Collection
    {
        $users = User::select('id', 'name', 'user_name', 'role_id')
            ->with('role:id,display_name')
            ->where('role_id', '!=', Roles::MASTER_ADMIN_ID)
            ->where('status', 'Active')
            ->get();
        return $users;
    }
}
