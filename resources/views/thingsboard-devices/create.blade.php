<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Add ThingsBoard Mapping</h2>
                <div class="erp-page-subtitle">
                    Create mapping for a production line or machine.
                </div>
            </div>
        </div>
    </x-slot>

    @include('thingsboard-devices.form', [
        'device' => null,
        'action' => route('thingsboard-devices.store'),
        'method' => 'POST',
        'buttonText' => 'Save Mapping',
    ])
</x-app-layout>