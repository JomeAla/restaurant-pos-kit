<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Role::with('permissions')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|alpha_dash|unique:roles',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        $role = Role::create($validated);

        return response()->json($role, 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json($role->load('permissions'));
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|alpha_dash|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        $role->update($validated);

        return response()->json($role->load('permissions'));
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->slug === 'admin') {
            return response()->json(['message' => 'Cannot delete admin role.'], 403);
        }

        $role->permissions()->delete();
        $role->delete();

        return response()->json(['message' => 'Role deleted.']);
    }

    public function permissions(Role $role): JsonResponse
    {
        return response()->json($role->permissions()->pluck('permission'));
    }

    public function updatePermissions(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'required|string',
        ]);

        $role->permissions()->delete();

        foreach ($validated['permissions'] as $permission) {
            RolePermission::create([
                'role_id' => $role->id,
                'permission' => $permission,
            ]);
        }

        return response()->json($role->load('permissions'));
    }
}
