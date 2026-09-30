<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(private SyncService $sync)
    {
    }

    /**
     * Run a full checkout in a single transaction: verify stock, build the sale,
     * write the line items and payment, decrement stock and record sync events.
     *
     * @throws InsufficientStockException
     */
    public function checkout(
        array $items,
        ?string $customerName = null,
        float $discount = 0,
        string $method = 'cash',
        ?string $reference = null,
        ?int $cashierId = null,
    ): Sale {
        $cashierId = $cashierId ?? auth()->id();

        return DB::transaction(function () use ($items, $customerName, $discount, $method, $reference, $cashierId) {
            $subtotal = 0;
            $rows = [];

            foreach ($items as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();

                if (! $product || $product->stock < $item['qty']) {
                    $name = $product?->name ?? sprintf('#%d', $item['product_id']);

                    throw new InsufficientStockException("Not enough stock for {$name}.");
                }

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

            $total = max($subtotal - $discount, 0);

            $sale = Sale::create([
                'invoice_no' => Sale::generateInvoiceNo(),
                'customer_name' => $customerName,
                'cashier_id' => $cashierId,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_status' => 'paid',
            ]);

            foreach ($rows as $row) {
                SaleItem::create(array_merge(['sale_id' => $sale->id], $row));
            }

            $sale->payments()->create([
                'user_id' => $cashierId,
                'amount' => $total,
                'method' => $method,
                'reference' => $reference,
            ]);

            $this->sync->record('sale', $sale->id, 'created', $sale->fresh()->load('items', 'payments')->toArray());

            return $sale->load('items', 'payments');
        });
    }
}