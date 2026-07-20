@php
    $editingAbsence = $absence ?? null;
@endphp

@if($errors->any())
    <div class="form-errors">
        @foreach($errors->all() as $error)
            <div>⚠ {{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="absence-form-grid">
    <div>
        <label>Employé <span class="required">*</span></label>
        <select name="user_id" required>
            <option value="">-- Sélectionner un employé --</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}"
                    {{ old('user_id', $editingAbsence?->user_id) == $u->id ? 'selected' : '' }}>
                    {{ $u->name }} ({{ $u->role }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Date <span class="required">*</span></label>
        <input type="date" name="date"
               value="{{ old('date', $editingAbsence?->date?->format('Y-m-d')) }}" required>
    </div>

    <div>
        <label>Type <span class="required">*</span></label>
        <select name="type" required>
            @foreach($types as $key => $label)
                <option value="{{ $key }}"
                    {{ old('type', $editingAbsence?->type ?? 'absence') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Statut <span class="required">*</span></label>
        <select name="statut" required>
            @foreach($statuts as $key => $label)
                <option value="{{ $key }}"
                    {{ old('statut', $editingAbsence?->statut ?? 'pending') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-full">
        <label>Motif</label>
        <input type="text" name="motif"
               value="{{ old('motif', $editingAbsence?->motif) }}"
               placeholder="Ex: Rendez-vous médical, Raison familiale...">
    </div>

    <div class="form-full">
        <label>Notes</label>
        <textarea name="notes" rows="3"
                  placeholder="Informations complémentaires...">{{ old('notes', $editingAbsence?->notes) }}</textarea>
    </div>
</div>

<style>
    .absence-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-full { grid-column: 1 / -1; }
    .absence-form-grid label { display: block; font-size: 12px; font-weight: 900; color: #334155; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.03em; }
    .absence-form-grid input,
    .absence-form-grid select,
    .absence-form-grid textarea { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 600; color: #0f172a; background: #fff; }
    .absence-form-grid input:focus,
    .absence-form-grid select:focus,
    .absence-form-grid textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    .absence-form-grid textarea { height: 80px; resize: vertical; }
    .required { color: #dc2626; }
    .form-errors { background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 12px 16px; margin-bottom: 18px; font-size: 13px; font-weight: 700; color: #dc2626; }
    @media(max-width: 600px) { .absence-form-grid { grid-template-columns: 1fr; } }
</style>
