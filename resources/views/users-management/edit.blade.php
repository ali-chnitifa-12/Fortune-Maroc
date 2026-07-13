<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">{{ __('Edit User') }}</h2>
                <div class="erp-page-subtitle">
                    {{ __('Update application user role, status, zone or production line access.') }}
                </div>
            </div>

            <a href="{{ route('users-management.index') }}" class="erp-btn erp-btn-secondary">
                {{ __('Back to Users') }}
            </a>
        </div>
    </x-slot>

    @php
        $editingUser = $managedUser ?? $user ?? null;
    @endphp

    <div class="erp-page-wrap">
        @if(!$editingUser || !$editingUser->id)
            <div class="fortune-error">
                {{ __('User not found for edit form.') }}
            </div>
        @else
            @if(session('success'))
                <div class="fortune-success">
                    {{ __(session('success')) }}
                </div>
            @endif

            @if($errors->any())
                <div class="fortune-error">
                    <ul style="list-style:disc;margin-left:20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ __($error) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="erp-card">
                <form method="POST" action="{{ url('/users-management/' . $editingUser->id) }}">
                    @csrf
                    @method('PUT')

                    @include('users-management.form', [
                        'user' => $editingUser,
                        'zones' => $zones,
                        'productionLines' => $productionLines,
                        'selectedZones' => $selectedZones ?? [],
                        'buttonText' => 'Update User',
                    ])
                </form>
            </div>
        @endif
    </div>

    @include('components.erp-page-style')
</x-app-layout>