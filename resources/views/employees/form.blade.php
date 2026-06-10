@php
    $currentFullName = old('full_name', $employee->full_name ?? '');
    $currentMatricule = old('matricule', $employee->matricule ?? '');
    $currentDepartment = old('department', $employee->department ?? '');
    $currentPosition = old('position', $employee->position ?? '');
    $currentProductionLineId = old('production_line_id', $employee->production_line_id ?? '');
    $currentIsActive = old('is_active', isset($employee->is_active) ? (int) $employee->is_active : 1);
    $currentDepartureDate = old('departure_date', optional($employee->departure_date ?? null)->format('Y-m-d'));
    $currentDepartureReason = old('departure_reason', $employee->departure_reason ?? '');
@endphp

<div class="employee-form-grid">
    <div>
        <label>Nom complet</label>
        <input
            type="text"
            name="full_name"
            value="{{ $currentFullName }}"
            placeholder="Nom complet de l'employé"
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
            placeholder="Poste de l'employé"
        >
    </div>

    <div>
        <label>Ligne de production</label>
        <select name="production_line_id">
            <option value="">-- Sélectionner une ligne --</option>
            @foreach($productionLines as $line)
                <option value="{{ $line->id }}" {{ (string) $currentProductionLineId === (string) $line->id ? 'selected' : '' }}>
                    {{ $line->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Statut</label>
        <select name="is_active">
            <option value="1" {{ (string) $currentIsActive === '1' ? 'selected' : '' }}>
                Actif
            </option>
            <option value="0" {{ (string) $currentIsActive === '0' ? 'selected' : '' }}>
                Départ / Inactif
            </option>
        </select>
    </div>

    <div>
        <label>Date de départ</label>
        <input
            type="date"
            name="departure_date"
            value="{{ $currentDepartureDate }}"
        >
    </div>

    <div class="employee-form-full">
        <label>Motif de départ</label>
        <input
            type="text"
            name="departure_reason"
            value="{{ $currentDepartureReason }}"
            placeholder="Ex: Démission, fin de contrat, mutation..."
        >
    </div>
</div>

<style>
    .employee-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .employee-form-full {
        grid-column: 1 / -1;
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