<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\SyncService;
use Illuminate\Http\Request;

class PaymentApiController extends Controller
{
    public function __construct(private SyncService $sync)
    {
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('payments.view'), 403);

        $payments = Payment::query()
            ->with(['sale:id,invoice_no,customer_name,total', 'user:id,name'])
            ->when($request->method, fn ($q, $m) => $q->where('method', $m))
            ->when($request->search, fn ($q, $s) => $q->whereHas('sale',
                fn ($sq) => $sq->where('invoice_no', 'like', "%{$s}%")->orWhere('customer_name', 'like', "%{$s}%")))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($payments);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('payments.add'), 403);

        $payment = Payment::create($this->validated($request) + ['user_id' => auth()->id()]);

        $payment->sale->refreshPaymentStatus();
        $this->sync->record('payment', $payment->id, 'created', $payment->fresh()->load('sale')->toArray());

        return response()->json(['data' => $payment->fresh()->load('sale')], 201);
    }

    public function update(Request $request, Payment $payment)
    {
        abort_unless(auth()->user()->hasPermission('payments.edit'), 403);

        $payment->update($this->validated($request));

        $payment->sale->refreshPaymentStatus();
        $this->sync->record('payment', $payment->id, 'updated', $payment->fresh()->load('sale')->toArray());

        return response()->json(['data' => $payment->fresh()->load('sale')]);
    }

    public function destroy(Payment $payment)
    {
        abort_unless(auth()->user()->hasPermission('payments.delete'), 403);

        $this->sync->record('payment', $payment->id, 'deleted', $payment->toArray());

        $sale = $payment->sale;
        $payment->delete();
        $sale->refreshPaymentStatus();

        return response()->json(null, 204);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:' . implode(',', Payment::METHODS)],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
    }
}