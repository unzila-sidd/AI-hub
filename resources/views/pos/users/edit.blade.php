@extends('layouts.admin')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Edit User</h1></div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="col-lg-6">
            <div class="card card-outline card-primary">
                <form method="POST" action="{{ route('pos.users.update', $user) }}">
                    @csrf @method('PUT')
                    <div class="card-body">
                        @include('pos.users.form', ['user' => $user])
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary">Update User</button>
                        <a href="{{ route('pos.users.index') }}" class="btn btn-secondary float-right">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection