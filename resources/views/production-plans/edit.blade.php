<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Edit Production Plan</h2>
                <div class="erp-page-subtitle">
                    Update shift production plan before hourly entries are generated.
                </div>
            </div>

            <a href="{{ route('production-plans.index') }}" class="erp-btn erp-btn-secondary">
                Back to Plans
            </a>
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

        @include('production-plans.form')
    </div>

    @include('components.erp-page-style')
</x-app-layout>