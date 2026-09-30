<div class="form-row">
    <div class="form-group col-md-8">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>SKU</label>
        <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku ?? '') }}" required>
        @error('sku') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Category</label>
        <input type="text" name="category" class="form-control" value="{{ old('category', $product->category ?? '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>Price</label>
        <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $product->price ?? '') }}" required>
    </div>
    <div class="form-group col-md-4">
        <label>Cost</label>
        <input type="number" step="0.01" min="0" name="cost" class="form-control" value="{{ old('cost', $product->cost ?? '') }}">
    </div>
    <div class="form-group col-md-6">
        <label>Stock</label>
        <input type="number" min="0" name="stock" class="form-control" value="{{ old('stock', $product->stock ?? 0) }}">
    </div>
    <div class="form-group col-md-6">
        <label>Low stock alert at</label>
        <input type="number" min="0" name="alert_stock" class="form-control" value="{{ old('alert_stock', $product->alert_stock ?? 5) }}">
    </div>
    <div class="form-group col-md-12">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $product->description ?? '') }}</textarea>
    </div>
    <div class="form-group col-md-12">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="active" name="active" value="1" {{ old('active', $product->active ?? true) ? 'checked' : '' }}>
            <label class="custom-control-label" for="active">Active (visible in POS)</label>
        </div>
    </div>
</div>