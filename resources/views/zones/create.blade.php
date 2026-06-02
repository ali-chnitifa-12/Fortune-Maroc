<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Add Zone</h2>
                <div class="erp-page-subtitle">
                    Create a production zone.
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
            <form method="POST" action="{{ route('zones.store') }}">
                @csrf

                <div class="erp-form-grid">
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
                    <textarea name="description" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="erp-form-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Save Zone
                    </button>

                    <a href="{{ route('zones.index') }}" class="erp-btn erp-btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    @include('components.erp-page-style')
</x-app-layout>