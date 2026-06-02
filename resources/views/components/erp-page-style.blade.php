<style>
    .erp-page-wrap {
        padding: 16px 22px;
    }

    .erp-page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .erp-page-title {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .erp-page-subtitle {
        margin-top: 4px;
        font-size: 13px;
        color: #64748b;
    }

    .erp-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    .erp-form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .erp-form-grid label,
    .erp-form-section label {
        display: block;
        margin-bottom: 5px;
        font-size: 12px;
        font-weight: 800;
        color: #334155;
    }

    .erp-form-grid input,
    .erp-form-grid select,
    .erp-form-section textarea,
    .erp-table input,
    .erp-table select {
        width: 100%;
        min-height: 36px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 6px 9px;
        font-size: 13px;
        color: #0f172a;
        background: #ffffff;
    }

    .erp-form-section {
        margin-top: 14px;
    }

    .erp-form-actions {
        display: flex;
        gap: 8px;
        margin-top: 16px;
    }

    .erp-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none !important;
        border: none;
        cursor: pointer;
        white-space: nowrap;
        line-height: 1;
    }

    .erp-btn-primary {
        background: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18);
    }

    .erp-btn-primary:hover {
        background: #1d4ed8 !important;
    }

    .erp-btn-secondary {
        background: #f8fafc !important;
        color: #0f172a !important;
        border: 1px solid #e5e7eb;
    }

    .erp-btn-small {
        min-height: 28px;
        padding: 5px 9px;
        font-size: 11px;
    }

    .erp-responsive-table {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .erp-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
        font-size: 12px;
    }

    .erp-table th {
        background: #f8fafc;
        color: #334155;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        padding: 9px 8px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        white-space: nowrap;
    }

    .erp-table td {
        padding: 9px 8px;
        border-bottom: 1px solid #eef2f7;
        color: #0f172a;
        vertical-align: middle;
        white-space: nowrap;
    }

    .erp-result-title {
        font-size: 14px;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 10px;
    }

    .erp-section-title {
        font-size: 16px;
        font-weight: 900;
        color: #0f172a;
    }

    .erp-section-subtitle {
        margin-top: 3px;
        font-size: 12px;
        color: #64748b;
    }

    .text-right {
        text-align: right !important;
    }

    .erp-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        line-height: 1;
    }

    .erp-pill-success {
        background: #dcfce7;
        color: #166534;
    }

    .erp-pill-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .erp-pill-muted {
        background: #f1f5f9;
        color: #475569;
    }

    .erp-link {
        color: #2563eb;
        font-size: 12px;
        font-weight: 800;
        margin-right: 8px;
    }

    .erp-delete-link {
        color: #dc2626;
        font-size: 12px;
        font-weight: 800;
        background: transparent;
        border: none;
        cursor: pointer;
    }

    .erp-muted-small {
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
    }

    .erp-empty {
        text-align: center;
        color: #64748b !important;
        padding: 20px !important;
        font-weight: 700;
    }

    .erp-pagination {
        margin-top: 12px;
    }

    .erp-checkbox-row {
        display: inline-flex !important;
        align-items: center;
        gap: 8px;
        min-height: 36px;
    }

    .erp-checkbox-row input {
        width: auto !important;
        min-height: auto !important;
    }

    @media (max-width: 1280px) {
        .erp-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .erp-page-wrap {
            padding: 12px 10px;
        }

        .erp-page-head {
            align-items: stretch;
            flex-direction: column;
        }

        .erp-form-grid {
            grid-template-columns: 1fr;
        }

        .erp-form-actions {
            flex-direction: column;
        }

        .erp-btn {
            width: 100%;
        }

        .erp-card {
            padding: 12px;
        }
    }
</style>