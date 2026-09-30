<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Sale;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class SaleApiController extends Controller
{
    public function __construct(private CheckoutService $checkout)
    {
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasAnyPermission(['sales.view', 'sales.create']), 403);

        $sales = Sale::query()
            ->with(['payments', 'cashier:id,name'])
            ->withCount('items')
            ->when($request->search, fn ($q, $s) => $q
                ->where('invoice_no', 'like', "%{$s}%")
                ->orWhere('customer_name', 'like', "%{$s}%"))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($sales);
    }

    public function checkout(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('sales.create'), 403);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'method' => ['required', 'in:' . implode(',', Payment::METHODS)],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $sale = $this->checkout->checkout(
                items: $validated['items'],
                customerName: $validated['customer_name'] ?? null,
                discount: (float) ($validated['discount'] ?? 0),
                method: $validated['method'],
                reference: $validated['reference'] ?? null,
            );

            return response()->json(['data' => $sale], 201);
        } catch (InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}