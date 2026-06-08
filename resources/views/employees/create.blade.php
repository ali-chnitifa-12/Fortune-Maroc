<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Ajouter un employé</h2>
                <div class="erp-page-subtitle">
                    Créer une fiche employé pour le registre RH.
                </div>
            </div>

            <a href="{{ route('employees.index') }}" class="erp-btn erp-btn-secondary">
                Retour aux employés
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
            <form method="POST" action="{{ route('employees.store') }}">
                @csrf

                @include('employees.form')

                <div class="erp-form-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Enregistrer
                    </button>

                    <a href="{{ route('employees.index') }}" class="erp-btn erp-btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    @include('components.erp-page-style')
</x-app-layout>