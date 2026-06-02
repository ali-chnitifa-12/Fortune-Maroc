<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Edit Machine</h2>
                <div class="erp-page-subtitle">
                    Update machine details and production line assignment.
                </div>
            </div>
        </div>
    </x-slot>

    @include('machines.form', [
        'machine' => $machine,
        'action' => route('machines.update', $machine),
        'method' => 'PUT',
        'buttonText' => 'Update Machine',
    ])
</x-app-layout>