<x-app-layout>
    <style>
        .erp-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 24px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .erp-page-header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
        }

        .erp-page-header p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #64748b;
        }

        .erp-content-wrap {
            padding: 18px 24px;
        }

        .hr-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .hr-stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
        }

        .hr-stat-label {
            font-size: 12px;
            font-weight: 900;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .hr-stat-value {
            margin-top: 8px;
            font-size: 30px;
            font-weight: 900;
            color: #0f172a;
        }

        .erp-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
        }

        .erp-card h2 {
            margin: 0 0 12px;
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
        }

        .erp-table {
            width: 100%;
            border-collapse: collapse;
        }

        .erp-table th {
            text-align: left;
            font-size: 12px;
            color: #64748b;
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .erp-table td {
            font-size: 13px;
            color: #0f172a;
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .erp-empty {
            padding: 18px;
            text-align: center;
            color: #64748b;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .hr-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .erp-page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .erp-content-wrap {
                padding: 14px;
            }

            .hr-stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="erp-page-header">
        <div>
            <h1>Dashboard RH</h1>
            <p>Vue globale des employés et des absences.</p>
        </div>
    </div>

    <div class="erp-content-wrap">
        <div class="hr-stats-grid">
            <div class="hr-stat-card">
                <div class="hr-stat-label">Total employés</div>
                <div class="hr-stat-value">{{ $totalEmployees }}</div>
            </div>

            <div class="hr-stat-card">
                <div class="hr-stat-label">Employés actifs</div>
                <div class="hr-stat-value">{{ $activeEmployees }}</div>
            </div>

            <div class="hr-stat-card">
                <div class="hr-stat-label">Départs / inactifs</div>
                <div class="hr-stat-value">{{ $inactiveEmployees }}</div>
            </div>

            <div class="hr-stat-card">
                <div class="hr-stat-label">Absences total</div>
                <div class="hr-stat-value">{{ $totalAbsences }}</div>
            </div>

            <div class="hr-stat-card">
                <div class="hr-stat-label">Absences aujourd'hui</div>
                <div class="hr-stat-value">{{ $todayAbsences }}</div>
            </div>

            <div class="hr-stat-card">
                <div class="hr-stat-label">Absences ce mois</div>
                <div class="hr-stat-value">{{ $monthAbsences }}</div>
            </div>
        </div>

        <div class="erp-card">
            <h2>Dernières absences</h2>

            @if($latestAbsences->isEmpty())
                <div class="erp-empty">Aucune absence trouvée.</div>
            @else
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employé</th>
                            <th>Matricule</th>
                            <th>Service</th>
                            <th>Motif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($latestAbsences as $absence)
                            <tr>
                                <td>{{ optional($absence->absence_date)->format('d/m/Y') ?? $absence->absence_date }}</td>
                                <td>{{ $absence->employee?->full_name ?? $absence->employee_name ?? '-' }}</td>
                                <td>{{ $absence->employee_matricule ?? $absence->employee?->matricule ?? '-' }}</td>
                                <td>{{ $absence->employee?->department ?? $absence->department ?? '-' }}</td>
                                <td>{{ $absence->reason ?? $absence->absence_reason ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-app-layout>