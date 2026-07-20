<x-app-layout>
    <x-slot name="header">
        <h2>Gestion des Absences</h2>
    </x-slot>

    <div class="absence-page">

        {{-- Flash success --}}
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        {{-- Header bar --}}
        <div class="absence-header">
            <div>
                <h1 class="absence-title">📋 Absences</h1>
                <p class="absence-subtitle">{{ $absences->total() }} enregistrement(s) trouvé(s)</p>
            </div>
            @if(auth()->user()?->canManageAbsences())
                <a href="{{ route('absences.create') }}" class="btn-primary">
                    + Nouvelle Absence
                </a>
            @endif
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('absences.index') }}" class="filter-card">
            <div class="filter-grid">
                <div>
                    <label>Employé</label>
                    <select name="user_id">
                        <option value="">Tous les employés</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Type</label>
                    <select name="type">
                        <option value="">Tous les types</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['type'] ?? '') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Statut</label>
                    <select name="statut">
                        <option value="">Tous les statuts</option>
                        @foreach($statuts as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['statut'] ?? '') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Date début</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div>
                    <label>Date fin</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-primary">Filtrer</button>
                    <a href="{{ route('absences.index') }}" class="btn-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-card">
            @if($absences->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <div class="empty-text">Aucune absence enregistrée</div>
                </div>
            @else
                <table class="absence-table">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Motif</th>
                            <th>Statut</th>
                            <th>Notes</th>
                            @if(auth()->user()?->canManageAbsences())
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absences as $absence)
                            <tr>
                                <td class="td-user">
                                    <span class="user-avatar">{{ strtoupper(substr($absence->user?->name ?? '?', 0, 1)) }}</span>
                                    <span>{{ $absence->user?->name ?? '-' }}</span>
                                </td>
                                <td>{{ $absence->date?->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge badge-type-{{ $absence->type }}">
                                        {{ $absence->typeLabel() }}
                                    </span>
                                </td>
                                <td>{{ $absence->motif ?: '-' }}</td>
                                <td>
                                    <span class="badge badge-statut-{{ $absence->statut }}">
                                        {{ $absence->statutLabel() }}
                                    </span>
                                </td>
                                <td class="td-notes">{{ $absence->notes ?: '-' }}</td>
                                @if(auth()->user()?->canManageAbsences())
                                    <td class="td-actions">
                                        <a href="{{ route('absences.edit', $absence) }}" class="action-link-edit">Modifier</a>
                                        <form method="POST" action="{{ route('absences.destroy', $absence) }}" onsubmit="return confirm('Confirmer la suppression ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="action-link-delete">Supprimer</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pagination-wrap">
                    {{ $absences->links() }}
                </div>
            @endif
        </div>
    </div>

    <style>
        .absence-page { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .absence-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; gap: 12px; }
        .absence-title { font-size: 22px; font-weight: 900; color: #0f172a; }
        .absence-subtitle { font-size: 13px; color: #64748b; font-weight: 700; margin-top: 2px; }
        .alert-success { background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 12px 16px; border-radius: 10px; font-weight: 700; font-size: 13px; margin-bottom: 18px; }

        .filter-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; margin-bottom: 20px; }
        .filter-grid { display: grid; grid-template-columns: repeat(3, 1fr) repeat(2, 1fr) auto; gap: 12px; align-items: end; }
        .filter-grid label { display: block; font-size: 11px; font-weight: 900; color: #64748b; text-transform: uppercase; margin-bottom: 5px; }
        .filter-grid select, .filter-grid input { width: 100%; height: 38px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 10px; font-size: 13px; font-weight: 600; color: #0f172a; background: #fff; }
        .filter-actions { display: flex; gap: 8px; }

        .table-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
        .absence-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .absence-table thead tr { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .absence-table th { padding: 11px 14px; font-size: 11px; font-weight: 900; color: #64748b; text-transform: uppercase; text-align: left; white-space: nowrap; }
        .absence-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; font-weight: 600; }
        .absence-table tbody tr:last-child td { border-bottom: none; }
        .absence-table tbody tr:hover { background: #f8fafc; }

        .td-user { display: flex; align-items: center; gap: 10px; }
        .user-avatar { width: 32px; height: 32px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900; flex-shrink: 0; }
        .td-notes { max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .td-actions { display: flex; align-items: center; gap: 8px; }

        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 900; }
        .badge-type-absence { background: #fef2f2; color: #dc2626; }
        .badge-type-retard   { background: #fff7ed; color: #ea580c; }
        .badge-type-conge    { background: #eff6ff; color: #2563eb; }
        .badge-type-maladie  { background: #fdf4ff; color: #9333ea; }
        .badge-statut-pending  { background: #fefce8; color: #ca8a04; }
        .badge-statut-approved { background: #f0fdf4; color: #16a34a; }
        .badge-statut-rejected { background: #fef2f2; color: #dc2626; }

        .action-link-edit { font-size: 12px; font-weight: 800; color: #2563eb; text-decoration: none; padding: 4px 10px; border-radius: 6px; background: #eff6ff; }
        .action-link-delete { font-size: 12px; font-weight: 800; color: #dc2626; background: #fef2f2; border: none; padding: 4px 10px; border-radius: 6px; cursor: pointer; }
        .action-link-edit:hover { background: #dbeafe; }
        .action-link-delete:hover { background: #fee2e2; }

        .btn-primary { height: 38px; padding: 0 18px; background: #2563eb; color: #fff; border: none; border-radius: 9px; font-size: 13px; font-weight: 900; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { height: 38px; padding: 0 16px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 9px; font-size: 13px; font-weight: 900; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }

        .empty-state { padding: 60px 20px; text-align: center; }
        .empty-icon { font-size: 48px; margin-bottom: 12px; }
        .empty-text { font-size: 15px; font-weight: 800; color: #94a3b8; }
        .pagination-wrap { padding: 14px 16px; border-top: 1px solid #f1f5f9; }

        @media(max-width: 900px) {
            .filter-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</x-app-layout>
