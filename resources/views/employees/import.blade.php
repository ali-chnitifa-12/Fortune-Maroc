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
            max-width: 760px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
        }

        .erp-help {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #334155;
            font-size: 13px;
            line-height: 1.6;
        }

        .erp-help code {
            font-weight: 900;
            color: #0f172a;
        }

        .erp-field label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 900;
            color: #334155;
        }

        .erp-field input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 9px 10px;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
        }

        .erp-error {
            margin-top: 8px;
            color: #dc2626;
            font-size: 13px;
            font-weight: 700;
        }

        .erp-form-actions {
            margin-top: 18px;
            display: flex;
            gap: 10px;
        }

        .erp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 36px;
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
            <h1>Importer des employés</h1>
            <p>Importer rapidement une liste d'employés depuis un fichier Excel.</p>
        </div>

        <a href="{{ route('employees.index') }}" class="erp-btn erp-btn-secondary">
            Retour aux employés
        </a>
    </div>

    <div class="erp-content-wrap">
        <div class="erp-card">
            <div class="erp-help">
                Le fichier doit être au format <strong>Excel (.xlsx)</strong> ou <strong>CSV UTF-8</strong>.
                <br>
                Colonnes attendues :
                <br>
                <code>NOM COMPLET;MATRICULE;SERVICE;POSTE;LIGNE</code>
                <br>
                Exemple :
                <br>
                <code>Sara Atif;BA76543;Production;Opératrice;Ligne 1</code>
            </div>

            <form method="POST" action="{{ route('employees.import') }}" enctype="multipart/form-data">
                @csrf

                <div class="erp-field">
                    <label>Fichier Excel</label>
                    <input type="file" name="file" accept=".xlsx,.csv,.txt" required>

                    @error('file')
                        <div class="erp-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="erp-form-actions">
                    <button type="submit" class="erp-btn erp-btn-primary">
                        Importer
                    </button>

                    <a href="{{ route('employees.index') }}" class="erp-btn erp-btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>