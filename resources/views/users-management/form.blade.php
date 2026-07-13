@php
    $formUser = $user ?? null;
    $isEdit = $formUser && !empty($formUser->id);

    $zones = $zones ?? collect();
    $productionLines = $productionLines ?? collect();
    $selectedZones = $selectedZones ?? [];

    if (!is_array($selectedZones)) {
        $selectedZones = collect($selectedZones)->map(fn ($id) => (int) $id)->toArray();
    }

    $currentRole = old('role', $formUser->role ?? 'operator');
    $currentLineId = old('production_line_id', $formUser->production_line_id ?? '');
    $currentStatus = old('is_active', $isEdit ? (($formUser->is_active ?? true) ? '1' : '0') : '1');
    $buttonText = $buttonText ?? ($isEdit ? 'Update User' : 'Save User');
@endphp

<div class="user-form-grid">
    <div>
        <label>{{ __('Name') }} <span class="required">*</span></label>
        <input type="text"
               name="name"
               value="{{ old('name', $formUser->name ?? '') }}"
               required>
    </div>

    <div>
        <label>{{ __('Email') }} <span class="required">*</span></label>
        <input type="email"
               name="email"
               value="{{ old('email', $formUser->email ?? '') }}"
               required>
    </div>

    <div>
        <label>
            {{ __('Password') }}
            @if($isEdit)
                <span class="field-note">({{ __('leave empty to keep current') }})</span>
            @else
                <span class="required">*</span>
            @endif
        </label>

        <input type="password"
               name="password"
               {{ $isEdit ? '' : 'required' }}>
    </div>

    <div>
        <label>
            {{ __('Confirm Password') }}
            @if(!$isEdit)
                <span class="required">*</span>
            @endif
        </label>

        <input type="password"
               name="password_confirmation"
               {{ $isEdit ? '' : 'required' }}>
    </div>

    <div>
        <label>{{ __('Role') }} <span class="required">*</span></label>
        <select name="role" id="role_select" required>
            <option value="operator" {{ $currentRole === 'operator' ? 'selected' : '' }}>
                {{ __('Operator') }}
            </option>

            <option value="supervisor" {{ $currentRole === 'supervisor' ? 'selected' : '' }}>
                {{ __('Supervisor') }}
            </option>

            <option value="responsable_production" {{ $currentRole === 'responsable_production' ? 'selected' : '' }}>
                {{ __('Responsable Production') }}
            </option>

            <option value="rh" {{ $currentRole === 'rh' ? 'selected' : '' }}>
                {{ __('RH') }}
            </option>

            <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>
                {{ __('Admin') }}
            </option>
        </select>
    </div>

    <div>
        <label>{{ __('Status') }} <span class="required">*</span></label>
        <select name="is_active" required>
            <option value="1" {{ (string) $currentStatus === '1' ? 'selected' : '' }}>
                {{ __('Active') }}
            </option>

            <option value="0" {{ (string) $currentStatus === '0' ? 'selected' : '' }}>
                {{ __('Inactive') }}
            </option>
        </select>
    </div>

    <div id="operator_line_block" class="role-block">
        <label>{{ __('Production Line') }} <span class="required">*</span></label>
        <select name="production_line_id" id="production_line_id">
            <option value="">{{ __('Select production line') }}</option>

            @foreach($productionLines as $line)
                <option value="{{ $line->id }}" {{ (string) $currentLineId === (string) $line->id ? 'selected' : '' }}>
                    {{ $line->code }} - {{ $line->name }}
                    @if($line->zone)
                        / {{ $line->zone->code }}
                    @endif
                </option>
            @endforeach
        </select>

        <div class="form-help">
            {{ __('Required only for Operator role.') }}
        </div>
    </div>

    <div id="supervisor_zone_block" class="role-block user-form-full">
        <label>{{ __('Assigned Zones') }} <span class="required">*</span></label>

        <div class="zone-checkbox-grid">
            @foreach($zones as $zone)
                @php
                    $checkedZones = old('zone_ids', $selectedZones);
                    if (!is_array($checkedZones)) {
                        $checkedZones = [];
                    }
                @endphp

                <label class="zone-checkbox">
                    <input type="checkbox"
                           name="zone_ids[]"
                           value="{{ $zone->id }}"
                        {{ in_array((int) $zone->id, array_map('intval', $checkedZones), true) ? 'checked' : '' }}>
                    <span>{{ $zone->code }} - {{ $zone->name }}</span>
                </label>
            @endforeach
        </div>

        <div class="form-help">
            {{ __('Required only for Supervisor role.') }}
        </div>
    </div>

    <div id="full_access_block" class="role-info user-form-full">
        {{ __('This role has access according to its global permissions. No zone or line assignment is required.') }}
    </div>

    <div id="rh_access_block" class="role-info user-form-full">
        {{ __('RH role has access only to HR modules: HR Dashboard, Employees and Absences.') }}
    </div>
</div>

<div class="erp-form-actions">
    <button type="submit" class="erp-btn erp-btn-primary">
        {{ __($buttonText) }}
    </button>

    <a href="{{ route('users-management.index') }}" class="erp-btn erp-btn-secondary">
        {{ __('Cancel') }}
    </a>
</div>

<style>
    .user-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .user-form-full {
        grid-column: 1 / -1;
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
        height: 38px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 7px 10px;
        background: #ffffff;
        color: #0f172a;
        font-size: 13px;
        font-weight: 700;
    }

    .required {
        color: #dc2626;
    }

    .field-note {
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
    }

    .form-help {
        margin-top: 5px;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
    }

    .role-block {
        display: none;
    }

    .role-block.active {
        display: block;
    }

    .role-info {
        display: none;
        padding: 12px 14px;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 13px;
        font-weight: 900;
    }

    .role-info.active {
        display: block;
    }

    .zone-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }

    .zone-checkbox {
        display: flex !important;
        align-items: center;
        gap: 8px;
        margin: 0 !important;
        padding: 9px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #ffffff;
        cursor: pointer;
    }

    .zone-checkbox input {
        width: auto;
        height: auto;
    }

    .zone-checkbox span {
        font-size: 12px;
        font-weight: 800;
        color: #334155;
    }

    .erp-form-actions {
        margin-top: 18px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    @media (max-width: 900px) {
        .user-form-grid {
            grid-template-columns: 1fr;
        }

        .zone-checkbox-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    function updateUserRoleBlocks() {
        const roleSelect = document.getElementById('role_select');
        const operatorBlock = document.getElementById('operator_line_block');
        const supervisorBlock = document.getElementById('supervisor_zone_block');
        const fullAccessBlock = document.getElementById('full_access_block');
        const rhAccessBlock = document.getElementById('rh_access_block');
        const productionLineSelect = document.getElementById('production_line_id');

        if (!roleSelect) {
            return;
        }

        const role = roleSelect.value;

        if (operatorBlock) {
            operatorBlock.classList.toggle('active', role === 'operator');
        }

        if (supervisorBlock) {
            supervisorBlock.classList.toggle('active', role === 'supervisor');
        }

        if (fullAccessBlock) {
            fullAccessBlock.classList.toggle('active', ['admin', 'responsable_production'].includes(role));
        }

        if (rhAccessBlock) {
            rhAccessBlock.classList.toggle('active', role === 'rh');
        }

        if (productionLineSelect) {
            productionLineSelect.required = role === 'operator';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('role_select');

        if (roleSelect) {
            roleSelect.addEventListener('change', updateUserRoleBlocks);
        }

        updateUserRoleBlocks();
    });
</script>