<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Add Production Plan</h2>
                <div class="erp-page-subtitle">
                    Create shift production plan. The system generates hourly production entries automatically.
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

        @include('production-plans.form')
    </div>

    @include('components.erp-page-style')
</x-app-layout>