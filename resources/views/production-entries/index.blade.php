<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Production Entries</h2>
                <div class="erp-page-subtitle">
                    Hourly entries generated automatically from shift production plans.
                </div>
            </div>

            <a href="{{ route('production-plans.index') }}" class="erp-btn erp-btn-primary">
                Create Plan
            </a>
        </div>
    </x-slot>

    @php
        $sortField = request('sort', 'production_date');
        $sortDirection = request('direction', 'desc');

        $sortUrl = function ($field) use ($sortField, $sortDirection) {
            $nextDirection = ($sortField === $field && $sortDirection === 'asc') ? 'desc' : 'asc';

            return request()->fullUrlWithQuery([
                'sort' => $field,
                'direction' => $nextDirection,
                'page' => 1,
            ]);
        };

        $sortIcon = function ($field) use ($sortField, $sortDirection) {
            if ($sortField !== $field) {
                return '↕';
            }

            return $sortDirection === 'asc' ? '↑' : '↓';
        };
    @endphp

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

                <input type="hidden" name="sort" value="{{ $sortField }}">
                <input type="hidden" name="direction" value="{{ $sortDirection }}">

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

            <div class="erp-responsive-table clean-table-wrap">
                <table class="erp-table clean-entry-table">
                    <thead>
                        <tr>
                            <th><a class="sort-link" href="{{ $sortUrl('entry_code') }}">Entry {{ $sortIcon('entry_code') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('plan_code') }}">Plan {{ $sortIcon('plan_code') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('production_date') }}">Date {{ $sortIcon('production_date') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('hour') }}">Hour {{ $sortIcon('hour') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('shift') }}">Shift {{ $sortIcon('shift') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('zone') }}">Zone {{ $sortIcon('zone') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('line') }}">Line {{ $sortIcon('line') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('product') }}">Product {{ $sortIcon('product') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('planned_qty') }}">Planned {{ $sortIcon('planned_qty') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('actual_qty') }}">Actual {{ $sortIcon('actual_qty') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('good_qty') }}">Good {{ $sortIcon('good_qty') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('rejected_qty') }}">Rejected {{ $sortIcon('rejected_qty') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('chute_1_qty') }}">Chute 1 {{ $sortIcon('chute_1_qty') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('chute_2_qty') }}">Chute 2 {{ $sortIcon('chute_2_qty') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('chute_3_qty') }}">Chute 3 {{ $sortIcon('chute_3_qty') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('stop_duration_min') }}">Stop {{ $sortIcon('stop_duration_min') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('oee') }}">OEE {{ $sortIcon('oee') }}</a></th>
                            <th><a class="sort-link" href="{{ $sortUrl('entry_status') }}">Status {{ $sortIcon('entry_status') }}</a></th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td><strong>{{ $entry->entry_code ?? '-' }}</strong></td>

                                <td>
                                    <strong>{{ $entry->productionPlan?->plan_code ?? '-' }}</strong>
                                </td>

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

                                <td>{{ number_format((float) $entry->chute_1_qty, 2) }}</td>

                                <td>{{ number_format((float) $entry->chute_2_qty, 2) }}</td>

                                <td>{{ number_format((float) $entry->chute_3_qty, 2) }}</td>

                                <td>{{ (int) $entry->stop_duration_min }} m</td>

                                <td>{{ number_format((float) $entry->oee, 2) }}%</td>

                                <td>
                                    @if($entry->entry_status === 'draft')
                                        <span class="erp-pill erp-pill-warning">Draft</span>
                                    @elseif($entry->entry_status === 'finished')
                                        <span class="erp-pill erp-pill-info">Finished</span>
                                    @elseif($entry->entry_status === 'sent_to_thingsboard')
                                        <span class="erp-pill erp-pill-success">Sent</span>
                                    @else
                                        <span class="erp-pill">{{ ucwords(str_replace('_', ' ', $entry->entry_status)) }}</span>
                                    @endif
                                </td>

                                <td class="actions-column">
                                    <div class="action-dropdown">
                                        <button type="button" class="action-menu-button" onclick="toggleActionMenu(event, 'entry-actions-{{ $entry->id }}')">
                                            Actions
                                            <span>▾</span>
                                        </button>

                                        <div id="entry-actions-{{ $entry->id }}" class="action-menu">
                                            @if($entry->entry_status === 'draft')
                                                <a href="{{ route('production-entries.edit', $entry) }}" class="action-menu-item">
                                                    Open Entry
                                                </a>
                                            @elseif($entry->entry_status === 'finished')
                                                <a href="{{ route('production-entries.edit', $entry) }}" class="action-menu-item">
                                                    Review Entry
                                                </a>

                                                @if(auth()->user()?->canApproveProductionEntries())
                                                    <form method="POST"
                                                          action="{{ route('production-entries.approve', $entry) }}"
                                                          onsubmit="return confirm('Approve this entry and send it automatically to ThingsBoard?')">
                                                        @csrf
                                                        <button type="submit" class="action-menu-item action-menu-success">
                                                            Approve & Send
                                                        </button>
                                                    </form>
                                                @endif
                                            @elseif($entry->entry_status === 'sent_to_thingsboard')
                                                <a href="{{ route('production-entries.edit', $entry) }}" class="action-menu-item">
                                                    View Entry
                                                </a>
                                            @endif

                                            @if(auth()->user()?->canDeleteProductionEntries() && $entry->entry_status !== 'sent_to_thingsboard')
                                                <form method="POST"
                                                      action="{{ route('production-entries.destroy', $entry) }}"
                                                      onsubmit="return confirm('Delete this production entry?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-menu-item action-menu-danger">
                                                        Delete Entry
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="19" class="erp-empty">
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

        .clean-table-wrap {
            overflow-x: auto;
            overflow-y: visible;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .clean-entry-table {
            min-width: 1650px;
        }

        .sort-link {
            color: #0f172a;
            text-decoration: none;
            font-weight: 900;
            white-space: nowrap;
        }

        .sort-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        .actions-column {
            width: 120px;
            min-width: 120px;
            text-align: right;
            position: relative;
        }

        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-menu-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 32px;
            padding: 7px 12px;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
            white-space: nowrap;
        }

        .action-menu-button:hover {
            background: #dbeafe;
            color: #1e40af;
        }

        .action-menu {
            display: none;
            position: absolute;
            top: 38px;
            right: 0;
            z-index: 999;
            width: 180px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
            padding: 6px;
            text-align: left;
        }

        .action-menu.active {
            display: block;
        }

        .action-menu-item {
            display: block;
            width: 100%;
            padding: 9px 10px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #334155;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.2;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
        }

        .action-menu-item:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .action-menu-success {
            color: #15803d;
        }

        .action-menu-success:hover {
            background: #dcfce7;
            color: #166534;
        }

        .action-menu-danger {
            color: #dc2626;
        }

        .action-menu-danger:hover {
            background: #fee2e2;
            color: #991b1b;
        }

        .erp-pill-info {
            background: #dbeafe;
            color: #1d4ed8;
        }

        @media (max-width: 1400px) {
            .entry-filter-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .clean-entry-table {
                min-width: 1550px;
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
        function toggleActionMenu(event, menuId) {
            event.stopPropagation();

            document.querySelectorAll('.action-menu').forEach(menu => {
                if (menu.id !== menuId) {
                    menu.classList.remove('active');
                }
            });

            const menu = document.getElementById(menuId);

            if (menu) {
                menu.classList.toggle('active');
            }
        }

        document.addEventListener('click', function () {
            document.querySelectorAll('.action-menu').forEach(menu => {
                menu.classList.remove('active');
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.action-menu').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
        });

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