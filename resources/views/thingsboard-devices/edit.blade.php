<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Edit ThingsBoard Mapping</h2>
                <div class="erp-page-subtitle">
                    Update ThingsBoard line or machine mapping.
                </div>
            </div>
        </div>
    </x-slot>

    @include('thingsboard-devices.form', [
        'device' => $device,
        'action' => route('thingsboard-devices.update', $device),
        'method' => 'PUT',
        'buttonText' => 'Update Mapping',
    ])
</x-app-layout>