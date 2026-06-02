<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            Add Production Plan
        </h2>
        <div class="fortune-header-subtitle">
            Create planned production for a machine, product, shift and hour.
        </div>
    </x-slot>

    @include('production-plans.form', [
        'action' => route('production-plans.store'),
        'method' => 'POST',
        'buttonText' => 'Save Production Plan',
        'plan' => null,
    ])
</x-app-layout>