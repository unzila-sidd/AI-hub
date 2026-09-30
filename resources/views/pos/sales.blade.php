@extends('layouts.admin')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Sales</h1></div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td><strong>{{ $sale->invoice_no }}</strong></td>
                                <td>{{ $sale->customer_name ?? 'Walk-in' }}</td>
                                <td>{{ $sale->items_count ?? '-' }}</td>
                                <td>{{ number_format($sale->total, 2) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $sale->payments->first()->method ?? 'n/a')) }}</td>
                                <td>
                                    @if ($sale->payment_status === 'paid')
                                        <span class="badge badge-success">Paid</span>
                                    @elseif ($sale->payment_status === 'refunded')
                                        <span class="badge badge-secondary">Refunded</span>
                                    @else
                                        <span class="badge badge-warning">Unpaid</span>
                                    @endif
                                </td>
                                <td>{{ $sale->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('pos.receipt', $sale->id) }}" class="btn btn-sm btn-outline-secondary">Receipt</a>
                                    @if (auth()->user()->hasPermission('payments.add'))
                                        <a href="{{ route('pos.payments.create', ['sale_id' => $sale->id]) }}" class="btn btn-sm btn-outline-primary">Add Payment</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No sales yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</section>
@endsection