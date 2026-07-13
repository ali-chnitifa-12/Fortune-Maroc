@php
    $plan = $plan ?? null;
    $isEdit = $plan && !empty($plan->id);

    $zones = $zones ?? collect();
    $productionLines = $productionLines ?? collect();
    $products = $products ?? collect();
    $shifts = $shifts ?? collect();
    $statuses = $statuses ?? [
        'planned' => 'Planned',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    $action = $action ?? ($isEdit ? route('production-plans.update', $plan) : route('production-plans.store'));
    $method = strtoupper($method ?? ($isEdit ? 'PUT' : 'POST'));
    $buttonText = $buttonText ?? ($isEdit ? 'Update Production Plan' : 'Save Production Plan');

    $selectedDate = old('plan_date', $plan?->plan_date?->format('Y-m-d') ?? now()->toDateString());
    $selectedShiftId = old('shift_id', $plan->shift_id ?? '');
    $selectedZoneId = old('zone_id', $plan->zone_id ?? '');
    $selectedLineId = old('production_line_id', $plan->production_line_id ?? '');
    $selectedProductId = old('product_id', $plan->product_id ?? '');
    $selectedStatus = old('status', $plan->status ?? 'planned');

    $hourStart = old('hour_start', $plan?->hour_start ? substr($plan->hour_start, 0, 5) : '');
    $hourEnd = old('hour_end', $plan?->hour_end ? substr($plan->hour_end, 0, 5) : '');

    $linesByZoneUrl = route('production-plans.lines-by-zone', ['zone' => '__ZONE_ID__']);
    $productsByLineUrl = route('production-plans.products-by-line', ['production_line' => '__LINE_ID__']);
@endphp

<div class="erp-card">
    <form method="POST" action="{{ $action }}" id="productionPlanForm">
        @csrf

        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="plan-form-grid">
            <div>
                <label>{{ __('Plan Date') }} <span class="required">*</span></label>
                <input type="date"
                       name="plan_date"
                       value="{{ $selectedDate }}"
                       required>
            </div>

            <div>
                <label>{{ __('Shift') }} <span class="required">*</span></label>
                <select name="shift_id" id="shift_id" required>
                    <option value="">{{ __('Select shift') }}</option>

                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}"
                                data-start-time="{{ $shift->start_time ? substr($shift->start_time, 0, 5) : '' }}"
                                data-end-time="{{ $shift->end_time ? substr($shift->end_time, 0, 5) : '' }}"
                            {{ (string) $selectedShiftId === (string) $shift->id ? 'selected' : '' }}>
                            {{ $shift->code }} - {{ $shift->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>{{ __('Zone') }} <span class="required">*</span></label>
                <select name="zone_id" id="zone_id" required>
                    <option value="">{{ __('Select zone') }}</option>

                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" {{ (string) $selectedZoneId === (string) $zone->id ? 'selected' : '' }}>
                            {{ $zone->code }} - {{ $zone->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>{{ __('Production Line') }} <span class="required">*</span></label>
                <select name="production_line_id" id="production_line_id" required>
                    <option value="">{{ __('Select zone first') }}</option>
                </select>
                <div class="erp-help-text">
                    {{ __('Lines depend on selected zone.') }}
                </div>
            </div>

            <div>
                <label>{{ __('Product') }} <span class="required">*</span></label>
                <select name="product_id" id="product_id" required>
                    <option value="">{{ __('Select line first') }}</option>
                </select>
                <div class="erp-help-text">
                    {{ __('Products depend on selected line.') }}
                </div>
            </div>

            <div>
                <label>{{ __('Hour Start') }}</label>
                <input type="time"
                       name="hour_start"
                       id="hour_start"
                       value="{{ $hourStart }}"
                       readonly>
                <div class="erp-help-text">
                    {{ __('Automatically filled from selected shift.') }}
                </div>
            </div>

            <div>
                <label>{{ __('Hour End') }}</label>
                <input type="time"
                       name="hour_end"
                       id="hour_end"
                       value="{{ $hourEnd }}"
                       readonly>
                <div class="erp-help-text">
                    {{ __('Automatically filled from selected shift.') }}
                </div>
            </div>

            <div>
                <label>{{ __('Planned Qty') }} <span class="required">*</span></label>
                <input type="number"
                       step="0.01"
                       min="0.01"
                       name="planned_qty"
                       value="{{ old('planned_qty', $plan->planned_qty ?? '') }}"
                       required>
            </div>

            <div>
                <label>{{ __('Target OEE %') }}</label>
                <input type="number"
                       step="0.01"
                       min="0"
                       max="100"
                       name="target_oee"
                       value="{{ old('target_oee', $plan->target_oee ?? '') }}">
            </div>

            <div>
                <label>{{ __('Responsible') }}</label>
                <input type="text"
                       name="responsible"
                       value="{{ old('responsible', $plan->responsible ?? '') }}">
            </div>

            <div>
                <label>{{ __('Status') }}</label>
                <select name="status">
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ (string) $selectedStatus === (string) $value ? 'selected' : '' }}>
                            {{ __($label) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="plan-form-full">
                <label>{{ __('Notes') }}</label>
                <textarea name="notes" rows="5">{{ old('notes', $plan->notes ?? '') }}</textarea>
            </div>
        </div>

        <div id="formLoadMessage" class="plan-load-message" style="display:none;"></div>

        <div class="plan-form-actions">
            <button type="submit" class="erp-btn erp-btn-primary">
                {{ __($buttonText) }}
            </button>

            <a href="{{ route('production-plans.index') }}" class="erp-btn erp-btn-secondary">
                {{ __('Cancel') }}
            </a>
        </div>
    </form>
</div>

<style>
    .plan-form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .plan-form-grid label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 900;
        color: #334155;
    }

    .plan-form-grid input,
    .plan-form-grid select,
    .plan-form-grid textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 13px;
        color: #0f172a;
        background: #ffffff;
    }

    .plan-form-grid input,
    .plan-form-grid select {
        height: 38px;
    }

    .plan-form-grid input[readonly] {
        background: #f8fafc;
        color: #475569;
        cursor: not-allowed;
    }

    .plan-form-grid textarea {
        min-height: 110px;
        resize: vertical;
    }

    .plan-form-full {
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

    .plan-load-message {
        margin-top: 14px;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 13px;
        font-weight: 900;
    }

    .plan-load-message.error {
        border-color: #fecaca;
        background: #fee2e2;
        color: #991b1b;
    }

    .plan-form-actions {
        margin-top: 18px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    @media (max-width: 1100px) {
        .plan-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .plan-form-grid {
            grid-template-columns: 1fr;
        }

        .plan-form-actions {
            flex-direction: column;
        }

        .plan-form-actions .erp-btn {
            width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const zoneSelect = document.getElementById('zone_id');
        const lineSelect = document.getElementById('production_line_id');
        const productSelect = document.getElementById('product_id');
        const shiftSelect = document.getElementById('shift_id');
        const hourStartInput = document.getElementById('hour_start');
        const hourEndInput = document.getElementById('hour_end');
        const messageBox = document.getElementById('formLoadMessage');

        const initialZoneId = @json((string) $selectedZoneId);
        const initialLineId = @json((string) $selectedLineId);
        const initialProductId = @json((string) $selectedProductId);

        const linesByZoneUrl = @json($linesByZoneUrl);
        const productsByLineUrl = @json($productsByLineUrl);

        function buildUrl(template, key, value) {
            return template.replace(key, encodeURIComponent(value));
        }

        function showMessage(message, isError = false) {
            if (!messageBox) {
                return;
            }

            if (!message) {
                messageBox.style.display = 'none';
                messageBox.textContent = '';
                messageBox.classList.remove('error');
                return;
            }

            messageBox.textContent = message;
            messageBox.style.display = 'block';
            messageBox.classList.toggle('error', isError);
        }

        async function fetchJson(url) {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            return await response.json();
        }

        function resetLines(message = '{{ __('Select zone first') }}') {
            lineSelect.innerHTML = '';
            lineSelect.disabled = true;

            const option = document.createElement('option');
            option.value = '';
            option.textContent = message;
            lineSelect.appendChild(option);
        }

        function resetProducts(message = '{{ __('Select line first') }}') {
            productSelect.innerHTML = '';
            productSelect.disabled = true;

            const option = document.createElement('option');
            option.value = '';
            option.textContent = message;
            productSelect.appendChild(option);
        }

        function fillShiftHours() {
            if (!shiftSelect || !hourStartInput || !hourEndInput) {
                return;
            }

            const selectedOption = shiftSelect.options[shiftSelect.selectedIndex];

            if (!selectedOption || !selectedOption.value) {
                hourStartInput.value = '';
                hourEndInput.value = '';
                return;
            }

            hourStartInput.value = selectedOption.getAttribute('data-start-time') || '';
            hourEndInput.value = selectedOption.getAttribute('data-end-time') || '';
        }

        async function loadLines(zoneId, selectedLineId = '') {
            resetLines('{{ __('Loading lines...') }}');
            resetProducts();

            if (!zoneId) {
                resetLines();
                showMessage('');
                return;
            }

            try {
                const url = buildUrl(linesByZoneUrl, '__ZONE_ID__', zoneId);
                const lines = await fetchJson(url);

                lineSelect.innerHTML = '';
                lineSelect.disabled = false;

                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = '{{ __('Select production line') }}';
                lineSelect.appendChild(emptyOption);

                if (!Array.isArray(lines) || lines.length === 0) {
                    resetLines('{{ __('No active lines found for selected zone') }}');
                    showMessage('{{ __('No active production line found for selected zone.') }}', true);
                    return;
                }

                lines.forEach(function (line) {
                    const option = document.createElement('option');
                    option.value = line.id;
                    option.textContent = (line.code || '-') + ' - ' + (line.name || '-');

                    if (String(selectedLineId) === String(line.id)) {
                        option.selected = true;
                    }

                    lineSelect.appendChild(option);
                });

                showMessage('');

                if (lineSelect.value) {
                    await loadProducts(lineSelect.value, initialProductId);
                } else {
                    resetProducts();
                }
            } catch (error) {
                console.error('Production lines loading failed:', error);
                resetLines('{{ __('Error loading lines') }}');
                showMessage('{{ __('Unable to load production lines. Please check routes and permissions.') }}', true);
            }
        }

        async function loadProducts(lineId, selectedProductId = '') {
            resetProducts('{{ __('Loading products...') }}');

            if (!lineId) {
                resetProducts();
                return;
            }

            try {
                const url = buildUrl(productsByLineUrl, '__LINE_ID__', lineId);
                const products = await fetchJson(url);

                productSelect.innerHTML = '';
                productSelect.disabled = false;

                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = '{{ __('Select product') }}';
                productSelect.appendChild(emptyOption);

                if (!Array.isArray(products) || products.length === 0) {
                    resetProducts('{{ __('No active products found for selected line') }}');
                    showMessage('{{ __('No active product is assigned to selected production line.') }}', true);
                    return;
                }

                products.forEach(function (product) {
                    const option = document.createElement('option');
                    option.value = product.id;

                    const standardQty = product.standard_qty_per_hour
                        ? ' / Std: ' + product.standard_qty_per_hour
                        : '';

                    option.textContent = (product.code || '-') + ' - ' + (product.name || '-') + standardQty;

                    if (String(selectedProductId) === String(product.id)) {
                        option.selected = true;
                    }

                    productSelect.appendChild(option);
                });

                showMessage('');
            } catch (error) {
                console.error('Products loading failed:', error);
                resetProducts('{{ __('Error loading products') }}');
                showMessage('{{ __('Unable to load products. Please check product assignment to line.') }}', true);
            }
        }

        if (zoneSelect) {
            zoneSelect.addEventListener('change', function () {
                loadLines(zoneSelect.value, '');
            });
        }

        if (lineSelect) {
            lineSelect.addEventListener('change', function () {
                loadProducts(lineSelect.value, '');
            });
        }

        if (shiftSelect) {
            shiftSelect.addEventListener('change', fillShiftHours);
        }

        resetLines();
        resetProducts();
        fillShiftHours();

        if (initialZoneId) {
            loadLines(initialZoneId, initialLineId);
        }
    });
</script>