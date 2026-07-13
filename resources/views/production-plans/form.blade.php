@php
    $isEdit = isset($plan);

    $selectedZoneId = old('zone_id', $plan->zone_id ?? '');
    $selectedLineId = old('production_line_id', $plan->production_line_id ?? '');
    $selectedProductId = old('product_id', $plan->product_id ?? '');
    $selectedShiftId = old('shift_id', $plan->shift_id ?? '');
    $selectedStatus = old('status', $plan->status ?? 'planned');

    $planDate = old('plan_date', isset($plan) && $plan->plan_date ? $plan->plan_date->format('Y-m-d') : now()->format('Y-m-d'));
@endphp

<div class="erp-card">
    <form method="POST" action="{{ $isEdit ? route('production-plans.update', $plan) : route('production-plans.store') }}">
        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        <div class="plan-note">
            <strong>Shift generation logic:</strong>
            The system generates hourly production entries automatically from the selected shift.
            Planned Qty is the total shift quantity and will be divided by the generated hours.
        </div>

        <div class="plan-grid">
            @if($isEdit)
                <div>
                    <label>Plan Code</label>
                    <input type="text" value="{{ $plan->plan_code ?? '-' }}" readonly>
                </div>
            @endif

            <div>
                <label>Plan Date <span class="required">*</span></label>
                <input type="date" name="plan_date" value="{{ $planDate }}" required>
            </div>

            <div>
                <label>Shift <span class="required">*</span></label>
                <select name="shift_id" required>
                    <option value="">Select shift</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" {{ (string) $selectedShiftId === (string) $shift->id ? 'selected' : '' }}>
                            {{ $shift->code }} - {{ $shift->name }}
                            @if($shift->start_time && $shift->end_time)
                                ({{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <div class="erp-help-text">Hours will be generated from shift setup.</div>
            </div>

            <div>
                <label>Zone <span class="required">*</span></label>
                <select name="zone_id" id="zone_id" required>
                    <option value="">Select zone</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" {{ (string) $selectedZoneId === (string) $zone->id ? 'selected' : '' }}>
                            {{ $zone->code }} - {{ $zone->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Production Line <span class="required">*</span></label>
                <select name="production_line_id" id="production_line_id" required>
                    <option value="">Select zone first</option>
                </select>
                <div class="erp-help-text">Lines depend on selected zone.</div>
            </div>

            <div>
                <label>Product <span class="required">*</span></label>
                <select name="product_id" id="product_id" required>
                    <option value="">Select line first</option>
                </select>
                <div class="erp-help-text">Products depend on selected line.</div>
            </div>

            <div>
                <label>Total Planned Qty for Shift <span class="required">*</span></label>
                <input type="number"
                       name="planned_qty"
                       step="0.01"
                       min="0.01"
                       value="{{ old('planned_qty', $plan->planned_qty ?? '') }}"
                       required>
                <div class="erp-help-text">This total will be divided by generated hourly entries.</div>
            </div>

            <div>
                <label>Target OEE %</label>
                <input type="number"
                       name="target_oee"
                       step="0.01"
                       min="0"
                       max="100"
                       value="{{ old('target_oee', $plan->target_oee ?? '') }}">
            </div>

            <div>
                <label>Responsible</label>
                <input type="text"
                       name="responsible"
                       value="{{ old('responsible', $plan->responsible ?? '') }}">
            </div>

            @if($isEdit)
                <div>
                    <label>Status <span class="required">*</span></label>
                    <select name="status" required>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ $selectedStatus === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="status" value="planned">
            @endif

            <div class="plan-full">
                <label>Notes</label>
                <textarea name="notes" rows="5">{{ old('notes', $plan->notes ?? '') }}</textarea>
            </div>
        </div>

        <div class="erp-form-actions">
            <button type="submit" class="erp-btn erp-btn-primary">
                {{ $isEdit ? 'Update Production Plan' : 'Create Plan & Generate Entries' }}
            </button>

            <a href="{{ route('production-plans.index') }}" class="erp-btn erp-btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

<style>
    .plan-note {
        margin-bottom: 16px;
        padding: 12px 14px;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.45;
    }

    .plan-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .plan-grid label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 900;
        color: #334155;
    }

    .plan-grid input,
    .plan-grid select,
    .plan-grid textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 13px;
        color: #0f172a;
        background: #ffffff;
    }

    .plan-grid input,
    .plan-grid select {
        height: 38px;
    }

    .plan-grid input[readonly] {
        background: #f8fafc;
        color: #475569;
    }

    .plan-full {
        grid-column: 1 / -1;
    }

    .required {
        color: #dc2626;
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
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    @media (max-width: 1100px) {
        .plan-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .plan-grid {
            grid-template-columns: 1fr;
        }

        .erp-form-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .erp-form-actions .erp-btn {
            width: 100%;
        }
    }
</style>

<script>
    const selectedZoneId = @json((string) $selectedZoneId);
    const selectedLineId = @json((string) $selectedLineId);
    const selectedProductId = @json((string) $selectedProductId);

    const zoneSelect = document.getElementById('zone_id');
    const lineSelect = document.getElementById('production_line_id');
    const productSelect = document.getElementById('product_id');

    function resetLines(message = 'Select zone first') {
        lineSelect.innerHTML = `<option value="">${message}</option>`;
    }

    function resetProducts(message = 'Select line first') {
        productSelect.innerHTML = `<option value="">${message}</option>`;
    }

    function loadLines(zoneId, preselectedLineId = '') {
        resetLines('Loading lines...');
        resetProducts('Select line first');

        if (!zoneId) {
            resetLines();
            return;
        }

        fetch(`/production-plans/lines-by-zone/${zoneId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Cannot load production lines');
                }

                return response.json();
            })
            .then(lines => {
                lineSelect.innerHTML = '<option value="">Select production line</option>';

                if (!lines.length) {
                    resetLines('No active line found for this zone');
                    return;
                }

                lines.forEach(line => {
                    const option = document.createElement('option');
                    option.value = line.id;
                    option.textContent = `${line.code} - ${line.name}`;

                    if (String(preselectedLineId) === String(line.id)) {
                        option.selected = true;
                    }

                    lineSelect.appendChild(option);
                });

                if (preselectedLineId) {
                    loadProducts(preselectedLineId, selectedProductId);
                }
            })
            .catch(error => {
                console.error(error);
                resetLines('Error loading lines');
                resetProducts('Select line first');
            });
    }

    function loadProducts(lineId, preselectedProductId = '') {
        resetProducts('Loading products...');

        if (!lineId) {
            resetProducts();
            return;
        }

        fetch(`/production-plans/products-by-line/${lineId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Cannot load products');
                }

                return response.json();
            })
            .then(products => {
                productSelect.innerHTML = '<option value="">Select product</option>';

                if (!products.length) {
                    resetProducts('No active product assigned to this line');
                    return;
                }

                products.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = `${product.code} - ${product.name}`;

                    if (String(preselectedProductId) === String(product.id)) {
                        option.selected = true;
                    }

                    productSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error(error);
                resetProducts('Error loading products');
            });
    }

    zoneSelect.addEventListener('change', function () {
        loadLines(this.value);
    });

    lineSelect.addEventListener('change', function () {
        loadProducts(this.value);
    });

    document.addEventListener('DOMContentLoaded', function () {
        if (selectedZoneId) {
            loadLines(selectedZoneId, selectedLineId);
        } else {
            resetLines();
            resetProducts();
        }
    });
</script>