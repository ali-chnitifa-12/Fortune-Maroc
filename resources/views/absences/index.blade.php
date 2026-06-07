<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Absences</h2>
                <div class="erp-page-subtitle">
                    Manage employee absences, reasons, shifts and absence hours.
                </div>
            </div>

            @if(auth()->user()?->canManageAbsences())
                <a href="{{ route('absences.create') }}" class="erp-btn erp-btn-primary">
                    Add Absence
                </a>
            @endif
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

        <div class="erp-card" style="margin-bottom: 16px;">
            <form method="GET" action="{{ route('absences.index') }}" class="absence-filter-grid">
                <div>
                    <label>Employee</label>
                    <input
                        type="text"
                        name="employee"
                        value="{{ $filters['employee'] ?? '' }}"
                        placeholder="Search employee"
                    >
                </div>

                <div>
                    <label>Date</label>
                    <input
                        type="date"
                        name="date"
                        value="{{ $filters['date'] ?? '' }}"
                    >
                </div>

                <div>
                    <label>Shift</label>
                    <select name="shift">
                        <option value="">All shifts</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift }}" {{ ($filters['shift'] ?? '') === $shift ? 'selected' : '' }}>
                                {{ $shift }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Reason</label>
                    <select name="reason">
                        <option value="">All reasons</option>
                        @foreach($reasons as $reason)
                            <option value="{{ $reason }}" {{ ($filters['reason'] ?? '') === $reason ? 'selected' : '' }}>
                                {{ $reason }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="absence-filter-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Filter
                    </button>

                    <a href="{{ route('absences.index') }}" class="erp-btn erp-btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="erp-card">
            <div class="erp-responsive-table">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Reason</th>
                            <th>Hours</th>
                            <th>Comment</th>
                            <th>Created By</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($absences as $absence)
                            <tr>
                                <td>{{ $absence->employee_name }}</td>
                                <td>{{ $absence->absence_date?->format('Y-m-d') }}</td>
                                <td>{{ $absence->shift ?: '-' }}</td>
                                <td>
                                    <span class="erp-pill erp-pill-warning">
                                        {{ $absence->reason }}
                                    </span>
                                </td>
                                <td>{{ $absence->hours }}</td>
                                <td>{{ $absence->comment ?: '-' }}</td>
                                <td>{{ $absence->creator?->name ?: '-' }}</td>
                                <td class="text-right">
                                    @if(auth()->user()?->canManageAbsences())
                                        <a href="{{ route('absences.edit', $absence) }}" class="erp-link">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ route('absences.destroy', $absence) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('Delete this absence?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="erp-delete-link">
                                                Delete
                                            </button>
                                        </form>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="erp-empty">
                                    No absences found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 14px;">
                {{ $absences->links() }}
            </div>
        </div>
    </div>

    @include('components.erp-page-style')

    <style>
        .absence-filter-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr 1.2fr auto;
            gap: 12px;
            align-items: end;
        }

        .absence-filter-grid label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 900;
            color: #334155;
        }

        .absence-filter-grid input,
        .absence-filter-grid select {
            width: 100%;
            height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
        }

        .absence-filter-actions {
            display: flex;
            gap: 8px;
        }

        .erp-pill-warning {
            background: #fef3c7;
            color: #92400e;
        }

        @media (max-width: 1100px) {
            .absence-filter-grid {
                grid-template-columns: 1fr;
            }

            .absence-filter-actions {
                flex-direction: column;
            }
        }
    </style>
</x-app-layout>