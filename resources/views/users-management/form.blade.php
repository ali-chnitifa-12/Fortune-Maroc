@php
    $currentRole = old('role', $user->role ?? 'operator');
    $currentLineId = old('production_line_id', $user->production_line_id ?? '');
    $oldZoneIds = old('zone_ids', $selectedZones ?? []);
@endphp

<div class="user-form-grid">
    <div>
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required>
    </div>

    <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
    </div>

    <div>
        <label>Password {{ $isEdit ? '(leave empty to keep current)' : '' }}</label>
        <input type="password" name="password" {{ $isEdit ? '' : 'required' }}>
    </div>

    <div>
        <label>Role</label>
        <select name="role" id="role_select" required>
            <option value="operator" {{ $currentRole === 'operator' ? 'selected' : '' }}>Operator</option>
            <option value="responsable_production" {{ $currentRole === 'responsable_production' ? 'selected' : '' }}>Responsable Production</option>
            <option value="supervisor" {{ $currentRole === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
            <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
    </div>

    <div id="operator_line_block">
        <label>Production Line for Operator</label>
        <select name="production_line_id">
            <option value="">Select production line</option>
            @foreach($productionLines as $line)
                <option value="{{ $line->id }}" {{ (string) $currentLineId === (string) $line->id ? 'selected' : '' }}>
                    {{ $line->code }} - {{ $line->name }}
                    @if($line->zone)
                        / {{ $line->zone->code }}
                    @endif
                </option>
            @endforeach
        </select>
        <div class="erp-help-text">
            Operator can see only this production line.
        </div>
    </div>

    <div id="supervisor_zone_block">
        <label>Zones for Supervisor</label>
        <select name="zone_ids[]" multiple size="6">
            @foreach($zones as $zone)
                <option value="{{ $zone->id }}" {{ in_array($zone->id, array_map('intval', $oldZoneIds), true) ? 'selected' : '' }}>
                    {{ $zone->code }} - {{ $zone->name }}
                </option>
            @endforeach
        </select>
        <div class="erp-help-text">
            Hold CTRL to select multiple zones. Supervisor sees all lines inside selected zones.
        </div>
    </div>

    <div id="full_access_block">
        <label>Assignment</label>
        <div class="readonly-box">
            This role has access to all zones and all production lines.
        </div>
    </div>

    <div>
        <label>Status</label>
        <select name="is_active">
            <option value="1" {{ old('is_active', $user->is_active ?? true) ? 'selected' : '' }}>Active</option>
            <option value="0" {{ !old('is_active', $user->is_active ?? true) ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</div>

<style>
    .user-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .user-form-grid label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 900;
        color: #334155;
    }

    .user-form-grid input,
    .user-form-grid select {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 13px;
        color: #0f172a;
        background: #ffffff;
    }

    .user-form-grid input,
    .user-form-grid select:not([multiple]) {
        height: 38px;
    }

    .erp-help-text {
        margin-top: 5px;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
    }

    .readonly-box {
        min-height: 38px;
        display: flex;
        align-items: center;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #f8fafc;
        padding: 8px 10px;
        font-size: 13px;
        font-weight: 800;
        color: #475569;
    }

    .erp-form-actions {
        margin-top: 18px;
        display: flex;
        gap: 10px;
    }

    @media (max-width: 768px) {
        .user-form-grid {
            grid-template-columns: 1fr;
        }

        .erp-form-actions {
            flex-direction: column;
        }
    }
</style>

<script>
    const roleSelect = document.getElementById('role_select');
    const operatorLineBlock = document.getElementById('operator_line_block');
    const supervisorZoneBlock = document.getElementById('supervisor_zone_block');
    const fullAccessBlock = document.getElementById('full_access_block');

    function refreshAssignmentFields() {
        const role = roleSelect.value;

        operatorLineBlock.style.display = 'none';
        supervisorZoneBlock.style.display = 'none';
        fullAccessBlock.style.display = 'none';

        if (role === 'operator') {
            operatorLineBlock.style.display = 'block';
            return;
        }

        if (role === 'supervisor') {
            supervisorZoneBlock.style.display = 'block';
            return;
        }

        if (role === 'responsable_production' || role === 'admin') {
            fullAccessBlock.style.display = 'block';
        }
    }

    roleSelect.addEventListener('change', refreshAssignmentFields);
    refreshAssignmentFields();
</script>