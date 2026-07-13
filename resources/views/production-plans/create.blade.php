<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">{{ __('Add Production Plan') }}</h2>
                <div class="erp-page-subtitle">
                    {{ __('Create planned production for a machine, product, shift and hour.') }}
                </div>
            </div>

            <a href="{{ route('production-plans.index') }}" class="erp-btn erp-btn-secondary">
                {{ __('Back to Production Planning') }}
            </a>
        </div>
    </x-slot>

    <div class="erp-page-wrap">
        @if($errors->any())
            <div class="fortune-error">
                <ul style="list-style:disc;margin-left:20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ __($error) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('production-plans.form', [
            'plan' => null,
            'zones' => $zones,
            'productionLines' => $productionLines,
            'products' => $products,
            'shifts' => $shifts,
            'statuses' => $statuses,
            'action' => route('production-plans.store'),
            'method' => 'POST',
            'buttonText' => 'Save Production Plan',
        ])
    </div>

    @include('components.erp-page-style')
</x-app-layout>