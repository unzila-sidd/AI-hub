@extends('layouts.admin')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Products</h1></div>
            <div class="col-sm-6 text-sm-right">
                @if (auth()->user()->hasPermission('products.add'))
                    <a href="{{ route('pos.products.create') }}" class="btn btn-primary">Add Product</a>
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
                    <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search name / SKU / category" value="{{ request('search') }}">
                    <select name="stock" class="form-control mr-2 mb-2">
                        <option value="">All stock</option>
                        <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low stock</option>
                    </select>
                    <button class="btn btn-secondary mb-2">Filter</button>
                </form>

                @if (session('success'))
                    <div class="alert alert-success py-2">{{ session('success') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th><th>SKU</th><th>Category</th><th class="text-right">Price</th><th class="text-center">Stock</th><th>Status</th><th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->sku }}</td>
                                    <td>{{ $product->category ?? '-' }}</td>
                                    <td class="text-right">{{ number_format($product->price, 2) }}</td>
                                    <td class="text-center">
                                        @if ($product->stock <= $product->alert_stock)
                                            <span class="badge badge-warning">{{ $product->stock }}</span>
                                        @else
                                            <span class="badge badge-success">{{ $product->stock }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->active ? 'Active' : 'Inactive' }}</td>
                                    <td class="text-right">
                                        @if (auth()->user()->hasPermission('products.edit'))
                                            <a href="{{ route('pos.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        @endif
                                        @if (auth()->user()->hasPermission('products.delete'))
                                            <form action="{{ route('pos.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">No products found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</section>
@endsection