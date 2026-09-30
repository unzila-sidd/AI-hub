@extends('layouts.admin')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Roles & Permissions</h1></div>
            <div class="col-sm-6 text-sm-right">
                <a href="{{ route('pos.roles.create') }}" class="btn btn-primary">Add Role</a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success py-2">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger py-2">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Role</th><th>Description</th><th class="text-center">Permissions</th><th class="text-center">Users</th><th>Slug</th><th class="text-right">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td><strong>{{ $role->name }}</strong></td>
                                <td>{{ $role->description }}</td>
                                <td class="text-center">{{ $role->permissions_count }}</td>
                                <td class="text-center">{{ $role->users_count }}</td>
                                <td><code>{{ $role->slug }}</code></td>
                                <td class="text-right">
                                    <a href="{{ route('pos.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">Settings</a>
                                    @unless ($role->slug === 'admin')
                                        <form action="{{ route('pos.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No roles yet. Run the seeder.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection