<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductApiController extends Controller
{
    public function __construct(private SyncService $sync)
    {
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasAnyPermission(['products.view', 'products.add', 'products.edit', 'products.delete']), 403);

        $products = Product::query()
            ->when($request->search, fn ($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('sku', 'like', "%{$request->search}%")
                ->orWhere('category', 'like', "%{$request->search}%"))
            ->when($request->stock === 'low', fn ($q) => $q->lowStock())
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($products);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('products.add'), 403);

        $product = Product::create($this->validated($request));

        $this->sync->record('product', $product->id, 'created', $product->toArray());

        return response()->json(['data' => $product], 201);
    }

    public function update(Request $request, Product $product)
    {
        abort_unless(auth()->user()->hasPermission('products.edit'), 403);

        $product->update($this->validated($request, $product));

        $this->sync->record('product', $product->id, 'updated', $product->fresh()->toArray());

        return response()->json(['data' => $product->fresh()]);
    }

    public function destroy(Product $product)
    {
        abort_unless(auth()->user()->hasPermission('products.delete'), 403);

        $this->sync->record('product', $product->id, 'deleted', $product->toArray());

        $product->delete();

        return response()->json(null, 204);
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product?->id)],
            'category' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'alert_stock' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);
    }
}