<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function __construct(private SyncService $sync)
    {
    }

    public function index()
    {
        abort_unless(auth()->user()->hasPermission('roles.manage'), 403);

        $roles = Role::withCount('users', 'permissions')->get();

        return view('pos.roles.index', compact('roles'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('roles.manage'), 403);

        $permissions = Permission::orderBy('group')->get()->groupBy('group');

        return view('pos.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('roles.manage'), 403);

        $data = $this->validated($request);

        $role = Role::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        $this->sync->record('role', $role->id, 'created', $role->fresh()->load('permissions')->toArray());

        return redirect()->route('pos.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        abort_unless(auth()->user()->hasPermission('roles.manage'), 403);

        $permissions = Permission::orderBy('group')->get()->groupBy('group');
        $role->load('permissions');

        return view('pos.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        abort_unless(auth()->user()->hasPermission('roles.manage'), 403);

        $data = $this->validated($request);

        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        $this->sync->record('role', $role->id, 'updated', $role->fresh()->load('permissions')->toArray());

        return redirect()->route('pos.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        abort_unless(auth()->user()->hasPermission('roles.manage'), 403);

        if ($role->slug === 'admin') {
            return back()->with('error', 'The admin role cannot be deleted.');
        }

        $this->sync->record('role', $role->id, 'deleted', $role->toArray());

        $role->delete();

        return back()->with('success', 'Role deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);
    }
}