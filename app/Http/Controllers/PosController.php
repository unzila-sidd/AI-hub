<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Sale;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(private CheckoutService $checkout)
    {
    }

    public function index()
    {
        abort_unless(auth()->user()->hasPermission('sales.create'), 403);

        $products = Product::active()
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $todaySales = Sale::whereDate('created_at', today())->count();
        $todayRevenue = (float) Sale::whereDate('created_at', today())->sum('total');

        return view('pos.index', compact('products', 'todaySales', 'todayRevenue'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('sales.create'), 403);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'method' => ['required', 'in:cash,card,upi,bank_transfer,other'],
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

            return redirect()->route('pos.receipt', $sale->id);
        } catch (InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function sales()
    {
        abort_unless(auth()->user()->hasAnyPermission(['sales.view', 'sales.create']), 403);

        $sales = Sale::with('payments')->withCount('items')->latest()->paginate(20);

        return view('pos.sales', compact('sales'));
    }

    public function receipt(Sale $sale)
    {
        abort_unless($sale->cashier_id === auth()->id() || auth()->user()->hasAnyPermission(['sales.view', 'reports.view']), 403);

        $sale->load('items', 'payments');

        return view('pos.receipt', compact('sale'));
    }
}