<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
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
            ->paginate(15)
            ->withQueryString();

        return view('pos.products.index', compact('products'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('products.add'), 403);

        return view('pos.products.create');
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('products.add'), 403);

        $data = $this->validated($request);

        $product = Product::create($data);

        $this->sync->record('product', $product->id, 'created', $product->toArray());

        return redirect()->route('pos.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        abort_unless(auth()->user()->hasPermission('products.edit'), 403);

        return view('pos.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        abort_unless(auth()->user()->hasPermission('products.edit'), 403);

        $data = $this->validated($request, $product);

        $product->update($data);

        $this->sync->record('product', $product->id, 'updated', $product->fresh()->toArray());

        return redirect()->route('pos.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        abort_unless(auth()->user()->hasPermission('products.delete'), 403);

        $this->sync->record('product', $product->id, 'deleted', $product->toArray());

        $product->delete();

        return back()->with('success', 'Product deleted.');
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