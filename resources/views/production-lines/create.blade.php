<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Add Production Line</h2>
                <div class="erp-page-subtitle">
                    Create a production line and assign allowed products.
                </div>
            </div>
        </div>
    </x-slot>

    <div class="erp-page-wrap">
        @if($errors->any())
            <div class="fortune-error">
                <ul style="list-style:disc;margin-left:20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="erp-card">
            <form method="POST" action="{{ route('production-lines.store') }}">
                @csrf

                <div class="erp-form-grid">
                    <div>
                        <label>Zone</label>
                        <select name="zone_id" required>
                            <option value="">Select zone</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}"
                                    {{ old('zone_id') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->code }} - {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Code</label>
                        <input type="text"
                               name="code"
                               value="{{ old('code') }}"
                               required>
                    </div>

                    <div>
                        <label>Name</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               required>
                    </div>

                    <div>
                        <label>Status</label>
                        <select name="is_active" required>
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="erp-form-section">
                    <label>Description</label>
                    <textarea name="description" rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="erp-section-head" style="margin-top:18px;">
                    <div>
                        <h3 class="erp-section-title">Assigned Products</h3>
                        <div class="erp-section-subtitle">
                            Select products allowed for this production line.
                        </div>
                    </div>
                </div>

                <div class="erp-responsive-table">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th style="width:70px;">Select</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th style="width:220px;">Standard Qty / Hour</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <input type="checkbox"
                                               name="products[{{ $product->id }}][selected]"
                                               value="1"
                                               {{ old("products.{$product->id}.selected") ? 'checked' : '' }}>
                                    </td>

                                    <td>
                                        <strong>{{ $product->code }}</strong>
                                    </td>

                                    <td>{{ $product->name }}</td>

                                    <td>
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="products[{{ $product->id }}][standard_qty_per_hour]"
                                               value="{{ old("products.{$product->id}.standard_qty_per_hour", $product->standard_qty_per_hour) }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="erp-form-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Save Production Line
                    </button>

                    <a href="{{ route('production-lines.index') }}" class="erp-btn erp-btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    @include('components.erp-page-style')
</x-app-layout>