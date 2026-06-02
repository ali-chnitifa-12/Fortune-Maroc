<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h2 class="font-semibold text-xl leading-tight">
                    Production Dashboard
                </h2>
                <div class="fortune-header-subtitle">
                    Today: {{ now()->format('Y-m-d') }}
                </div>
            </div>

            <a href="{{ route('production-entries.create') }}"
               class="fortune-btn-primary">
                Add Production Entry
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:18px;">
                <div class="dashboard-kpi-card">
                    <div class="dashboard-kpi-label">Today Entries</div>
                    <div class="dashboard-kpi-value">{{ $stats['today_entries'] ?? 0 }}</div>
                </div>

                <div class="dashboard-kpi-card">
                    <div class="dashboard-kpi-label">Planned Qty</div>
                    <div class="dashboard-kpi-value">{{ number_format($stats['planned_qty'] ?? 0, 2) }}</div>
                </div>

                <div class="dashboard-kpi-card">
                    <div class="dashboard-kpi-label">Actual Qty</div>
                    <div class="dashboard-kpi-value">{{ number_format($stats['actual_qty'] ?? 0, 2) }}</div>
                </div>

                <div class="dashboard-kpi-card">
                    <div class="dashboard-kpi-label">Good Qty</div>
                    <div class="dashboard-kpi-value">{{ number_format($stats['good_qty'] ?? 0, 2) }}</div>
                </div>

                <div class="dashboard-kpi-card">
                    <div class="dashboard-kpi-label">Rejected Qty</div>
                    <div class="dashboard-kpi-value">{{ number_format($stats['rejected_qty'] ?? 0, 2) }}</div>
                </div>

                <div class="dashboard-kpi-card">
                    <div class="dashboard-kpi-label">Downtime Min</div>
                    <div class="dashboard-kpi-value">{{ $stats['downtime_min'] ?? 0 }}</div>
                </div>

                <div class="dashboard-kpi-card">
                    <div class="dashboard-kpi-label">Average OEE</div>
                    <div class="dashboard-kpi-value">{{ $stats['average_oee'] ?? 0 }}%</div>
                </div>

                <div class="dashboard-kpi-card">
                    <div class="dashboard-kpi-label">Not Sent to TB</div>
                    <div class="dashboard-kpi-value">{{ $stats['not_sent_to_tb'] ?? 0 }}</div>
                </div>
            </div>

            <div class="dashboard-panel" style="margin-bottom:18px;">
                <div class="dashboard-panel-title">Quick Actions</div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="{{ route('production-entries.index') }}" class="dashboard-action-btn">
                        Production Entries
                    </a>

                    <a href="{{ route('production-entries.create') }}" class="dashboard-action-btn primary">
                        Add Entry
                    </a>

                    @can('view-master-data')
                        <a href="{{ route('machines.index') }}" class="dashboard-action-btn">
                            Machines
                        </a>

                        <a href="{{ route('products.index') }}" class="dashboard-action-btn">
                            Products
                        </a>
                    @endcan

                    @can('manage-users')
                        <a href="{{ route('users-management.index') }}" class="dashboard-action-btn">
                            Users
                        </a>
                    @endcan
                </div>
            </div>

            <div class="dashboard-panel">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <div class="dashboard-panel-title">Latest Production Entries</div>

                    <a href="{{ route('production-entries.index') }}"
                       style="font-weight:800;color:#2563eb;text-decoration:none;">
                        View all
                    </a>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Hour</th>
                            <th>Shift</th>
                            <th>Machine</th>
                            <th>Product</th>
                            <th style="text-align:right;">Actual</th>
                            <th style="text-align:right;">Good</th>
                            <th style="text-align:right;">OEE %</th>
                            <th>TB</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($latestEntries as $entry)
                            <tr>
                                <td>{{ $entry->production_date?->format('Y-m-d') }}</td>
                                <td>{{ substr($entry->hour_start, 0, 5) }} - {{ substr($entry->hour_end, 0, 5) }}</td>
                                <td>{{ $entry->shift?->code }}</td>
                                <td>{{ $entry->machine?->code }}</td>
                                <td>{{ $entry->product?->name }}</td>
                                <td style="text-align:right;">{{ $entry->actual_qty }}</td>
                                <td style="text-align:right;">{{ $entry->good_qty }}</td>
                                <td style="text-align:right;">{{ $entry->oee }}</td>
                                <td>
                                    @if($entry->sent_to_thingsboard)
                                        <span class="dashboard-badge success">Sent</span>
                                    @else
                                        <span class="dashboard-badge warning">Not sent</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align:center;color:#6b7280;padding:18px;">
                                    No production entries found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <style>
        .dashboard-kpi-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }

        .dashboard-kpi-label {
            color: #6b7280;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .dashboard-kpi-value {
            color: #111827;
            font-size: 27px;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .dashboard-panel {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }

        .dashboard-panel-title {
            color: #111827;
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 14px;
        }

        .dashboard-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 8px 15px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background: white;
            color: #111827;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
        }

        .dashboard-action-btn:hover {
            background: #f9fafb;
        }

        .dashboard-action-btn.primary {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
            box-shadow: 0 6px 14px rgba(37, 99, 235, 0.18);
        }

        .dashboard-action-btn.primary:hover {
            background: #1d4ed8;
        }

        .dashboard-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }

        .dashboard-badge.success {
            background: #dcfce7;
            color: #166534;
        }

        .dashboard-badge.warning {
            background: #fef3c7;
            color: #92400e;
        }

        @media (max-width: 1024px) {
            div[style*="grid-template-columns:repeat(4,1fr)"] {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 640px) {
            div[style*="grid-template-columns:repeat(4,1fr)"] {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</x-app-layout>