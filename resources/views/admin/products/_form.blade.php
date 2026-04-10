@php $p = $product ?? null; @endphp

<div class="admin-product-form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div style="grid-column:1/-1;">
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Title *</label>
        <input type="text" name="title" value="{{ old('title', $p?->title) }}" required
               style="width:100%;padding:10px;border:1px solid {{ $errors->has('title') ? '#dc3545' : '#e2e8f0' }};border-radius:6px;">
        @error('title')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
    </div>

    <div>
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Category *</label>
        <select name="category" required style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ old('category', $p?->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        @error('category')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
    </div>

    <div>
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Price (UGX) *</label>
        <input type="number" name="price" value="{{ old('price', $p?->price) }}" required min="1"
               style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
        @error('price')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
    </div>

    <div>
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Original Price (UGX)</label>
        <input type="number" name="original_price" value="{{ old('original_price', $p?->original_price) }}" min="1"
               style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
    </div>

    <div>
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Discount (%)</label>
        <input type="number" name="discount" value="{{ old('discount', $p?->discount) }}" min="0" max="100"
               style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
    </div>

    <div>
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Stock *</label>
        <input type="number" name="stock" value="{{ old('stock', $p?->stock ?? 10) }}" required min="0"
               style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
        @error('stock')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
    </div>

    <div>
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Rating (0–5)</label>
        <input type="number" name="rating" value="{{ old('rating', $p?->rating ?? 4.0) }}" min="0" max="5" step="0.1"
               style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
    </div>

    <div>
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Reviews Count</label>
        <input type="number" name="reviews" value="{{ old('reviews', $p?->reviews ?? 0) }}" min="0"
               style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
    </div>

    <div>
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">SKU</label>
        <input type="text" name="sku" value="{{ old('sku', $p?->sku) }}"
               style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
    </div>

    <div>
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Warranty</label>
        <input type="text" name="warranty" value="{{ old('warranty', $p?->warranty) }}" placeholder="e.g. 1 Year"
               style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
    </div>

    <div style="grid-column:1/-1;">
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Image Path</label>
        <input type="text" name="image" value="{{ old('image', $p?->image) }}" placeholder="images/l1.jpg"
               style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
        <p style="font-size:12px;color:#64748b;margin-top:4px;">Relative path from public directory, e.g. images/l1.jpg</p>
    </div>

    <div style="grid-column:1/-1;">
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Description *</label>
        <textarea name="description" rows="4" required
                  style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">{{ old('description', $p?->description) }}</textarea>
        @error('description')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
    </div>

    <div style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" name="credit_available" id="credit_available" value="1"
               {{ old('credit_available', $p?->credit_available ?? true) ? 'checked' : '' }}
               style="width:18px;height:18px;">
        <label for="credit_available" style="font-size:14px;cursor:pointer;">Available on Credit</label>
    </div>
</div>
<style>
@media (max-width: 768px) {
    .admin-product-form-grid { grid-template-columns: 1fr !important; }
}
</style>
