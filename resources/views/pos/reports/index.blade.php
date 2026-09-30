@extends('layouts.admin')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Reports</h1></div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-body">
                <form class="form-inline" method="get">
                    <label class="mr-2 mb-2">From</label>
                    <input type="date" name="from" class="form-control mr-3 mb-2" value="{{ $from->format('Y-m-d') }}">
                    <label class="mr-2 mb-2">To</label>
                    <input type="date" name="to" class="form-control mr-3 mb-2" value="{{ $to->format('Y-m-d') }}">
                    <button class="btn btn-primary mb-2">Filter</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner"><h3>{{ $summary['sales'] }}</h3><p>Sales</p></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner"><h3>{{ number_format($summary['revenue'], 2) }}</h3><p>Revenue</p></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner"><h3>{{ $summary['items_sold'] }}</h3><p>Items Sold</p></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner"><h3>{{ number_format($summary['avg'], 2) }}</h3><p>Avg Sale</p></div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Daily Sales</h3></div>
                    <div class="card-body">
                        @forelse ($daily as $row)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small">
                                    <span>{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</span>
                                    <span>{{ $row->sales }} sale(s) - {{ number_format($row->revenue, 2) }}</span>
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width: {{ ($row->revenue / $max) * 100 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No sales in this period.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3 class="card-title">Top Products</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-sm">
                            <thead><tr><th>Product</th><th class="text-center">Qty</th><th class="text-right">Revenue</th></tr></thead>
                            <tbody>
                                @forelse ($topProducts as $product)
                                    <tr>
                                        <td>{{ $product->product_name }}</td>
                                        <td class="text-center">{{ $product->qty }}</td>
                                        <td class="text-right">{{ number_format($product->revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">No data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Payments by Method</h3></div>
                    <div class="card-body">
                        @forelse ($byMethod as $method)
                            <div class="d-flex justify-content-between border-bottom py-1">
                                <span>{{ ucfirst(str_replace('_', ' ', $method->method)) }}</span>
                                <span>{{ $method->count }} payments - <strong>{{ number_format($method->total, 2) }}</strong></span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No payments in this period.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card card-warning">
                    <div class="card-header"><h3 class="card-title">Low Stock Alerts</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-sm">
                            <tbody>
                                @forelse ($lowStock as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td class="text-right"><span class="badge badge-danger">{{ $product->stock }} left</span></td>
                                    </tr>
                                @empty
                                    <tr><td class="text-center text-muted py-2">All stock levels are fine.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection