<div class="erp-page-wrap">
    @if ($errors->any())
        <div class="fortune-error">
            <ul style="list-style:disc;margin-left:20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="erp-card">
        <form method="POST" action="{{ $action }}">
            @csrf

            @if($method !== 'POST')
                @method($method)
            @endif

            <div class="erp-form-grid">
                <div>
                    <label>Plan Date</label>
                    <input type="date"
                           name="plan_date"
                           value="{{ old('plan_date', $plan?->plan_date?->format('Y-m-d') ?? date('Y-m-d')) }}"
                           required>
                </div>

                <div>
                    <label>Shift</label>
                    <select name="shift_id" required>
                        <option value="">Select shift</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}"
                                {{ old('shift_id', $plan?->shift_id) == $shift->id ? 'selected' : '' }}>
                                {{ $shift->code }} - {{ $shift->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Zone</label>
                    <select id="zone_id" name="zone_id" required>
                        <option value="">Select zone</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}"
                                {{ old('zone_id', $plan?->zone_id) == $zone->id ? 'selected' : '' }}>
                                {{ $zone->code }} - {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Production Line</label>
                    <select id="production_line_id" name="production_line_id" required>
                        <option value="">Select zone first</option>
                    </select>
                    <div id="line_help" class="erp-muted-small">
                        Lines depend on selected zone.
                    </div>
                </div>

                <div>
                    <label>Product</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">Select line first</option>
                    </select>
                    <div id="product_help" class="erp-muted-small">
                        Products depend on selected line.
                    </div>
                </div>

                <div>
                    <label>Hour Start</label>
                    <input type="time"
                           name="hour_start"
                           value="{{ old('hour_start', $plan?->hour_start ? substr($plan->hour_start, 0, 5) : '') }}"
                           required>
                </div>

                <div>
                    <label>Hour End</label>
                    <input type="time"
                           name="hour_end"
                           value="{{ old('hour_end', $plan?->hour_end ? substr($plan->hour_end, 0, 5) : '') }}"
                           required>
                </div>

                <div>
                    <label>Planned Qty</label>
                    <input id="planned_qty"
                           type="number"
                           step="0.01"
                           name="planned_qty"
                           value="{{ old('planned_qty', $plan?->planned_qty) }}"
                           required>
                </div>

                <div>
                    <label>Target OEE %</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           max="100"
                           name="target_oee"
                           value="{{ old('target_oee', $plan?->target_oee) }}">
                </div>

                <div>
                    <label>Responsible</label>
                    <input type="text"
                           name="responsible"
                           value="{{ old('responsible', $plan?->responsible) }}">
                </div>

                <div>
                    <label>Status</label>
                    <select name="status" required>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}"
                                {{ old('status', $plan?->status ?? 'planned') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="erp-form-section">
                <label>Notes</label>
                <textarea name="notes" rows="4">{{ old('notes', $plan?->notes) }}</textarea>
            </div>

            <div class="erp-form-actions">
                <button type="submit" class="erp-btn erp-btn-primary">
                    {{ $buttonText }}
                </button>

                <a href="{{ route('production-plans.index') }}" class="erp-btn erp-btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@include('components.erp-page-style')

<script>
    const selectedZoneId = "{{ old('zone_id', $plan?->zone_id) }}";
    const selectedLineId = "{{ old('production_line_id', $plan?->production_line_id) }}";
    const selectedProductId = "{{ old('product_id', $plan?->product_id) }}";

    function resetSelect(selectId, placeholder) {
        const select = document.getElementById(selectId);
        select.innerHTML = '<option value="">' + placeholder + '</option>';
    }

    function loadLinesByZone(zoneId, selectedLineId = null) {
        resetSelect('production_line_id', 'Loading lines...');
        resetSelect('product_id', 'Select line first');

        if (!zoneId) {
            resetSelect('production_line_id', 'Select zone first');
            document.getElementById('line_help').innerText = 'Lines depend on selected zone.';
            return;
        }

        fetch(`/zones/${zoneId}/production-lines`)
            .then(response => response.json())
            .then(lines => {
                const lineSelect = document.getElementById('production_line_id');
                lineSelect.innerHTML = '<option value="">Select production line</option>';

                lines.forEach(line => {
                    const option = document.createElement('option');
                    option.value = line.id;
                    option.textContent = line.code + ' - ' + line.name;

                    if (selectedLineId && String(line.id) === String(selectedLineId)) {
                        option.selected = true;
                    }

                    lineSelect.appendChild(option);
                });

                document.getElementById('line_help').innerText = lines.length + ' line(s) in selected zone.';

                if (selectedLineId) {
                    loadProductsByLine(selectedLineId, selectedProductId);
                }
            });
    }

    function loadProductsByLine(lineId, selectedProductId = null) {
        resetSelect('product_id', 'Loading products...');

        if (!lineId) {
            resetSelect('product_id', 'Select line first');
            document.getElementById('product_help').innerText = 'Products depend on selected line.';
            return;
        }

        fetch(`/production-lines/${lineId}/products`)
            .then(response => response.json())
            .then(products => {
                const productSelect = document.getElementById('product_id');
                productSelect.innerHTML = '<option value="">Select product</option>';

                products.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = product.code + ' - ' + product.name;
                    option.dataset.standardQty = product.standard_qty_per_hour ?? '';

                    if (selectedProductId && String(product.id) === String(selectedProductId)) {
                        option.selected = true;
                    }

                    productSelect.appendChild(option);
                });

                document.getElementById('product_help').innerText = products.length + ' product(s) assigned to selected line.';

                if (selectedProductId) {
                    fillPlannedQtyFromProduct();
                }
            });
    }

    function fillPlannedQtyFromProduct() {
        const productSelect = document.getElementById('product_id');
        const selectedOption = productSelect.options[productSelect.selectedIndex];

        if (selectedOption && selectedOption.dataset.standardQty) {
            document.getElementById('planned_qty').value = selectedOption.dataset.standardQty;
        }
    }

    document.getElementById('zone_id').addEventListener('change', function () {
        document.getElementById('planned_qty').value = '';
        loadLinesByZone(this.value);
    });

    document.getElementById('production_line_id').addEventListener('change', function () {
        document.getElementById('planned_qty').value = '';
        loadProductsByLine(this.value);
    });

    document.getElementById('product_id').addEventListener('change', function () {
        fillPlannedQtyFromProduct();
    });

    if (selectedZoneId) {
        loadLinesByZone(selectedZoneId, selectedLineId);
    }
</script>