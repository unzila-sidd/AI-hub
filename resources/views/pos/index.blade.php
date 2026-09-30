@extends('layouts.admin')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>POS Terminal</h1></div>
            <div class="col-sm-6 text-sm-right">
                <span class="badge badge-info">Sales today: {{ $todaySales }}</span>
                <span class="badge badge-success">Revenue today: {{ number_format($todayRevenue, 2) }}</span>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">

        @if (session('error'))
            <div class="alert alert-danger py-2">{{ session('error') }}</div>
        @endif

        <div class="row">

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Products</h3>
                    </div>
                    <div class="card-body">
                        <div class="pos-grid" id="productGrid">
                            @foreach ($products as $product)
                                <div class="pos-product {{ $product->stock <= 0 ? 'out' : '' }}"
                                     data-id="{{ $product->id }}"
                                     data-name="{{ $product->name }}"
                                     data-price="{{ $product->price }}"
                                     data-stock="{{ $product->stock }}">
                                    <div class="font-weight-bold">{{ $product->name }}</div>
                                    <div class="price">{{ number_format($product->price, 2) }}</div>
                                    <div class="stock">Stock: {{ $product->stock }}</div>
                                </div>
                            @endforeach
                        </div>
                        @if ($products->isEmpty())
                            <p class="text-muted mb-0">No in-stock products. Add some in Products first.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Cart</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Customer</label>
                            <input type="text" id="customerName" class="form-control" placeholder="Walk-in customer">
                        </div>
                        <div id="cartItems" class="mb-2">
                            <p class="text-muted mb-0">Cart is empty.</p>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Subtotal</span>
                            <span id="subtotal">0.00</span>
                        </div>
                        <div class="form-group">
                            <label for="discount">Discount</label>
                            <input type="number" id="discount" class="form-control" value="0" min="0" step="0.01">
                        </div>
                        <div class="d-flex justify-content-between font-weight-bold">
                            <span>Total</span>
                            <span id="total">0.00</span>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label>Payment method</label>
                            <select id="method" class="form-control">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reference</label>
                            <input type="text" id="reference" class="form-control" placeholder="Optional ref no.">
                        </div>
                        <button class="btn btn-success btn-block" id="checkout">Complete Sale</button>
                        <div id="checkoutError" class="text-danger mt-2"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection

@section('scripts')
<script src="{{ asset('js/pos.js') }}"></script>
@endsection