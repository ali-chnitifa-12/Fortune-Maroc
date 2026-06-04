<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Edit Production Entry</h2>
                <div class="erp-page-subtitle">
                    Complete production quantities and manage machine downtime.
                </div>
            </div>

            <a href="{{ route('production-entries.index') }}" class="erp-btn erp-btn-secondary">
                Back to Entries
            </a>
        </div>
    </x-slot>

    @php
        $activeTab = request('tab', 'production');
        $isDraft = $entry->entry_status === 'draft';
        $isFinished = $entry->entry_status === 'finished';
        $isSent = $entry->entry_status === 'sent_to_thingsboard';

        $actualQty = old('actual_qty', $entry->actual_qty ?? 0);
        $rejectedQty = old('rejected_qty', $entry->rejected_qty ?? 0);
        $chuteQty = old('chute_qty', $entry->chute_qty ?? 0);

        $openDowntime = $entry->downtimes->firstWhere('ended_at', null);
    @endphp

    <div class="erp-page-wrap">
        @if(session('success'))
            <div class="fortune-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="fortune-error">
                <ul style="list-style:disc;margin-left:20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="erp-tabs">
            <a href="{{ route('production-entries.edit', ['production_entry' => $entry->id, 'tab' => 'production']) }}"
               class="erp-tab {{ $activeTab === 'production' ? 'active' : '' }}">
                Production Entry
            </a>

            <a href="{{ route('production-entries.edit', ['production_entry' => $entry->id, 'tab' => 'downtime']) }}"
               class="erp-tab {{ $activeTab === 'downtime' ? 'active' : '' }}">
                Machine Downtime
            </a>
        </div>

        @if($activeTab === 'production')
            <div class="erp-card">
                <form id="productionForm"
                      method="POST"
                      action="{{ route('production-entries.update', $entry) }}">
                    @csrf
                    @method('PUT')

                    <div class="entry-grid">
                        <div>
                        <label>Entry Code</label>
                        <input type="text" value="{{ $entry->entry_code ?? '-' }}" readonly>
                        </div>

                        <div>
                            <label>Plan Code</label>
                            <input type="text" value="{{ $entry->productionPlan?->plan_code ?? '-' }}" readonly>
                        </div>
                        <div>
                            <label>Production Date</label>
                            <input type="text" value="{{ $entry->production_date?->format('d/m/Y') }}" readonly>
                        </div>

                        <div>
                            <label>Shift</label>
                            <input type="text" value="{{ $entry->shift?->code }} - {{ $entry->shift?->name }}" readonly>
                        </div>

                        <div>
                            <label>Zone</label>
                            <input type="text" value="{{ $entry->zone?->code }} - {{ $entry->zone?->name }}" readonly>
                        </div>

                        <div>
                            <label>Production Line</label>
                            <input type="text" value="{{ $entry->productionLine?->code }} - {{ $entry->productionLine?->name }}" readonly>
                        </div>

                        <div>
                            <label>Product</label>
                            <input type="text" value="{{ $entry->product?->code }} - {{ $entry->product?->name }}" readonly>
                        </div>

                        <div>
                            <label>Hour</label>
                            <input type="text"
                                   value="{{ $entry->hour_start ? substr($entry->hour_start, 0, 5) : '-' }} - {{ $entry->hour_end ? substr($entry->hour_end, 0, 5) : '-' }}"
                                   readonly>
                        </div>

                        <div>
                            <label>Planned Qty</label>
                            <input type="text" value="{{ number_format((float) $entry->planned_qty, 2, ',', ' ') }}" readonly>
                        </div>

                        <div>
                            <label>Actual Qty <span class="required">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   id="actual_qty"
                                   name="actual_qty"
                                   value="{{ $actualQty }}"
                                   {{ !$isDraft ? 'readonly' : '' }}>
                            <div class="erp-help-text">
                                Required before update or finish.
                            </div>
                        </div>

                        <div>
                            <label>Rejected Qty <span class="required">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   id="rejected_qty"
                                   name="rejected_qty"
                                   value="{{ $rejectedQty }}"
                                   {{ !$isDraft ? 'readonly' : '' }}>
                            <div class="erp-help-text">
                                Product quantity. Used in Good Qty and OEE calculation.
                            </div>
                        </div>

                        <div>
                            <label>Chute</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   id="chute_qty"
                                   name="chute_qty"
                                   value="{{ $chuteQty }}"
                                   {{ !$isDraft ? 'readonly' : '' }}>
                            <div class="erp-help-text">
                                Separate information field. Not used in OEE calculation.
                            </div>
                        </div>

                        <div>
                            <label>Entry Status</label>
                            <input type="text" value="{{ ucwords(str_replace('_', ' ', $entry->entry_status)) }}" readonly>
                        </div>

                        <div>
                            <label>Machine Status</label>
                            <input type="text" value="{{ ucwords(str_replace('_', ' ', $entry->machine_status)) }}" readonly>
                        </div>

                        <div>
                            <label>Good Qty</label>
                            <input type="text" value="{{ number_format((float) $entry->good_qty, 2, ',', ' ') }}" readonly>
                        </div>

                        <div>
                            <label>Stops Count</label>
                            <input type="text" value="{{ (int) $entry->stops_count }}" readonly>
                        </div>

                        <div>
                            <label>Stopped Time</label>
                            <input type="text" value="{{ (int) $entry->stop_duration_min }} min" readonly>
                        </div>

                        <div>
                            <label>OEE Preview</label>
                            <input type="text" value="{{ number_format((float) $entry->oee, 2, '.', '') }}%" readonly>
                        </div>

                        <div class="entry-full">
                            <label>Comment</label>
                            <textarea name="comment" rows="4" {{ !$isDraft ? 'readonly' : '' }}>{{ old('comment', $entry->comment ?? '') }}</textarea>
                        </div>
                    </div>

                    @if($isDraft)
                        <div class="entry-warning">
                            Actual Qty is required to calculate OEE correctly. Chute is a separate information field and does not affect OEE.
                        </div>
                    @endif

                    <div class="erp-form-actions">
                        @if($isDraft)
                            <button type="submit"
                                    class="erp-btn erp-btn-primary"
                                    onclick="return validateUpdateEntry();">
                                Update Entry
                            </button>

                            <button type="button"
                                    class="erp-btn erp-btn-success"
                                    onclick="confirmFinishEntry();">
                                Finish Entry
                            </button>
                        @endif

                        @if($isFinished && auth()->user()?->canApproveProductionEntries())
                            <button type="button"
                                    class="erp-btn erp-btn-success"
                                    onclick="confirmApproveEntry();">
                                Approve & Send to ThingsBoard
                            </button>
                        @endif

                        <a href="{{ route('production-entries.index') }}" class="erp-btn erp-btn-secondary">
                            Back
                        </a>
                    </div>
                </form>

                @if($isDraft)
                    <form id="finishForm"
                          method="POST"
                          action="{{ route('production-entries.finish', $entry) }}"
                          style="display:none;">
                        @csrf
                    </form>
                @endif

                @if($isFinished && auth()->user()?->canApproveProductionEntries())
                    <form id="approveForm"
                          method="POST"
                          action="{{ route('production-entries.approve', $entry) }}"
                          style="display:none;">
                        @csrf
                    </form>
                @endif
            </div>
        @endif

        @if($activeTab === 'downtime')
            <div class="erp-card">
                <div class="downtime-head">
                    <div>
                        <h3 class="section-title">Machine Stop / Fixed</h3>
                        <div class="section-subtitle">
                            Select the stopped machine from this production line only.
                        </div>
                    </div>

                    <div>
                        @if($openDowntime)
                            <span class="erp-pill erp-pill-danger">Machine In Repair</span>
                        @else
                            <span class="erp-pill erp-pill-success">Machine Active</span>
                        @endif
                    </div>
                </div>

                @if($openDowntime)
                    <div class="entry-warning">
                        Current machine stop is open. Click Fixed Machine after repair.
                    </div>
                @else
                    <div class="entry-info">
                        No open machine stop. You can declare a new stop if a machine is stopped.
                    </div>
                @endif

                @if($isDraft)
                    @if(!$openDowntime)
                        <form id="stopMachineForm"
                              method="POST"
                              action="{{ route('production-entries.stop', $entry) }}">
                            @csrf

                            <div class="entry-grid">
                                <div>
                                    <label>Stopped Machine <span class="required">*</span></label>
                                    <select name="machine_id" id="machine_id">
                                        <option value="">Select machine</option>
                                        @foreach($lineMachines as $machine)
                                            <option value="{{ $machine->id }}">
                                                {{ $machine->code }} - {{ $machine->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="erp-help-text">
                                        Machine list depends on selected production line.
                                    </div>
                                </div>

                                <div>
                                    <label>Actual Qty</label>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           name="actual_qty"
                                           value="{{ $actualQty }}">
                                    <div class="erp-help-text">
                                        Optional during stop. You can fill it later.
                                    </div>
                                </div>

                                <div>
                                    <label>Rejected Qty</label>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           name="rejected_qty"
                                           value="{{ $rejectedQty }}">
                                    <div class="erp-help-text">
                                        Optional during stop. Must not exceed Actual Qty if filled.
                                    </div>
                                </div>

                                <div>
                                    <label>Chute</label>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           name="chute_qty"
                                           value="{{ $chuteQty }}">
                                    <div class="erp-help-text">
                                        Optional during stop. Separate information field only.
                                    </div>
                                </div>
                            </div>

                            <div class="erp-form-actions">
                                <button type="submit"
                                        class="erp-btn erp-btn-danger"
                                        onclick="return validateStopMachine();">
                                    Stop Machine
                                </button>
                            </div>
                        </form>
                    @else
                        <form id="fixedMachineForm"
                              method="POST"
                              action="{{ route('production-entries.fixed', $entry) }}">
                            @csrf

                            <div class="erp-form-actions">
                                <button type="submit" class="erp-btn erp-btn-primary">
                                    Fixed Machine
                                </button>
                            </div>
                        </form>
                    @endif
                @endif
            </div>

            <div class="erp-card">
                <div class="downtime-head">
                    <div>
                        <h3 class="section-title">Downtime Lines</h3>
                        <div class="section-subtitle">
                            Category and reason are mandatory before finishing the entry.
                        </div>
                    </div>
                </div>

                <div class="erp-responsive-table">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>Machine</th>
                                <th>Started</th>
                                <th>Ended</th>
                                <th>Duration</th>
                                <th>Category</th>
                                <th>Reason</th>
                                <th>Comment</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($entry->downtimes as $downtime)
                                <tr>
                                    <td>
                                        {{ $downtime->machine?->code }}
                                    </td>

                                    <td>
                                        {{ $downtime->started_at ? $downtime->started_at->format('H:i:s') : '-' }}
                                    </td>

                                    <td>
                                        {{ $downtime->ended_at ? $downtime->ended_at->format('H:i:s') : 'Open' }}
                                    </td>

                                    <td>
                                        {{ (int) $downtime->duration_min }} min
                                    </td>

                                    <td>
                                        @if($downtime->ended_at && $isDraft)
                                            <form id="downtimeForm{{ $downtime->id }}"
                                                  method="POST"
                                                  action="{{ route('production-downtimes.update', $downtime) }}">
                                                @csrf
                                                @method('PUT')

                                                <select name="downtime_category_id">
                                                    <option value="">Select</option>
                                                    @foreach($downtimeCategories as $category)
                                                        <option value="{{ $category->id }}"
                                                            {{ (int) $downtime->downtime_category_id === (int) $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @else
                                            {{ $downtime->downtimeCategory?->name ?? '-' }}
                                        @endif
                                    </td>

                                    <td>
                                        @if($downtime->ended_at && $isDraft)
                                            <select name="downtime_reason_id"
                                                    form="downtimeForm{{ $downtime->id }}">
                                                <option value="">Select</option>
                                                @foreach($downtimeReasons as $reason)
                                                    <option value="{{ $reason->id }}"
                                                        {{ (int) $downtime->downtime_reason_id === (int) $reason->id ? 'selected' : '' }}>
                                                        {{ $reason->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            {{ $downtime->downtimeReason?->name ?? '-' }}
                                        @endif
                                    </td>

                                    <td>
                                        @if($downtime->ended_at && $isDraft)
                                            <input type="text"
                                                   name="comment"
                                                   form="downtimeForm{{ $downtime->id }}"
                                                   value="{{ $downtime->comment }}">
                                        @else
                                            {{ $downtime->comment ?? '-' }}
                                        @endif
                                    </td>

                                    <td>
                                        @if($downtime->ended_at && $isDraft)
                                            <button type="submit"
                                                    form="downtimeForm{{ $downtime->id }}"
                                                    class="erp-btn erp-btn-small erp-btn-primary">
                                                Save
                                            </button>
                                        @elseif(!$downtime->ended_at)
                                            <span class="erp-pill erp-pill-danger">In Repair</span>
                                        @else
                                            <span class="erp-pill erp-pill-success">Saved</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="erp-empty">
                                        No downtime lines found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($isDraft)
                    <div class="erp-form-actions">
                        <button type="button"
                                class="erp-btn erp-btn-success"
                                onclick="confirmFinishEntry();">
                            Finish Entry
                        </button>
                    </div>

                    <form id="finishForm"
                          method="POST"
                          action="{{ route('production-entries.finish', $entry) }}"
                          style="display:none;">
                        @csrf
                    </form>
                @endif
            </div>
        @endif
    </div>

    <div id="popupOverlay" class="popup-overlay">
        <div class="popup-box">
            <div class="popup-icon">!</div>
            <div class="popup-title" id="popupTitle">Warning</div>
            <div class="popup-message" id="popupMessage">Message</div>
            <button type="button" class="erp-btn erp-btn-primary" onclick="closePopup()">
                OK
            </button>
        </div>
    </div>

    @include('components.erp-page-style')

    <style>
        .erp-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #ffffff;
        }

        .erp-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 18px;
            border-radius: 10px;
            color: #334155;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
        }

        .erp-tab.active {
            background: #2563eb;
            color: #ffffff;
        }

        .entry-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .entry-grid label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 900;
            color: #334155;
        }

        .entry-grid input,
        .entry-grid select,
        .entry-grid textarea,
        .erp-table select,
        .erp-table input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
        }

        .entry-grid input,
        .entry-grid select {
            height: 38px;
        }

        .entry-grid input[readonly],
        .entry-grid textarea[readonly] {
            background: #f8fafc;
            color: #475569;
        }

        .entry-full {
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

        .entry-warning {
            margin-top: 14px;
            padding: 12px 14px;
            border: 1px solid #facc15;
            border-radius: 10px;
            background: #fef9c3;
            color: #92400e;
            font-size: 13px;
            font-weight: 900;
        }

        .entry-info {
            margin-top: 14px;
            padding: 12px 14px;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 900;
        }

        .erp-form-actions {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .erp-btn-success {
            background: #16a34a;
            color: #ffffff;
        }

        .erp-btn-danger {
            background: #dc2626;
            color: #ffffff;
        }

        .erp-btn-small {
            min-height: 30px;
            padding: 5px 10px;
            font-size: 12px;
        }

        .downtime-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }

        .section-title {
            margin: 0;
            font-size: 17px;
            font-weight: 900;
            color: #0f172a;
        }

        .section-subtitle {
            margin-top: 5px;
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
        }

        .erp-pill-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .popup-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.45);
            padding: 20px;
        }

        .popup-overlay.active {
            display: flex;
        }

        .popup-box {
            width: 100%;
            max-width: 470px;
            border-radius: 18px;
            background: #ffffff;
            padding: 28px 26px;
            text-align: center;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.25);
        }

        .popup-icon {
            width: 44px;
            height: 44px;
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #fef3c7;
            color: #b45309;
            font-size: 24px;
            font-weight: 900;
        }

        .popup-title {
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
        }

        .popup-message {
            margin: 12px 0 22px;
            font-size: 14px;
            font-weight: 800;
            color: #475569;
            line-height: 1.5;
        }

        @media (max-width: 1100px) {
            .entry-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .entry-grid {
                grid-template-columns: 1fr;
            }

            .erp-tabs {
                flex-direction: column;
            }

            .erp-tab {
                width: 100%;
            }

            .downtime-head {
                flex-direction: column;
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
        function getActualQty() {
            const input = document.getElementById('actual_qty');

            if (!input) {
                return 0;
            }

            const value = parseFloat(input.value || '0');

            return Number.isNaN(value) ? 0 : value;
        }

        function getRejectedQty() {
            const input = document.getElementById('rejected_qty');

            if (!input) {
                return 0;
            }

            const value = parseFloat(input.value || '0');

            return Number.isNaN(value) ? 0 : value;
        }

        function getChuteQty() {
            const input = document.getElementById('chute_qty');

            if (!input) {
                return 0;
            }

            const value = parseFloat(input.value || '0');

            return Number.isNaN(value) ? 0 : value;
        }

        function showPopup(title, message) {
            document.getElementById('popupTitle').innerText = title;
            document.getElementById('popupMessage').innerText = message;
            document.getElementById('popupOverlay').classList.add('active');
        }

        function closePopup() {
            document.getElementById('popupOverlay').classList.remove('active');
        }

        function validateUpdateEntry() {
            const actualQty = getActualQty();
            const rejectedQty = getRejectedQty();
            const chuteQty = getChuteQty();

            if (actualQty <= 0) {
                showPopup(
                    'Actual Quantity Required',
                    'Please enter Actual Qty before updating the production entry.'
                );

                return false;
            }

            if (rejectedQty < 0) {
                showPopup(
                    'Rejected Quantity Invalid',
                    'Rejected Qty cannot be negative.'
                );

                return false;
            }

            if (chuteQty < 0) {
                showPopup(
                    'Chute Invalid',
                    'Chute cannot be negative.'
                );

                return false;
            }

            if (rejectedQty > actualQty) {
                showPopup(
                    'Rejected Quantity Invalid',
                    'Rejected Qty cannot be greater than Actual Qty.'
                );

                return false;
            }

            return true;
        }

        function validateStopMachine() {
            const machineId = document.getElementById('machine_id')?.value || '';

            if (!machineId) {
                showPopup(
                    'Machine Required',
                    'Please select the stopped machine.'
                );

                return false;
            }

            return true;
        }

        function confirmFinishEntry() {
            const actualQty = getActualQty();
            const rejectedQty = getRejectedQty();
            const chuteQty = getChuteQty();

            if (actualQty <= 0) {
                showPopup(
                    'Actual Quantity Required',
                    'Please click Update Entry after entering Actual Qty before finishing.'
                );

                return false;
            }

            if (rejectedQty < 0) {
                showPopup(
                    'Rejected Quantity Invalid',
                    'Rejected Qty cannot be negative.'
                );

                return false;
            }

            if (chuteQty < 0) {
                showPopup(
                    'Chute Invalid',
                    'Chute cannot be negative.'
                );

                return false;
            }

            if (rejectedQty > actualQty) {
                showPopup(
                    'Rejected Quantity Invalid',
                    'Rejected Qty cannot be greater than Actual Qty.'
                );

                return false;
            }

            if (confirm('Finish this production entry?')) {
                document.getElementById('finishForm').submit();
            }

            return false;
        }

        function confirmApproveEntry() {
            if (confirm('Approve and send this entry to ThingsBoard?')) {
                document.getElementById('approveForm').submit();
            }

            return false;
        }
    </script>
</x-app-layout>