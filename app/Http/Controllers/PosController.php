<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function __construct(private SyncService $sync)
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
            $sale = DB::transaction(function () use ($validated) {
                $subtotal = 0;
                $rows = [];

                foreach ($validated['items'] as $item) {
                    $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();

                    abort_if(!$product || $product->stock < $item['qty'], 422, "Not enough stock for {$product?->name}.");

                    $lineTotal = $product->price * $item['qty'];
                    $subtotal += $lineTotal;

                    $rows[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => $product->price,
                        'qty' => $item['qty'],
                        'line_total' => $lineTotal,
                    ];

                    $product->decrement('stock', $item['qty']);

                    $this->sync->record('product', $product->id, 'stock_updated', [
                        'product_id' => $product->id,
                        'stock' => $product->fresh()->stock,
                    ]);
                }

                $discount = (float) ($validated['discount'] ?? 0);
                $total = max($subtotal - $discount, 0);

                $sale = Sale::create([
                    'invoice_no' => Sale::generateInvoiceNo(),
                    'customer_name' => $validated['customer_name'] ?? null,
                    'cashier_id' => auth()->id(),
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'payment_status' => 'paid',
                ]);

                foreach ($rows as $row) {
                    SaleItem::create(array_merge(['sale_id' => $sale->id], $row));
                }

                $sale->payments()->create([
                    'user_id' => auth()->id(),
                    'amount' => $total,
                    'method' => $validated['method'],
                    'reference' => $validated['reference'] ?? null,
                ]);

                $this->sync->record('sale', $sale->id, 'created', $sale->fresh()->load('items', 'payments')->toArray());

                return $sale->load('items');
            });

            return redirect()->route('pos.receipt', $sale->id);
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