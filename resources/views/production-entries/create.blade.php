<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            Add Production Entry
        </h2>
        <div class="fortune-header-subtitle">
            Production entry created from production planning.
        </div>
    </x-slot>

    @php
        $selectedPlan = $productionPlan ?? null;
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6">

                @if ($errors->any())
                    <div class="fortune-error">
                        <ul style="list-style:disc;margin-left:20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!$selectedPlan)
                    <div class="fortune-error">
                        Production entry must be created from Production Planning.
                    </div>
                @endif

                @if($selectedPlan)
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:10px;padding:12px;margin-bottom:16px;font-weight:800;">
                        Production Plan:
                        {{ $selectedPlan->plan_date?->format('Y-m-d') }}
                        |
                        {{ substr($selectedPlan->hour_start, 0, 5) }} - {{ substr($selectedPlan->hour_end, 0, 5) }}
                        |
                        {{ $selectedPlan->shift?->code }}
                        |
                        {{ $selectedPlan->machine?->code }}
                        |
                        {{ $selectedPlan->product?->code }}
                    </div>
                @endif

                <form method="POST" action="{{ route('production-entries.store') }}">
                    @csrf

                    <input type="hidden"
                           id="production_plan_id"
                           name="production_plan_id"
                           value="{{ old('production_plan_id', $selectedPlan?->id) }}">

                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;">
                        <div>
                            <label>Production Date</label>
                            <input id="production_date"
                                   type="date"
                                   name="production_date"
                                   value="{{ old('production_date', $selectedPlan?->plan_date?->format('Y-m-d')) }}"
                                   required
                                   readonly
                                   style="width:100%;background:#f9fafb;">
                        </div>

                        <div>
                            <label>Shift</label>
                            <select id="shift_id" name="shift_id" required readonly style="width:100%;background:#f9fafb;pointer-events:none;">
                                <option value="">Select shift</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}"
                                        {{ old('shift_id', $selectedPlan?->shift_id) == $shift->id ? 'selected' : '' }}>
                                        {{ $shift->code }} - {{ $shift->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Machine</label>
                            <select id="machine_id" name="machine_id" required readonly style="width:100%;background:#f9fafb;pointer-events:none;">
                                <option value="">Select machine</option>
                                @foreach($machines as $machine)
                                    <option value="{{ $machine->id }}"
                                        {{ old('machine_id', $selectedPlan?->machine_id) == $machine->id ? 'selected' : '' }}>
                                        {{ $machine->code }} - {{ $machine->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Product</label>
                            <select id="product_id" name="product_id" required readonly style="width:100%;background:#f9fafb;pointer-events:none;">
                                <option value="">Loading product...</option>
                            </select>

                            <div id="product_help" style="font-size:12px;color:#6b7280;margin-top:5px;">
                                Product is loaded from selected production plan.
                            </div>
                        </div>

                        <div>
                            <label>Hour Start</label>
                            <input type="time"
                                   name="hour_start"
                                   value="{{ old('hour_start', $selectedPlan?->hour_start ? substr($selectedPlan->hour_start, 0, 5) : '') }}"
                                   required
                                   readonly
                                   style="width:100%;background:#f9fafb;">
                        </div>

                        <div>
                            <label>Hour End</label>
                            <input type="time"
                                   name="hour_end"
                                   value="{{ old('hour_end', $selectedPlan?->hour_end ? substr($selectedPlan->hour_end, 0, 5) : '') }}"
                                   required
                                   readonly
                                   style="width:100%;background:#f9fafb;">
                        </div>

                        <div>
                            <label>Planned Qty</label>
                            <input id="planned_qty"
                                   type="number"
                                   step="0.01"
                                   name="planned_qty"
                                   value="{{ old('planned_qty', $selectedPlan?->planned_qty) }}"
                                   required
                                   readonly
                                   style="width:100%;background:#f9fafb;">
                        </div>

                        <div>
                            <label>Actual Qty</label>
                            <input id="actual_qty"
                                   type="number"
                                   step="0.01"
                                   name="actual_qty"
                                   value="{{ old('actual_qty', 0) }}"
                                   required
                                   style="width:100%;">
                        </div>

                        <div>
                            <label>Rejected Qty</label>
                            <input id="rejected_qty"
                                   type="number"
                                   step="0.01"
                                   name="rejected_qty"
                                   value="{{ old('rejected_qty', 0) }}"
                                   required
                                   style="width:100%;">
                        </div>

                        <div>
                            <label>Downtime Category Optional</label>
                            <select name="downtime_category_id" style="width:100%;">
                                <option value="">None</option>
                                @foreach($downtimeCategories as $category)
                                    <option value="{{ $category->id }}" {{ old('downtime_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Downtime Reason Optional</label>
                            <select name="downtime_reason_id" style="width:100%;">
                                <option value="">None</option>
                                @foreach($downtimeReasons as $reason)
                                    <option value="{{ $reason->id }}" {{ old('downtime_reason_id') == $reason->id ? 'selected' : '' }}>
                                        {{ $reason->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Initial Status</label>
                            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:9px;color:#6b7280;">
                                Entry: Draft | Machine: Active
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:15px;">
                        <label>Comment</label>
                        <textarea name="comment" rows="3" style="width:100%;">{{ old('comment') }}</textarea>
                    </div>

                    <div style="margin-top:20px;background:#f9fafb;padding:15px;border-radius:10px;border:1px solid #e5e7eb;">
                        <strong>Live KPI Preview</strong>

                        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-top:10px;">
                            <div>
                                <div style="font-size:12px;color:#6b7280;">Good Qty</div>
                                <div id="preview_good_qty" style="font-weight:bold;">0</div>
                            </div>

                            <div>
                                <div style="font-size:12px;color:#6b7280;">Availability</div>
                                <div id="preview_availability" style="font-weight:bold;">100%</div>
                            </div>

                            <div>
                                <div style="font-size:12px;color:#6b7280;">Performance</div>
                                <div id="preview_performance" style="font-weight:bold;">0%</div>
                            </div>

                            <div>
                                <div style="font-size:12px;color:#6b7280;">Quality</div>
                                <div id="preview_quality" style="font-weight:bold;">0%</div>
                            </div>

                            <div>
                                <div style="font-size:12px;color:#6b7280;">OEE</div>
                                <div id="preview_oee" style="font-weight:bold;">0%</div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:20px;display:flex;gap:10px;">
                        @if($selectedPlan)
                            <button type="submit" class="fortune-btn-primary">
                                Save Draft Entry
                            </button>
                        @endif

                        <a href="{{ route('production-plans.index') }}" class="fortune-btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        const initialMachineId = "{{ old('machine_id', $selectedPlan?->machine_id) }}";
        const initialProductId = "{{ old('product_id', $selectedPlan?->product_id) }}";

        function numberValue(id) {
            const value = parseFloat(document.getElementById(id).value);
            return isNaN(value) ? 0 : value;
        }

        function round2(value) {
            return Math.round(value * 100) / 100;
        }

        function calculatePreview() {
            const plannedQty = numberValue('planned_qty');
            const actualQty = numberValue('actual_qty');
            const rejectedQty = numberValue('rejected_qty');

            const goodQty = Math.max(0, actualQty - rejectedQty);
            const availability = 100;
            const performance = plannedQty > 0 ? (actualQty / plannedQty) * 100 : 0;
            const quality = actualQty > 0 ? (goodQty / actualQty) * 100 : 0;
            const oee = (availability * performance * quality) / 10000;

            document.getElementById('preview_good_qty').innerText = round2(goodQty);
            document.getElementById('preview_availability').innerText = round2(availability) + '%';
            document.getElementById('preview_performance').innerText = round2(performance) + '%';
            document.getElementById('preview_quality').innerText = round2(quality) + '%';
            document.getElementById('preview_oee').innerText = round2(oee) + '%';
        }

        function loadProductsByMachine(machineId, selectedProductId = null) {
            const productSelect = document.getElementById('product_id');
            const help = document.getElementById('product_help');

            productSelect.innerHTML = '<option value="">Loading products...</option>';
            productSelect.disabled = true;

            if (!machineId) {
                productSelect.innerHTML = '<option value="">No machine selected</option>';
                help.innerText = 'Product is loaded from selected production plan.';
                productSelect.disabled = false;
                return;
            }

            fetch(`/machines/${machineId}/products`)
                .then(response => response.json())
                .then(products => {
                    productSelect.innerHTML = '';

                    products.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = product.code + ' - ' + product.name;

                        if (selectedProductId && String(product.id) === String(selectedProductId)) {
                            option.selected = true;
                        }

                        productSelect.appendChild(option);
                    });

                    productSelect.disabled = false;
                    help.innerText = 'Product loaded from selected production plan.';
                    calculatePreview();
                })
                .catch(() => {
                    productSelect.innerHTML = '<option value="">Error loading product</option>';
                    help.innerText = 'Unable to load product.';
                    productSelect.disabled = false;
                });
        }

        document.getElementById('actual_qty').addEventListener('input', calculatePreview);
        document.getElementById('rejected_qty').addEventListener('input', calculatePreview);

        if (initialMachineId) {
            loadProductsByMachine(initialMachineId, initialProductId);
        }

        calculatePreview();
    </script>
</x-app-layout>