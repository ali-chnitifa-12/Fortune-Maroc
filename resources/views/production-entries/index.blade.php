<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Production Entries</h2>
                <div class="erp-page-subtitle">
                    Draft, finish, approve, and automatically send production entries to ThingsBoard.
                </div>
            </div>

            <a href="{{ route('production-plans.index') }}" class="erp-btn erp-btn-primary">
                Create Entry from Plan
            </a>
        </div>
    </x-slot>

    <div class="erp-page-wrap">
        @if(session('success'))
            <div class="fortune-success">{{ session('success') }}</div>
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

        <div class="erp-card">
            <form method="GET" action="{{ route('production-entries.index') }}">
                <div class="entry-filter-grid">
                    <div>
                        <label>Date From</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>

                    <div>
                        <label>Date To</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>

                    <div>
                        <label>Zone</label>
                        <select name="zone_id" id="zone_filter">
                            <option value="">All Zones</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ ($filters['zone_id'] ?? '') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->code }} - {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Line</label>
                        <select name="production_line_id" id="line_filter">
                            <option value="">All Lines</option>
                            @foreach($productionLines as $line)
                                <option value="{{ $line->id }}"
                                        data-zone-id="{{ $line->zone_id }}"
                                    {{ ($filters['production_line_id'] ?? '') == $line->id ? 'selected' : '' }}>
                                    {{ $line->code }} - {{ $line->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Product</label>
                        <select name="product_id">
                            <option value="">All Products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ ($filters['product_id'] ?? '') == $product->id ? 'selected' : '' }}>
                                    {{ $product->code }} - {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Shift</label>
                        <select name="shift_id">
                            <option value="">All Shifts</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ ($filters['shift_id'] ?? '') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->code }} - {{ $shift->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Status</label>
                        <select name="entry_status">
                            <option value="">All</option>
                            <option value="draft" {{ ($filters['entry_status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="finished" {{ ($filters['entry_status'] ?? '') === 'finished' ? 'selected' : '' }}>Finished</option>
                            <option value="sent_to_thingsboard" {{ ($filters['entry_status'] ?? '') === 'sent_to_thingsboard' ? 'selected' : '' }}>Sent To ThingsBoard</option>
                        </select>
                    </div>
                </div>

                <div class="entry-filter-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Apply Filters
                    </button>

                    <a href="{{ route('production-entries.index') }}" class="erp-btn erp-btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="erp-card">
            <div class="erp-result-title">
                Results: {{ $entries->total() }}
            </div>

            <div class="erp-responsive-table">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Hour</th>
                            <th>Shift</th>
                            <th>Zone</th>
                            <th>Line</th>
                            <th>Product</th>
                            <th>Planned</th>
                            <th>Actual</th>
                            <th>Good</th>
                            <th>Rejected</th>
                            <th>Chute</th>
                            <th>Stop</th>
                            <th>OEE</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td>{{ $entry->production_date?->format('Y-m-d') }}</td>

                                <td>
                                    {{ $entry->hour_start ? substr($entry->hour_start, 0, 5) : '-' }}
                                    -
                                    {{ $entry->hour_end ? substr($entry->hour_end, 0, 5) : '-' }}
                                </td>

                                <td>{{ $entry->shift?->code ?? '-' }}</td>

                                <td>{{ $entry->zone?->code ?? '-' }}</td>

                                <td>{{ $entry->productionLine?->code ?? '-' }}</td>

                                <td>
                                    <strong>{{ $entry->product?->code ?? '-' }}</strong>
                                    <div class="erp-muted-small">{{ $entry->product?->name }}</div>
                                </td>

                                <td>{{ number_format((float) $entry->planned_qty, 2) }}</td>

                                <td>{{ number_format((float) $entry->actual_qty, 2) }}</td>

                                <td>{{ number_format((float) $entry->good_qty, 2) }}</td>

                                <td>{{ number_format((float) $entry->rejected_qty, 2) }}</td>

                                <td>{{ number_format((float) $entry->chute_qty, 2) }}</td>

                                <td>{{ (int) $entry->stop_duration_min }} m</td>

                                <td>{{ number_format((float) $entry->oee, 2) }}%</td>

                                <td>
                                    @if($entry->entry_status === 'draft')
                                        <span class="erp-pill erp-pill-warning">Draft</span>
                                    @elseif($entry->entry_status === 'finished')
                                        <span class="erp-pill erp-pill-info">Finished</span>
                                    @elseif($entry->entry_status === 'sent_to_thingsboard')
                                        <span class="erp-pill erp-pill-success">Sent To ThingsBoard</span>
                                    @else
                                        <span class="erp-pill">{{ ucwords(str_replace('_', ' ', $entry->entry_status)) }}</span>
                                    @endif
                                </td>

                                <td class="text-right">
                                    @if($entry->entry_status === 'draft')
                                        <a href="{{ route('production-entries.edit', $entry) }}" class="erp-link">
                                            Open
                                        </a>
                                    @elseif($entry->entry_status === 'finished')
                                        <a href="{{ route('production-entries.edit', $entry) }}" class="erp-link">
                                            Review
                                        </a>

                                        @if(auth()->user()?->canApproveProductionEntries())
                                            <form method="POST"
                                                  action="{{ route('production-entries.approve', $entry) }}"
                                                  style="display:inline;"
                                                  onsubmit="return confirm('Approve this entry and send it automatically to ThingsBoard?')">
                                                @csrf
                                                <button type="submit" class="erp-action-button">
                                                    Approve & Send
                                                </button>
                                            </form>
                                        @endif
                                    @elseif($entry->entry_status === 'sent_to_thingsboard')
                                        <a href="{{ route('production-entries.edit', $entry) }}" class="erp-link">
                                            View
                                        </a>
                                    @endif

                                    @if(auth()->user()?->canDeleteProductionEntries() && $entry->entry_status !== 'sent_to_thingsboard')
                                        <form method="POST"
                                              action="{{ route('production-entries.destroy', $entry) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('Delete this production entry?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="erp-delete-link">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="erp-empty">
                                    No production entries found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="erp-pagination">
                {{ $entries->links() }}
            </div>
        </div>
    </div>

    @include('components.erp-page-style')

    <style>
        .entry-filter-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 12px;
        }

        .entry-filter-grid label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            font-weight: 800;
            color: #334155;
        }

        .entry-filter-grid input,
        .entry-filter-grid select {
            width: 100%;
            height: 36px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 9px;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
        }

        .entry-filter-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .erp-action-button {
            margin-left: 8px;
            border: 0;
            background: transparent;
            color: #2563eb;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
        }

        .erp-action-button:hover {
            text-decoration: underline;
        }

        .erp-pill-info {
            background: #dbeafe;
            color: #1d4ed8;
        }

        @media (max-width: 1400px) {
            .entry-filter-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .entry-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .entry-filter-grid {
                grid-template-columns: 1fr;
            }

            .entry-filter-actions {
                flex-direction: column;
            }
        }
    </style>

    <script>
        const zoneFilter = document.getElementById('zone_filter');
        const lineFilter = document.getElementById('line_filter');

        function filterLinesByZone() {
            if (!zoneFilter || !lineFilter) {
                return;
            }

            const zoneId = zoneFilter.value;

            Array.from(lineFilter.options).forEach(option => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                const optionZoneId = option.getAttribute('data-zone-id');
                option.hidden = zoneId && optionZoneId !== zoneId;
            });

            const selectedOption = lineFilter.options[lineFilter.selectedIndex];

            if (selectedOption && selectedOption.hidden) {
                lineFilter.value = '';
            }
        }

        if (zoneFilter) {
            zoneFilter.addEventListener('change', filterLinesByZone);
            filterLinesByZone();
        }
    </script>
</x-app-layout>