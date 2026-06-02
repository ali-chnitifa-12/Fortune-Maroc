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
                    <label>Production Line</label>
                    <select name="production_line_id">
                        <option value="">No production line</option>
                        @foreach($productionLines as $line)
                            <option value="{{ $line->id }}"
                                {{ old('production_line_id', $machine?->production_line_id) == $line->id ? 'selected' : '' }}>
                                {{ $line->zone?->code ?? '-' }} / {{ $line->code }} - {{ $line->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Code</label>
                    <input type="text"
                           name="code"
                           value="{{ old('code', $machine?->code) }}"
                           required>
                </div>

                <div>
                    <label>Name</label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $machine?->name) }}"
                           required>
                </div>

                <div>
                    <label>Legacy Line Text</label>
                    <input type="text"
                           name="line"
                           value="{{ old('line', $machine?->line) }}"
                           readonly>
                    <div class="erp-muted-small">
                        Automatically filled from selected production line.
                    </div>
                </div>

                <div>
                    <label>Status</label>
                    <select name="is_active" required>
                        <option value="1" {{ old('is_active', $machine?->is_active ?? true) == 1 ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="0" {{ old('is_active', $machine?->is_active ?? true) == 0 ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>
            </div>

            <div class="erp-form-section">
                <label>Description</label>
                <textarea name="description" rows="4">{{ old('description', $machine?->description) }}</textarea>
            </div>

            <div class="erp-form-actions">
                <button type="submit" class="erp-btn erp-btn-primary">
                    {{ $buttonText }}
                </button>

                <a href="{{ route('machines.index') }}" class="erp-btn erp-btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@include('components.erp-page-style')