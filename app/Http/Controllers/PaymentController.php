<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Sale;
use App\Services\SyncService;
use Illuminate\Http\Request;

class PaymentController extends Controller
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
            ->when($request->search, fn ($q, $s) => $q->whereHas('sale', fn ($sq) => $sq->where('invoice_no', 'like', "%{$s}%")->orWhere('customer_name', 'like', "%{$s}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pos.payments.index', compact('payments'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('payments.add'), 403);

        $sales = Sale::latest('created_at')->get(['id', 'invoice_no', 'customer_name', 'total']);

        return view('pos.payments.create', compact('sales'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('payments.add'), 403);

        $data = $this->validated($request);

        $payment = Payment::create($data + ['user_id' => auth()->id()]);

        $this->syncSaleStatus($payment->sale);
        $this->sync->record('payment', $payment->id, 'created', $payment->fresh()->load('sale')->toArray());

        return redirect()->route('pos.payments.index')->with('success', 'Payment recorded.');
    }

    public function edit(Payment $payment)
    {
        abort_unless(auth()->user()->hasPermission('payments.edit'), 403);

        $sales = Sale::latest('created_at')->get(['id', 'invoice_no', 'customer_name', 'total']);

        return view('pos.payments.edit', compact('payment', 'sales'));
    }

    public function update(Request $request, Payment $payment)
    {
        abort_unless(auth()->user()->hasPermission('payments.edit'), 403);

        $data = $this->validated($request);

        $payment->update($data);

        $this->syncSaleStatus($payment->sale);
        $this->sync->record('payment', $payment->id, 'updated', $payment->fresh()->load('sale')->toArray());

        return redirect()->route('pos.payments.index')->with('success', 'Payment updated.');
    }

    public function destroy(Payment $payment)
    {
        abort_unless(auth()->user()->hasPermission('payments.delete'), 403);

        $this->sync->record('payment', $payment->id, 'deleted', $payment->toArray());

        $sale = $payment->sale;
        $payment->delete();
        $this->syncSaleStatus($sale);

        return back()->with('success', 'Payment deleted.');
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

    private function syncSaleStatus(Sale $sale): void
    {
        $paid = (float) $sale->payments()->sum('amount');

        $sale->update([
            'payment_status' => $paid >= $sale->total ? 'paid' : 'unpaid',
        ]);
    }
}