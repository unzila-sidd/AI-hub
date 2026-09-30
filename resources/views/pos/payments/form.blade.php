<div class="form-group">
    <label>Sale / Invoice</label>
    <select name="sale_id" class="form-control" required>
        <option value="">Select a sale</option>
        @foreach ($sales as $sale)
            <option value="{{ $sale->id }}" {{ old('sale_id', $payment->sale_id ?? request('sale_id')) == $sale->id ? 'selected' : '' }}>
                {{ $sale->invoice_no }} - {{ $sale->customer_name ?? 'Walk-in' }} ({{ number_format($sale->total, 2) }})
            </option>
        @endforeach
    </select>
    @error('sale_id') <small class="text-danger">{{ $message }}</small> @enderror
</div>
<div class="form-group">
    <label>Amount</label>
    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount', $payment->amount ?? '') }}" required>
    @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
</div>
<div class="form-group">
    <label>Method</label>
    <select name="method" class="form-control">
        @foreach (\App\Models\Payment::METHODS as $method)
            <option value="{{ $method }}" {{ old('method', $payment->method ?? 'cash') === $method ? 'selected' : '' }}>
                {{ ucfirst(str_replace('_', ' ', $method)) }}
            </option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label>Reference</label>
    <input type="text" name="reference" class="form-control" value="{{ old('reference', $payment->reference ?? '') }}">
</div>
<div class="form-group">
    <label>Note</label>
    <input type="text" name="note" class="form-control" value="{{ old('note', $payment->note ?? '') }}">
</div>