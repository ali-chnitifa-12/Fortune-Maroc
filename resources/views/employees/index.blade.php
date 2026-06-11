<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Employés</h2>
                <div class="erp-page-subtitle">
                    Registre des employés utilisé par le service RH.
                </div>
            </div>

            @if(auth()->user()?->canManageAbsences())
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="{{ route('employees.import.form') }}" class="erp-btn erp-btn-secondary">
                        Importer employés
                    </a>

                    <a href="{{ route('employees.create') }}" class="erp-btn erp-btn-primary">
                        Ajouter un employé
                    </a>
                </div>
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
            <form method="GET" action="{{ route('employees.index') }}" class="employee-filter-grid">
                <div>
                    <label>Recherche</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Nom, matricule, service ou poste"
                    >
                </div>

                <div>
                    <label>Statut</label>
                    <select name="status">
                        <option value="">Tous</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>
                            Actif
                        </option>
                        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>
                            Inactif
                        </option>
                    </select>
                </div>

                <div class="employee-filter-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Filtrer
                    </button>

                    <a href="{{ route('employees.index') }}" class="erp-btn erp-btn-secondary">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <div class="erp-card">
            <div class="erp-responsive-table">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>NOM COMPLET</th>
<th>MATRICULE</th>
<th>SERVICE</th>
<th>POSTE</th>
<th>LIGNE</th>
<th>STATUT</th>
<th>CRÉÉ PAR</th>
<th>ACTIONS</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td>{{ $employee->full_name }}</td>
                                <td>{{ $employee->matricule ?: '-' }}</td>
                                <td>{{ $employee->department ?: '-' }}</td>
                                <td>{{ $employee->position ?: '-' }}</td>
                                <td>{{ $employee->productionLine?->name ?? '-' }}</td>
                                <td>
                                    @if($employee->is_active)
                                        <span class="erp-pill erp-pill-success">Actif</span>
                                    @else
                                        <span class="erp-pill erp-pill-muted">Inactif</span>
                                    @endif
                                </td>
                                <td>{{ $employee->creator?->name ?: '-' }}</td>
                                <td class="text-right">
                                    @if(auth()->user()?->canManageAbsences())
                                        <a href="{{ route('employees.edit', $employee) }}" class="erp-link">
                                            Modifier
                                        </a>

                                        <form method="POST"
                                              action="{{ route('employees.destroy', $employee) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('Supprimer cet employé ?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="erp-delete-link">
                                                Supprimer
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
                                    Aucun employé trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 14px;">
                {{ $employees->links() }}
            </div>
        </div>
    </div>

    @include('components.erp-page-style')

    <style>
        .employee-filter-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .employee-filter-grid label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 900;
            color: #334155;
        }

        .employee-filter-grid input,
        .employee-filter-grid select {
            width: 100%;
            height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
        }

        .employee-filter-actions {
            display: flex;
            gap: 8px;
        }

        .erp-pill-success {
            background: #dcfce7;
            color: #166534;
        }

        .erp-pill-muted {
            background: #e5e7eb;
            color: #374151;
        }

        @media (max-width: 900px) {
            .employee-filter-grid {
                grid-template-columns: 1fr;
            }

            .employee-filter-actions {
                flex-direction: column;
            }
        }
    </style>
</x-app-layout>