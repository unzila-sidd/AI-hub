@extends('layouts.admin')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Users</h1></div>
            <div class="col-sm-6 text-sm-right">
                <a href="{{ route('pos.users.create') }}" class="btn btn-primary">Add User</a>
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
            <div class="card-body">
                <form class="form-inline mb-3" method="get">
                    <input type="text" name="search" class="form-control mb-2" placeholder="Search name / email" value="{{ request('search') }}">
                    <button class="btn btn-secondary ml-2 mb-2">Search</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th class="text-right">Actions</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->name }} @if ($user->id === auth()->id()) <span class="badge badge-info">you</span> @endif</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if ($user->role)
                                            <span class="badge badge-primary">{{ $user->role->name }}</span>
                                        @else
                                            <span class="badge badge-secondary">No role</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('pos.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('pos.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No users found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</section>
@endsection