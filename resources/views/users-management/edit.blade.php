<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Edit User</h2>
                <div class="erp-page-subtitle">
                    Update user role and plant hierarchy assignment.
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
            <form method="POST" action="{{ route('users-management.update', $user) }}">
                @csrf
                @method('PUT')

                @include('users-management.form', [
                    'user' => $user,
                    'zones' => $zones,
                    'productionLines' => $productionLines,
                    'selectedZones' => $selectedZones,
                    'isEdit' => true,
                ])

                <div class="erp-form-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Update User
                    </button>

                    <a href="{{ route('users-management.index') }}" class="erp-btn erp-btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    @include('components.erp-page-style')
</x-app-layout>