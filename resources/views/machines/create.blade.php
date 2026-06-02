<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Add Machine</h2>
                <div class="erp-page-subtitle">
                    Create a machine and assign it to a production line.
                </div>
            </div>
        </div>
    </x-slot>

    @include('machines.form', [
        'machine' => null,
        'action' => route('machines.store'),
        'method' => 'POST',
        'buttonText' => 'Save Machine',
    ])
</x-app-layout>