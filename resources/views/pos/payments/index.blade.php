@extends('layouts.admin')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Payments</h1></div>
            <div class="col-sm-6 text-sm-right">
                @if (auth()->user()->hasPermission('payments.add'))
                    <a href="{{ route('pos.payments.create') }}" class="btn btn-primary">Add Payment</a>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form class="form-inline mb-3" method="get">
                    <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search invoice / customer" value="{{ request('search') }}">
                    <select name="method" class="form-control mr-2 mb-2">
                        <option value="">All methods</option>
                        <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ request('method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="upi" {{ request('method') === 'upi' ? 'selected' : '' }}>UPI</option>
                        <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                    <button class="btn btn-secondary mb-2">Filter</button>
                </form>

                @if (session('success'))
                    <div class="alert alert-success py-2">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger py-2">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>ID</th><th>Invoice</th><th>Customer</th><th class="text-right">Amount</th><th>Method</th><th>Reference</th><th>Cashier</th><th>Date</th><th class="text-right">Actions</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr>
                                    <td>#{{ $payment->id }}</td>
                                    <td>{{ $payment->sale->invoice_no ?? '-' }}</td>
                                    <td>{{ $payment->sale->customer_name ?? 'Walk-in' }}</td>
                                    <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
                                    <td><span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</span></td>
                                    <td>{{ $payment->reference ?? '-' }}</td>
                                    <td>{{ $payment->user->name ?? '-' }}</td>
                                    <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
                                    <td class="text-right">
                                        @if (auth()->user()->hasPermission('payments.edit'))
                                            <a href="{{ route('pos.payments.edit', $payment) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        @endif
                                        @if (auth()->user()->hasPermission('payments.delete'))
                                            <form action="{{ route('pos.payments.destroy', $payment) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this payment?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted">No payments found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</section>
@endsection