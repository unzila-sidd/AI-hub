@extends('layouts.admin')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Add User</h1></div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="col-lg-6">
            <div class="card card-outline card-primary">
                <form method="POST" action="{{ route('pos.users.store') }}">
                    @csrf
                    <div class="card-body">
                        @include('pos.users.form')
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary">Create User</button>
                        <a href="{{ route('pos.users.index') }}" class="btn btn-secondary float-right">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection