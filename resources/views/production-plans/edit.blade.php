<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Edit Production Entry</h2>
                <div class="erp-page-subtitle">
                    Complete production entry information, downtime lines, then finish.
                </div>
            </div>

            <a href="{{ route('production-entries.index') }}" class="erp-btn erp-btn-secondary">
                Back to Entries
            </a>
        </div>
    </x-slot>

    <div class="erp-page-wrap">
        @if(session('success'))
            <div class="fortune-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="fortune-error">
                <ul style="list-style:disc;margin-left:20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($entry->downtimes->count() > 0)
            <div class="erp-warning-box">
                This entry has downtime lines. Each completed downtime must have a category and reason before finishing.
            </div>
        @endif

        <div class="erp-card">
            <form method="POST" action="{{ route('production-entries.update', $entry) }}">
                @csrf
                @method('PUT')

                <input type="hidden" name="production_plan_id" value="{{ old('production_plan_id', $entry->production_plan_id) }}">

                <div class="erp-form-grid">
                    <div>
                        <label>Production Date</label>
                        <input type="date"
                               name="production_date"
                               value="{{ old('production_date', $entry->production_date?->format('Y-m-d')) }}"
                               required
                               readonly>
                    </div>

                    <div>
                        <label>Shift</label>
                        <select name="shift_id" required class="readonly-select">
                            <option value="">Select shift</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}"
                                    {{ old('shift_id', $entry->shift_id) == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->code }} - {{ $shift->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Machine</label>
                        <select id="machine_id" name="machine_id" required class="readonly-select">
                            <option value="">Select machine</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}"
                                    {{ old('machine_id', $entry->machine_id) == $machine->id ? 'selected' : '' }}>
                                    {{ $machine->code }} - {{ $machine->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Product</label>
                        <select id="product_id" name="product_id" required class="readonly-select">
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                    {{ old('product_id', $entry->product_id) == $product->id ? 'selected' : '' }}>
                                    {{ $product->code }} - {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Hour Start</label>
                        <input type="time"
                               name="hour_start"
                               value="{{ old('hour_start', substr($entry->hour_start, 0, 5)) }}"
                               required
                               readonly>
                    </div>

                    <div>
                        <label>Hour End</label>
                        <input type="time"
                               name="hour_end"
                               value="{{ old('hour_end', substr($entry->hour_end, 0, 5)) }}"
                               required
                               readonly>
                    </div>

                    <div>
                        <label>Planned Qty</label>
                        <input id="planned_qty"
                               type="number"
                               step="0.01"
                               name="planned_qty"
                               value="{{ old('planned_qty', $entry->planned_qty) }}"
                               required
                               readonly>
                    </div>

                    <div>
                        <label>Actual Qty</label>
                        <input id="actual_qty"
                               type="number"
                               step="0.01"
                               name="actual_qty"
                               value="{{ old('actual_qty', $entry->actual_qty) }}"
                               required
                               {{ in_array($entry->entry_status, ['approved', 'sent_to_thingsboard'], true) ? 'readonly' : '' }}>
                    </div>

                    <div>
                        <label>Rejected Qty</label>
                        <input id="rejected_qty"
                               type="number"
                               step="0.01"
                               name="rejected_qty"
                               value="{{ old('rejected_qty', $entry->rejected_qty) }}"
                               required
                               {{ in_array($entry->entry_status, ['approved', 'sent_to_thingsboard'], true) ? 'readonly' : '' }}>
                    </div>

                    <div>
                        <label>System Status</label>
                        <div class="erp-info-message">
                            Entry: {{ $entry->entry_status }} | Machine: {{ $entry->machine_status }}
                        </div>
                    </div>
                </div>

                <div class="erp-form-section">
                    <label>Comment</label>
                    <textarea name="comment"
                              rows="3"
                              {{ in_array($entry->entry_status, ['approved', 'sent_to_thingsboard'], true) ? 'readonly' : '' }}>{{ old('comment', $entry->comment) }}</textarea>
                </div>

                <div class="erp-summary-grid">
                    <div class="erp-mini-card">
                        <div class="erp-mini-label">Stops Count</div>
                        <div class="erp-mini-value">{{ $entry->stops_count }}</div>
                    </div>

                    <div class="erp-mini-card">
                        <div class="erp-mini-label">Total Stopped Time</div>
                        <div class="erp-mini-value">{{ $entry->stop_duration_min }} min</div>
                    </div>

                    <div class="erp-mini-card">
                        <div class="erp-mini-label">Current Stop Start</div>
                        <div class="erp-mini-value">{{ $entry->current_stop_started_at?->format('H:i:s') ?? '-' }}</div>
                    </div>

                    <div class="erp-mini-card">
                        <div class="erp-mini-label">Last Fixed Time</div>
                        <div class="erp-mini-value">{{ $entry->stop_ended_at?->format('H:i:s') ?? '-' }}</div>
                    </div>
                </div>

                <div class="erp-summary-grid">
                    <div class="erp-mini-card">
                        <div class="erp-mini-label">Good Qty</div>
                        <div id="preview_good_qty" class="erp-mini-value">{{ $entry->good_qty }}</div>
                    </div>

                    <div class="erp-mini-card">
                        <div class="erp-mini-label">Availability</div>
                        <div id="preview_availability" class="erp-mini-value">{{ $entry->availability }}%</div>
                    </div>

                    <div class="erp-mini-card">
                        <div class="erp-mini-label">Performance</div>
                        <div id="preview_performance" class="erp-mini-value">{{ $entry->performance }}%</div>
                    </div>

                    <div class="erp-mini-card">
                        <div class="erp-mini-label">Quality</div>
                        <div id="preview_quality" class="erp-mini-value">{{ $entry->quality }}%</div>
                    </div>

                    <div class="erp-mini-card">
                        <div class="erp-mini-label">OEE</div>
                        <div id="preview_oee" class="erp-mini-value">{{ $entry->oee }}%</div>
                    </div>
                </div>

                <div class="erp-form-actions">
                    @if(!in_array($entry->entry_status, ['approved', 'sent_to_thingsboard'], true))
                        <button type="submit" class="erp-btn erp-btn-primary">
                            Update Entry
                        </button>
                    @endif

                    <a href="{{ route('production-entries.index') }}" class="erp-btn erp-btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <div class="erp-card">
            <div class="erp-section-head">
                <div>
                    <h3 class="erp-section-title">Downtime Lines</h3>
                    <div class="erp-section-subtitle">
                        Each stop/fixed action creates one downtime line.
                    </div>
                </div>
            </div>

            <div class="erp-responsive-table">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Started</th>
                            <th>Ended</th>
                            <th>Duration</th>
                            <th>Category</th>
                            <th>Reason</th>
                            <th>Comment</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($entry->downtimes as $downtime)
                            <tr>
                                <form method="POST" action="{{ route('production-downtimes.update', $downtime) }}">
                                    @csrf
                                    @method('PUT')

                                    <td>{{ $downtime->started_at?->format('H:i:s') }}</td>
                                    <td>{{ $downtime->ended_at?->format('H:i:s') ?? 'Open' }}</td>
                                    <td>{{ $downtime->duration_min }} min</td>

                                    <td>
                                        <select name="downtime_category_id"
                                                required
                                                {{ !$downtime->ended_at || in_array($entry->entry_status, ['approved', 'sent_to_thingsboard'], true) ? 'disabled' : '' }}>
                                            <option value="">Select</option>
                                            @foreach($downtimeCategories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ $downtime->downtime_category_id == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <select name="downtime_reason_id"
                                                required
                                                {{ !$downtime->ended_at || in_array($entry->entry_status, ['approved', 'sent_to_thingsboard'], true) ? 'disabled' : '' }}>
                                            <option value="">Select</option>
                                            @foreach($downtimeReasons as $reason)
                                                <option value="{{ $reason->id }}"
                                                    {{ $downtime->downtime_reason_id == $reason->id ? 'selected' : '' }}>
                                                    {{ $reason->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text"
                                               name="comment"
                                               value="{{ $downtime->comment }}"
                                               {{ !$downtime->ended_at || in_array($entry->entry_status, ['approved', 'sent_to_thingsboard'], true) ? 'disabled' : '' }}>
                                    </td>

                                    <td class="text-right">
                                        @if($downtime->ended_at && !in_array($entry->entry_status, ['approved', 'sent_to_thingsboard'], true))
                                            <button type="submit" class="erp-btn erp-btn-small erp-btn-primary">
                                                Save
                                            </button>
                                        @elseif(!$downtime->ended_at)
                                            <span class="erp-pill erp-pill-danger">In Repair</span>
                                        @else
                                            <span class="erp-muted-small">Locked</span>
                                        @endif
                                    </td>
                                </form>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">
                                    No downtime lines for this entry.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .erp-page-wrap {
            padding: 16px 22px;
        }

        .erp-page-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .erp-page-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .erp-page-subtitle {
            margin-top: 4px;
            font-size: 13px;
            color: #64748b;
        }

        .erp-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
            margin-bottom: 16px;
        }

        .erp-warning-box {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 14px;
            font-size: 13px;
            font-weight: 700;
        }

        .erp-form-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .erp-form-grid label,
        .erp-form-section label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 800;
            color: #334155;
        }

        .erp-form-grid input,
        .erp-form-grid select,
        .erp-form-section textarea,
        .erp-table input,
        .erp-table select {
            width: 100%;
            min-height: 36px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 9px;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
        }

        .erp-form-grid input[readonly],
        .erp-form-section textarea[readonly],
        .readonly-select {
            background: #f8fafc !important;
            color: #64748b !important;
            pointer-events: none;
        }

        .erp-info-message {
            min-height: 36px;
            display: flex;
            align-items: center;
            padding: 6px 9px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f8fafc;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
        }

        .erp-form-section {
            margin-top: 14px;
        }

        .erp-form-section textarea {
            min-height: 90px;
            resize: vertical;
        }

        .erp-summary-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin-top: 14px;
        }

        .erp-mini-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
        }

        .erp-mini-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 800;
        }

        .erp-mini-value {
            margin-top: 4px;
            font-size: 15px;
            color: #0f172a;
            font-weight: 900;
        }

        .erp-form-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }

        .erp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }

        .erp-btn-primary {
            background: #2563eb;
            color: #ffffff;
        }

        .erp-btn-primary:hover {
            background: #1d4ed8;
        }

        .erp-btn-secondary {
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e5e7eb;
        }

        .erp-btn-small {
            min-height: 28px;
            padding: 5px 9px;
            font-size: 11px;
        }

        .erp-section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .erp-section-title {
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
        }

        .erp-section-subtitle {
            margin-top: 3px;
            font-size: 12px;
            color: #64748b;
        }

        .erp-responsive-table {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .erp-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .erp-table th {
            background: #f8fafc;
            color: #334155;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 9px 8px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            white-space: nowrap;
        }

        .erp-table td {
            padding: 9px 8px;
            border-bottom: 1px solid #eef2f7;
            color: #0f172a;
            vertical-align: middle;
            white-space: nowrap;
        }

        .text-right {
            text-align: right !important;
        }

        .erp-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 900;
            line-height: 1;
        }

        .erp-pill-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .erp-muted-small {
            font-size: 11px;
            color: #64748b;
        }

        .erp-empty {
            text-align: center;
            color: #64748b !important;
            padding: 18px !important;
            font-weight: 700;
        }

        @media (max-width: 1280px) {
            .erp-form-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .erp-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .erp-page-wrap {
                padding: 12px 10px;
            }

            .erp-page-head {
                align-items: stretch;
                flex-direction: column;
            }

            .erp-form-grid,
            .erp-summary-grid {
                grid-template-columns: 1fr;
            }

            .erp-form-actions {
                flex-direction: column;
            }

            .erp-btn {
                width: 100%;
            }

            .erp-card {
                padding: 12px;
            }
        }
    </style>

    <script>
        const totalStoppedMinutes = {{ (int) $entry->stop_duration_min }};

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
            const stopDuration = totalStoppedMinutes;

            const goodQty = Math.max(0, actualQty - rejectedQty);
            const availability = Math.max(0, Math.min(100, ((60 - stopDuration) / 60) * 100));
            const performance = plannedQty > 0 ? (actualQty / plannedQty) * 100 : 0;
            const quality = actualQty > 0 ? (goodQty / actualQty) * 100 : 0;
            const oee = (availability * performance * quality) / 10000;

            document.getElementById('preview_good_qty').innerText = round2(goodQty);
            document.getElementById('preview_availability').innerText = round2(availability) + '%';
            document.getElementById('preview_performance').innerText = round2(performance) + '%';
            document.getElementById('preview_quality').innerText = round2(quality) + '%';
            document.getElementById('preview_oee').innerText = round2(oee) + '%';
        }

        const actualInput = document.getElementById('actual_qty');
        const rejectedInput = document.getElementById('rejected_qty');

        if (actualInput) {
            actualInput.addEventListener('input', calculatePreview);
        }

        if (rejectedInput) {
            rejectedInput.addEventListener('input', calculatePreview);
        }

        calculatePreview();
    </script>
</x-app-layout>