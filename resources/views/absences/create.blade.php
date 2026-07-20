<x-app-layout>
    <x-slot name="header">
        <h2>Nouvelle Absence</h2>
    </x-slot>

    <div class="form-page">
        <div class="form-card">
            <div class="form-card-header">
                <h2 class="form-card-title">➕ Enregistrer une Absence</h2>
                <a href="{{ route('absences.index') }}" class="btn-secondary">← Retour</a>
            </div>

            <form method="POST" action="{{ route('absences.store') }}">
                @csrf
                @include('absences.form')
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Enregistrer</button>
                    <a href="{{ route('absences.index') }}" class="btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .form-page { max-width: 700px; margin: 0 auto; padding: 24px; }
        .form-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; }
        .form-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .form-card-title { font-size: 18px; font-weight: 900; color: #0f172a; }
        .form-actions { margin-top: 24px; display: flex; gap: 10px; }
        .btn-primary { height: 40px; padding: 0 20px; background: #2563eb; color: #fff; border: none; border-radius: 9px; font-size: 13px; font-weight: 900; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { height: 40px; padding: 0 16px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 9px; font-size: 13px; font-weight: 900; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }
    </style>
</x-app-layout>
