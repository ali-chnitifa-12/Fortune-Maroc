@php
    $currentEmployeeId = old('employee_id', $absence->employee_id ?? '');
    $currentEmployeeName = old('employee_name', $absence->employee_name ?? '');
    $currentDate = old('absence_date', optional($absence->absence_date ?? null)->format('Y-m-d') ?? now()->toDateString());
    $currentShift = old('shift', $absence->shift ?? '');
    $currentReason = old('reason', $absence->reason ?? '');
    $currentHours = old('hours', $absence->hours ?? '');
    $currentComment = old('comment', $absence->comment ?? '');
@endphp

<div class="absence-form-grid">
    <div>
        <label>Employé enregistré</label>
        <select name="employee_id" id="employee_select">
            <option value="">Saisie manuelle</option>
            @foreach($employees as $employee)
                <option
                    value="{{ $employee->id }}"
                    data-name="{{ $employee->full_name }}"
                    {{ (string) $currentEmployeeId === (string) $employee->id ? 'selected' : '' }}
                >
                    {{ $employee->full_name }}
                    @if($employee->matricule)
                        — {{ $employee->matricule }}
                    @endif
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Nom complet</label>
        <input
            type="text"
            name="employee_name"
            id="employee_name_input"
            value="{{ $currentEmployeeName }}"
            placeholder="Nom complet de l’employé"
        >
        <div class="erp-help-text">
            Choisir un employé enregistré ou saisir le nom manuellement.
        </div>
    </div>

    <div>
        <label>Date</label>
        <input
            type="date"
            name="absence_date"
            value="{{ $currentDate }}"
            required
        >
    </div>

    <div>
        <label>Shift</label>
        <select name="shift">
            <option value="">Choisir un shift</option>
            @foreach($shifts as $shift)
                <option value="{{ $shift }}" {{ $currentShift === $shift ? 'selected' : '' }}>
                    {{ $shift }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Motif</label>
        <select name="reason" required>
            <option value="">Choisir un motif</option>
            @foreach($reasons as $reason)
                <option value="{{ $reason }}" {{ $currentReason === $reason ? 'selected' : '' }}>
                    {{ $reason }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Heures d’absence</label>
        <input
            type="number"
            name="hours"
            value="{{ $currentHours }}"
            min="0"
            max="24"
            step="0.25"
            placeholder="8"
            required
        >
    </div>

    <div class="absence-form-full">
        <label>Commentaire</label>
        <textarea
            name="comment"
            rows="4"
            placeholder="Commentaire optionnel"
        >{{ $currentComment }}</textarea>
    </div>
</div>

<style>
    .absence-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .absence-form-full {
        grid-column: 1 / -1;
    }

    .absence-form-grid label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 900;
        color: #334155;
    }

    .absence-form-grid input,
    .absence-form-grid select,
    .absence-form-grid textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 13px;
        color: #0f172a;
        background: #ffffff;
    }

    .absence-form-grid input,
    .absence-form-grid select {
        height: 38px;
    }

    .absence-form-grid textarea {
        resize: vertical;
    }

    .erp-help-text {
        margin-top: 5px;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
    }

    .erp-form-actions {
        margin-top: 18px;
        display: flex;
        gap: 10px;
    }

    @media (max-width: 768px) {
        .absence-form-grid {
            grid-template-columns: 1fr;
        }

        .erp-form-actions {
            flex-direction: column;
        }
    }
</style>

<script>
    const employeeSelect = document.getElementById('employee_select');
    const employeeNameInput = document.getElementById('employee_name_input');

    function syncEmployeeName() {
        const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            employeeNameInput.readOnly = false;
            return;
        }

        employeeNameInput.value = selectedOption.dataset.name || '';
        employeeNameInput.readOnly = true;
    }

    employeeSelect.addEventListener('change', syncEmployeeName);
    syncEmployeeName();
</script>