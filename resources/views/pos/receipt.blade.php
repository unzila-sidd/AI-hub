@extends('layouts.admin')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="col-lg-6 mx-auto">
            <div class="card" id="receipt">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h3 class="mb-0">{{ config('app.name') }}</h3>
                        <div>Sale receipt</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <div><strong>Invoice:</strong> {{ $sale->invoice_no }}</div>
                            <div><strong>Customer:</strong> {{ $sale->customer_name ?? 'Walk-in' }}</div>
                        </div>
                        <div class="col-6 text-right">
                            <div><strong>Date:</strong> {{ $sale->created_at->format('d M Y H:i') }}</div>
                            <div><strong>Cashier:</strong> {{ $sale->cashier?->name ?? '-' }}</div>
                        </div>
                    </div>
                    <hr>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr><th>Item</th><th class="text-center">Qty</th><th class="text-right">Price</th><th class="text-right">Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ $item->qty }}</td>
                                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-6">
                            <strong>Payments</strong>
                            @foreach ($sale->payments as $payment)
                                <div>{{ ucfirst($payment->method) }}: {{ number_format($payment->amount, 2) }}</div>
                            @endforeach
                        </div>
                        <div class="col-6 text-right">
                            <div>Subtotal: {{ number_format($sale->subtotal, 2) }}</div>
                            <div>Discount: {{ number_format($sale->discount, 2) }}</div>
                            <h4 class="mt-1">TOTAL: {{ number_format($sale->total, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mb-3">
                <button class="btn btn-primary" onclick="window.print()">Print</button>
                <a href="{{ route('pos.index') }}" class="btn btn-secondary">New Sale</a>
            </div>
        </div>
    </div>
</section>
@endsection