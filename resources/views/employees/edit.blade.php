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

        .erp-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
        }

        .erp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 34px;
            padding: 0 14px;
            border-radius: 8px;
            border: 1px solid transparent;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
        }

        .erp-btn-primary {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        .erp-btn-secondary {
            background: #f8fafc;
            color: #0f172a;
            border-color: #e2e8f0;
        }

        .erp-form-actions {
            margin-top: 18px;
            display: flex;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .erp-page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .erp-content-wrap {
                padding: 14px;
            }

            .erp-form-actions {
                flex-direction: column;
            }
        }
    </style>

    <div class="erp-page-header">
        <div>
            <h1>Modifier un employé</h1>
            <p>Mettre à jour les informations de l'employé.</p>
        </div>

        <a href="{{ route('employees.index') }}" class="erp-btn erp-btn-secondary">
            Retour aux employés
        </a>
    </div>

    <div class="erp-content-wrap">
        <div class="erp-card">
            <form method="POST" action="{{ route('employees.update', $employee) }}">
                @csrf
                @method('PUT')

                @include('employees.form')

                <div class="erp-form-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Mettre à jour
                    </button>

                    <a href="{{ route('employees.index') }}" class="erp-btn erp-btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>