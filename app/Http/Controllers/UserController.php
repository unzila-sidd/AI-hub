<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private SyncService $sync)
    {
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('users.manage'), 403);

        $users = User::with('role')
            ->when($request->search, fn ($q, $s) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pos.users.index', compact('users'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('users.manage'), 403);

        $roles = Role::all();

        return view('pos.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('users.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => $data['role_id'] ?? null,
        ]);

        $this->sync->record('user', $user->id, 'created', $user->fresh()->load('role')->toArray());

        return redirect()->route('pos.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        abort_unless(auth()->user()->hasPermission('users.manage'), 403);

        $roles = Role::all();

        return view('pos.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()->hasPermission('users.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'] ?? null,
        ]);

        if (!empty($data['password'])) {
            $user->update(['password' => $data['password']]);
        }

        $this->sync->record('user', $user->id, 'updated', $user->fresh()->load('role')->toArray());

        return redirect()->route('pos.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        abort_unless(auth()->user()->hasPermission('users.manage'), 403);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $this->sync->record('user', $user->id, 'deleted', $user->toArray());

        $user->delete();

        return back()->with('success', 'User deleted.');
    }
}