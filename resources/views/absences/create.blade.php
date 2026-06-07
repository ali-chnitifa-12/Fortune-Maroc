<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Add Absence</h2>
                <div class="erp-page-subtitle">
                    Create a new employee absence record.
                </div>
            </div>

            <a href="{{ route('absences.index') }}" class="erp-btn erp-btn-secondary">
                Back to Absences
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

        <div class="erp-card">
            <form method="POST" action="{{ route('absences.store') }}">
                @csrf

                @include('absences.form')

                <div class="erp-form-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Save Absence
                    </button>

                    <a href="{{ route('absences.index') }}" class="erp-btn erp-btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    @include('components.erp-page-style')
</x-app-layout>