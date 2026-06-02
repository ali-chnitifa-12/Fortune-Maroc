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
        <form method="POST" action="{{ $action }}">
            @csrf

            @if($method !== 'POST')
                @method($method)
            @endif

            <div class="erp-form-grid">
                <div>
                    <label>Code</label>
                    <input type="text"
                           name="code"
                           value="{{ old('code', $zone?->code) }}"
                           required>
                </div>

                <div>
                    <label>Name</label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $zone?->name) }}"
                           required>
                </div>

                <div>
                    <label>Status</label>
                    <label class="erp-checkbox-row">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $zone?->is_active ?? true) ? 'checked' : '' }}>
                        Active
                    </label>
                </div>
            </div>

            <div class="erp-form-section">
                <label>Description</label>
                <textarea name="description" rows="4">{{ old('description', $zone?->description) }}</textarea>
            </div>

            <div class="erp-form-actions">
                <button type="submit" class="erp-btn erp-btn-primary">{{ $buttonText }}</button>
                <a href="{{ route('zones.index') }}" class="erp-btn erp-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@include('components.erp-page-style')