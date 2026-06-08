@php
    $currentFullName = old('full_name', $employee->full_name ?? '');
    $currentMatricule = old('matricule', $employee->matricule ?? '');
    $currentDepartment = old('department', $employee->department ?? '');
    $currentPosition = old('position', $employee->position ?? '');
    $currentIsActive = old('is_active', $employee->is_active ?? true);
@endphp

<div class="employee-form-grid">
    <div>
        <label>Nom complet</label>
        <input
            type="text"
            name="full_name"
            value="{{ $currentFullName }}"
            placeholder="Nom complet de l’employé"
            required
        >
    </div>

    <div>
        <label>Matricule</label>
        <input
            type="text"
            name="matricule"
            value="{{ $currentMatricule }}"
            placeholder="Matricule"
        >
    </div>

    <div>
        <label>Service</label>
        <input
            type="text"
            name="department"
            value="{{ $currentDepartment }}"
            placeholder="Ex: Production, RH, Maintenance"
        >
    </div>

    <div>
        <label>Poste</label>
        <input
            type="text"
            name="position"
            value="{{ $currentPosition }}"
            placeholder="Poste de l’employé"
        >
    </div>

    <div>
        <label>Statut</label>
        <select name="is_active">
            <option value="1" {{ $currentIsActive ? 'selected' : '' }}>Actif</option>
            <option value="0" {{ !$currentIsActive ? 'selected' : '' }}>Inactif</option>
        </select>
    </div>
</div>

<style>
    .employee-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .employee-form-grid label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 900;
        color: #334155;
    }

    .employee-form-grid input,
    .employee-form-grid select {
        width: 100%;
        height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 13px;
        color: #0f172a;
        background: #ffffff;
    }

    .erp-form-actions {
        margin-top: 18px;
        display: flex;
        gap: 10px;
    }

    @media (max-width: 768px) {
        .employee-form-grid {
            grid-template-columns: 1fr;
        }

        .erp-form-actions {
            flex-direction: column;
        }
    }
</style>