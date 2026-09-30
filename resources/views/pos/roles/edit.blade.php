@extends('layouts.admin')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Edit Role: {{ $role->name }}</h1></div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <form method="POST" action="{{ route('pos.roles.update', $role) }}">
                    @csrf @method('PUT')
                    <div class="card-body">
                        @include('pos.roles.form', ['role' => $role])
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary">Save Role</button>
                        <a href="{{ route('pos.roles.index') }}" class="btn btn-secondary float-right">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection