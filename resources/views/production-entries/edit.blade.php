<x-app-layout>
    @php
        $returnUrl = request('return_url', route('production-entries.index'));
    @endphp

    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">{{ __('Production Entry') }}</h2>
                <div class="erp-page-subtitle">
                    {{ __('Update hourly production data and manage machine downtime at shift level.') }}
                </div>
            </div>

            <a href="{{ $returnUrl }}" class="erp-btn erp-btn-secondary">
                {{ __('Back to Entries') }}
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
        $chute1Qty = old('chute_1_qty', $entry->chute_1_qty ?? 0);
        $chute2Qty = old('chute_2_qty', $entry->chute_2_qty ?? 0);
        $chute3Qty = old('chute_3_qty', $entry->chute_3_qty ?? 0);

        $planDowntimes = $plan ? $plan->downtimes : collect();
        $openDowntime = $planDowntimes->firstWhere('ended_at', null);

        $statusLabel = match ($entry->entry_status) {
            'draft' => __('Draft'),
            'finished' => __('Finished'),
            'sent_to_thingsboard' => __('Sent To ThingsBoard'),
            default => __(ucwords(str_replace('_', ' ', $entry->entry_status ?? '-'))),
        };

        $statusClass = match ($entry->entry_status) {
            'draft' => 'erp-pill-warning',
            'finished' => 'erp-pill-info',
            'sent_to_thingsboard' => 'erp-pill-success',
            default => 'erp-pill-neutral',
        };
    @endphp

    <div class="erp-page-wrap">
        @if(session('success'))
            <div class="fortune-success">
                {{ __(session('success')) }}
            </div>
        @endif

        @if($errors->any())
            <div class="fortune-error">
                <ul style="list-style:disc;margin-left:20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ __($error) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="shift-context-card">
            <div>
                <div class="context-label">{{ __('Plan') }}</div>
                <div class="context-value">{{ $plan?->plan_code ?? '-' }}</div>
            </div>

            <div>
                <div class="context-label">{{ __('Entry') }}</div>
                <div class="context-value">{{ $entry->entry_code ?? '-' }}</div>
            </div>

            <div>
                <div class="context-label">{{ __('Date') }}</div>
                <div class="context-value">{{ $entry->production_date?->format('d/m/Y') ?? '-' }}</div>
            </div>

            <div>
                <div class="context-label">{{ __('Hour') }}</div>
                <div class="context-value">
                    {{ $entry->hour_start ? substr($entry->hour_start, 0, 5) : '-' }}
                    -
                    {{ $entry->hour_end ? substr($entry->hour_end, 0, 5) : '-' }}
                </div>
            </div>

            <div>
                <div class="context-label">{{ __('Shift') }}</div>
                <div class="context-value">{{ $entry->shift?->code ?? '-' }}</div>
            </div>

            <div>
                <div class="context-label">{{ __('Line') }}</div>
                <div class="context-value">{{ $entry->productionLine?->code ?? '-' }}</div>
            </div>

            <div>
                <div class="context-label">{{ __('Product') }}</div>
                <div class="context-value">{{ $entry->product?->code ?? '-' }}</div>
            </div>

            <div>
                <div class="context-label">{{ __('Status') }}</div>
                <div class="context-value">
                    <span class="erp-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
            </div>
        </div>

        <div class="erp-tabs">
            <a href="{{ route('production-entries.edit', ['production_entry' => $entry->id, 'tab' => 'production', 'return_url' => $returnUrl]) }}"
               class="erp-tab {{ $activeTab === 'production' ? 'active' : '' }}">
                {{ __('Production Entry') }}
            </a>

            <a href="{{ route('production-entries.edit', ['production_entry' => $entry->id, 'tab' => 'downtime', 'return_url' => $returnUrl]) }}"
               class="erp-tab {{ $activeTab === 'downtime' ? 'active' : '' }}">
                {{ __('Shift Machine Downtime') }}
            </a>
        </div>

        @if($activeTab === 'production')
            <div class="erp-card">
                <form id="productionForm"
                      method="POST"
                      action="{{ route('production-entries.update', $entry) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="return_url" value="{{ $returnUrl }}">

                    <div class="entry-section-head">
                        <div>
                            <h3 class="section-title">{{ __('Production Data') }}</h3>
                            <div class="section-subtitle">
                                {{ __('Enter actual quantity, rejected quantity and chute information for this hour.') }}
                            </div>
                        </div>

                        <span class="erp-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>

                    <div class="entry-grid">
                        <div>
                            <label>{{ __('Entry Code') }}</label>
                            <input type="text" value="{{ $entry->entry_code ?? '-' }}" readonly>
                        </div>

                        <div>
                            <label>{{ __('Plan Code') }}</label>
                            <input type="text" value="{{ $plan?->plan_code ?? '-' }}" readonly>
                        </div>

                        <div>
                            <label>{{ __('Production Date') }}</label>
                            <input type="text" value="{{ $entry->production_date?->format('d/m/Y') }}" readonly>
                        </div>

                        <div>
                            <label>{{ __('Shift') }}</label>
                            <input type="text" value="{{ $entry->shift?->code }} - {{ $entry->shift?->name }}" readonly>
                        </div>

                        <div>
                            <label>{{ __('Zone') }}</label>
                            <input type="text" value="{{ $entry->zone?->code }} - {{ $entry->zone?->name }}" readonly>
                        </div>

                        <div>
                            <label>{{ __('Production Line') }}</label>
                            <input type="text" value="{{ $entry->productionLine?->code }} - {{ $entry->productionLine?->name }}" readonly>
                        </div>

                        <div>
                            <label>{{ __('Product') }}</label>
                            <input type="text" value="{{ $entry->product?->code }} - {{ $entry->product?->name }}" readonly>
                        </div>

                        <div>
                            <label>{{ __('Hour') }}</label>
                            <input type="text"
                                   value="{{ $entry->hour_start ? substr($entry->hour_start, 0, 5) : '-' }} - {{ $entry->hour_end ? substr($entry->hour_end, 0, 5) : '-' }}"
                                   readonly>
                        </div>

                        <div>
                            <label>{{ __('Planned Qty') }}</label>
                            <input type="text" value="{{ number_format((float) $entry->planned_qty, 2, ',', ' ') }}" readonly>
                        </div>

                        <div>
                            <label>{{ __('Actual Qty') }} <span class="required">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   id="actual_qty"
                                   name="actual_qty"
                                   value="{{ $actualQty }}"
                                   {{ !$isDraft ? 'readonly' : '' }}>
                            <div class="erp-help-text">
                                {{ __('Required before update or finish.') }}
                            </div>
                        </div>

                        <div>
                            <label>{{ __('Rejected Qty') }} <span class="required">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   id="rejected_qty"
                                   name="rejected_qty"
                                   value="{{ $rejectedQty }}"
                                   {{ !$isDraft ? 'readonly' : '' }}>
                            <div class="erp-help-text">
                                {{ __('Product quantity. Used in Good Qty and OEE calculation.') }}
                            </div>
                        </div>

                        <div>
                            <label>{{ __('Good Qty') }}</label>
                            <input type="text" value="{{ number_format((float) $entry->good_qty, 2, ',', ' ') }}" readonly>
                        </div>

                        <div>
                            <label>{{ __('Chute 1') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   id="chute_1_qty"
                                   name="chute_1_qty"
                                   value="{{ $chute1Qty }}"
                                   {{ !$isDraft ? 'readonly' : '' }}>
                            <div class="erp-help-text">{{ __('Separate information field.') }}</div>
                        </div>

                        <div>
                            <label>{{ __('Chute 2') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   id="chute_2_qty"
                                   name="chute_2_qty"
                                   value="{{ $chute2Qty }}"
                                   {{ !$isDraft ? 'readonly' : '' }}>
                            <div class="erp-help-text">{{ __('Separate information field.') }}</div>
                        </div>

                        <div>
                            <label>{{ __('Chute 3') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   id="chute_3_qty"
                                   name="chute_3_qty"
                                   value="{{ $chute3Qty }}"
                                   {{ !$isDraft ? 'readonly' : '' }}>
                            <div class="erp-help-text">{{ __('Separate information field.') }}</div>
                        </div>

                        <div>
                            <label>{{ __('Shift Stops Count') }}</label>
                            <input type="text" value="{{ (int) $entry->stops_count }}" readonly>
                        </div>

                        <div>
                            <label>{{ __('Shift Stopped Time') }}</label>
                            <input type="text" value="{{ (int) $entry->stop_duration_min }} {{ __('min') }}" readonly>
                        </div>

                        <div>
                            <label>{{ __('OEE Preview') }}</label>
                            <input type="text" value="{{ number_format((float) $entry->oee, 2, '.', '') }}%" readonly>
                        </div>

                        <div class="entry-full">
                            <label>{{ __('Comment') }}</label>
                            <textarea name="comment" rows="4" {{ !$isDraft ? 'readonly' : '' }}>{{ old('comment', $entry->comment ?? '') }}</textarea>
                        </div>
                    </div>

                    @if($isDraft)
                        <div class="entry-warning">
                            {{ __('Chute 1, Chute 2 and Chute 3 are separate information fields and do not affect OEE.') }}
                        </div>
                    @endif

                    <div class="erp-form-actions">
                        @if($isDraft)
                            <button type="submit"
                                    class="erp-btn erp-btn-primary"
                                    onclick="return validateUpdateEntry();">
                                {{ __('Update Entry') }}
                            </button>

                            <button type="button"
                                    class="erp-btn erp-btn-success"
                                    onclick="confirmFinishEntry();">
                                {{ __('Finish Entry') }}
                            </button>
                        @endif

                        @if($isFinished && auth()->user()?->canApproveProductionEntries())
                            <button type="button"
                                    class="erp-btn erp-btn-success"
                                    onclick="confirmApproveEntry();">
                                {{ __('Approve & Send to ThingsBoard') }}
                            </button>
                        @endif

                        <a href="{{ $returnUrl }}" class="erp-btn erp-btn-secondary">
                            {{ __('Back') }}
                        </a>
                    </div>
                </form>

                @if($isDraft)
                    <form id="finishForm"
                          method="POST"
                          action="{{ route('production-entries.finish', $entry) }}"
                          style="display:none;">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                    </form>
                @endif

                @if($isFinished && auth()->user()?->canApproveProductionEntries())
                    <form id="approveForm"
                          method="POST"
                          action="{{ route('production-entries.approve', $entry) }}"
                          style="display:none;">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                    </form>
                @endif
            </div>
        @endif

        @if($activeTab === 'downtime')
            <div class="erp-card">
                <div class="downtime-head">
                    <div>
                        <h3 class="section-title">{{ __('Shift Machine Stop / Fixed') }}</h3>
                        <div class="section-subtitle">
                            {{ __('Machine downtime is linked to the full production plan/shift, not only this hour.') }}
                        </div>
                    </div>

                    <div>
                        @if($openDowntime)
                            <span class="erp-pill erp-pill-danger">{{ __('Machine In Repair') }}</span>
                        @else
                            <span class="erp-pill erp-pill-success">{{ __('Machine Active') }}</span>
                        @endif
                    </div>
                </div>

                @if($openDowntime)
                    <div class="entry-warning">
                        {{ __('Current shift machine stop is open. Click Fixed Machine after repair.') }}
                    </div>
                @else
                    <div class="entry-info">
                        {{ __('No open machine stop for this shift. You can declare a new stop if a machine is stopped.') }}
                    </div>
                @endif

                @if($isDraft || $isFinished)
                    @if(!$openDowntime)
                        <form id="stopMachineForm"
                              method="POST"
                              action="{{ route('production-entries.stop', $entry) }}">
                            @csrf
                            <input type="hidden" name="return_url" value="{{ $returnUrl }}">

                            <div class="entry-grid">
                                <div>
                                    <label>{{ __('Stopped Machine') }} <span class="required">*</span></label>
                                    <select name="machine_id" id="machine_id">
                                        <option value="">{{ __('Select machine') }}</option>
                                        @foreach($lineMachines as $machine)
                                            <option value="{{ $machine->id }}">
                                                {{ $machine->code }} - {{ $machine->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="erp-help-text">
                                        {{ __('Machine list depends on selected production line.') }}
                                    </div>
                                </div>
                            </div>

                            <div class="erp-form-actions">
                                <button type="submit"
                                        class="erp-btn erp-btn-danger"
                                        onclick="return validateStopMachine();">
                                    {{ __('Stop Machine') }}
                                </button>
                            </div>
                        </form>
                    @else
                        <form id="fixedMachineForm"
                              method="POST"
                              action="{{ route('production-entries.fixed', $entry) }}">
                            @csrf
                            <input type="hidden" name="return_url" value="{{ $returnUrl }}">

                            <div class="erp-form-actions">
                                <button type="submit" class="erp-btn erp-btn-primary">
                                    {{ __('Fixed Machine') }}
                                </button>
                            </div>
                        </form>
                    @endif
                @endif
            </div>

            <div class="erp-card">
                <div class="downtime-head">
                    <div>
                        <h3 class="section-title">{{ __('Shift Downtime Lines') }}</h3>
                        <div class="section-subtitle">
                            {{ __('Category and reason are mandatory before finishing entries.') }}
                        </div>
                    </div>
                </div>

                <div class="erp-responsive-table downtime-table-wrap">
                    <table class="erp-table downtime-table">
                        <thead>
                            <tr>
                                <th>{{ __('Machine') }}</th>
                                <th>{{ __('Started') }}</th>
                                <th>{{ __('Ended') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Reason') }}</th>
                                <th>{{ __('Comment') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($planDowntimes as $downtime)
                                <tr>
                                    <td>{{ $downtime->machine?->code }}</td>

                                    <td>
                                        {{ $downtime->started_at ? $downtime->started_at->format('H:i:s') : '-' }}
                                    </td>

                                    <td>
                                        {{ $downtime->ended_at ? $downtime->ended_at->format('H:i:s') : __('Open') }}
                                    </td>

                                    <td>{{ (int) $downtime->duration_min }} {{ __('min') }}</td>

                                    <td>
                                        @if($downtime->ended_at && !$isSent)
                                            <form id="downtimeForm{{ $downtime->id }}"
                                                  method="POST"
                                                  action="{{ route('production-downtimes.update', $downtime) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="return_url" value="{{ $returnUrl }}">

                                                <select name="downtime_category_id">
                                                    <option value="">{{ __('Select') }}</option>
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
                                        @if($downtime->ended_at && !$isSent)
                                            <select name="downtime_reason_id"
                                                    form="downtimeForm{{ $downtime->id }}">
                                                <option value="">{{ __('Select') }}</option>
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
                                        @if($downtime->ended_at && !$isSent)
                                            <input type="text"
                                                   name="comment"
                                                   form="downtimeForm{{ $downtime->id }}"
                                                   value="{{ $downtime->comment }}">
                                        @else
                                            {{ $downtime->comment ?? '-' }}
                                        @endif
                                    </td>

                                    <td>
                                        @if($downtime->ended_at && !$isSent)
                                            <button type="submit"
                                                    form="downtimeForm{{ $downtime->id }}"
                                                    class="erp-btn erp-btn-small erp-btn-primary">
                                                {{ __('Save') }}
                                            </button>
                                        @elseif(!$downtime->ended_at)
                                            <span class="erp-pill erp-pill-danger">{{ __('In Repair') }}</span>
                                        @else
                                            <span class="erp-pill erp-pill-success">{{ __('Saved') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="erp-empty">
                                        {{ __('No shift downtime lines found.') }}
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
                            {{ __('Finish Entry') }}
                        </button>
                    </div>

                    <form id="finishForm"
                          method="POST"
                          action="{{ route('production-entries.finish', $entry) }}"
                          style="display:none;">
                        @csrf
                        <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                    </form>
                @endif
            </div>
        @endif
    </div>

    <div id="popupOverlay" class="popup-overlay">
        <div class="popup-box">
            <div class="popup-icon">!</div>
            <div class="popup-title" id="popupTitle">{{ __('Warning') }}</div>
            <div class="popup-message" id="popupMessage">{{ __('Message') }}</div>
            <button type="button" class="erp-btn erp-btn-primary" onclick="closePopup()">
                OK
            </button>
        </div>
    </div>

    @include('components.erp-page-style')

    <style>
        .shift-context-card {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
            padding: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        }

        .context-label {
            font-size: 11px;
            font-weight: 900;
            color: #64748b;
            text-transform: uppercase;
        }

        .context-value {
            margin-top: 4px;
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
        }

        .erp-tabs {
            display: flex;
            flex-wrap: wrap;
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

        .entry-section-head,
        .downtime-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
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

        .entry-grid textarea {
            resize: vertical;
            min-height: 95px;
        }

        .entry-grid input[readonly],
        .entry-grid textarea[readonly] {
            background: #f8fafc;
            color: #475569;
            cursor: not-allowed;
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
            line-height: 1.4;
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

        .downtime-table-wrap {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .downtime-table {
            min-width: 980px;
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
            border-color: #16a34a;
            color: #ffffff;
        }

        .erp-btn-success:hover {
            background: #15803d;
            color: #ffffff;
        }

        .erp-btn-danger {
            background: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
        }

        .erp-btn-danger:hover {
            background: #b91c1c;
            color: #ffffff;
        }

        .erp-btn-small {
            min-height: 30px;
            padding: 5px 10px;
            font-size: 12px;
        }

        .popup-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: rgba(15, 23, 42, 0.55);
            align-items: center;
            justify-content: center;
            padding: 18px;
        }

        .popup-overlay.active {
            display: flex;
        }

        .popup-box {
            width: 100%;
            max-width: 430px;
            padding: 24px;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.3);
            text-align: center;
        }

        .popup-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 14px;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            font-weight: 900;
        }

        .popup-title {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
        }

        .popup-message {
            margin: 10px 0 20px;
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            line-height: 1.5;
        }

        @media (max-width: 1200px) {
            .shift-context-card {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .entry-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .shift-context-card,
            .entry-grid {
                grid-template-columns: 1fr;
            }

            .entry-section-head,
            .downtime-head {
                flex-direction: column;
            }

            .erp-tabs {
                flex-direction: column;
            }

            .erp-tab {
                width: 100%;
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
        const messages = {
            actualRequiredTitle: @json(__('Actual Quantity Required')),
            actualRequiredMessage: @json(__('Please enter Actual Qty before updating the production entry.')),
            finishActualMessage: @json(__('Please click Update Entry after entering Actual Qty before finishing.')),
            rejectedInvalidTitle: @json(__('Rejected Quantity Invalid')),
            rejectedInvalidMessage: @json(__('Rejected Qty cannot be greater than Actual Qty.')),
            quantityInvalidTitle: @json(__('Quantity Invalid')),
            quantityInvalidMessage: @json(__('Rejected Qty and Chute fields cannot be negative.')),
            machineRequiredTitle: @json(__('Machine Required')),
            machineRequiredMessage: @json(__('Please select the stopped machine.')),
            finishConfirm: @json(__('Finish this production entry?')),
            approveConfirm: @json(__('Approve this entry and send it automatically to ThingsBoard?'))
        };

        function showPopup(title, message) {
            const overlay = document.getElementById('popupOverlay');
            const popupTitle = document.getElementById('popupTitle');
            const popupMessage = document.getElementById('popupMessage');

            popupTitle.textContent = title;
            popupMessage.textContent = message;
            overlay.classList.add('active');
        }

        function closePopup() {
            const overlay = document.getElementById('popupOverlay');
            overlay.classList.remove('active');
        }

        function numberValue(id) {
            const element = document.getElementById(id);

            if (!element) {
                return 0;
            }

            const value = parseFloat(element.value);

            if (Number.isNaN(value)) {
                return 0;
            }

            return value;
        }

        function validateProductionQuantities() {
            const actualQty = numberValue('actual_qty');
            const rejectedQty = numberValue('rejected_qty');
            const chute1Qty = numberValue('chute_1_qty');
            const chute2Qty = numberValue('chute_2_qty');
            const chute3Qty = numberValue('chute_3_qty');

            if (actualQty <= 0) {
                showPopup(messages.actualRequiredTitle, messages.actualRequiredMessage);
                return false;
            }

            if (rejectedQty < 0 || chute1Qty < 0 || chute2Qty < 0 || chute3Qty < 0) {
                showPopup(messages.quantityInvalidTitle, messages.quantityInvalidMessage);
                return false;
            }

            if (rejectedQty > actualQty) {
                showPopup(messages.rejectedInvalidTitle, messages.rejectedInvalidMessage);
                return false;
            }

            return true;
        }

        function validateUpdateEntry() {
            return validateProductionQuantities();
        }

        function confirmFinishEntry() {
            if (!validateProductionQuantities()) {
                return;
            }

            if (confirm(messages.finishConfirm)) {
                const form = document.getElementById('finishForm');

                if (form) {
                    form.submit();
                }
            }
        }

        function confirmApproveEntry() {
            if (confirm(messages.approveConfirm)) {
                const form = document.getElementById('approveForm');

                if (form) {
                    form.submit();
                }
            }
        }

        function validateStopMachine() {
            const machineSelect = document.getElementById('machine_id');

            if (!machineSelect || !machineSelect.value) {
                showPopup(messages.machineRequiredTitle, messages.machineRequiredMessage);
                return false;
            }

            return true;
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closePopup();
            }
        });
    </script>
</x-app-layout>