<style>
    /* ==========================================================================
       Financial Reports Executive Dashboard Stylesheet
       Compatible with Filament v3 Light & Dark Modes
       ========================================================================== */

    .fr-wrapper {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        width: 100%;
        color: #1e293b;
    }
    .dark .fr-wrapper {
        color: #f1f5f9;
    }

    /* Executive Header Banner */
    .fr-banner {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
        padding: 1.75rem;
        color: #ffffff;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -4px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.12);
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1.25rem;
    }
    .fr-banner-title {
        font-size: 1.75rem;
        font-weight: 900;
        letter-spacing: -0.025em;
        line-height: 1.2;
        margin: 0.35rem 0;
        color: #ffffff;
    }
    .fr-banner-subtitle {
        font-size: 0.8125rem;
        color: #cbd5e1;
        margin: 0;
    }
    .fr-banner-badges {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .fr-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.025em;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #e2e8f0;
    }
    .fr-banner-metrics {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .fr-banner-card {
        background: rgba(255, 255, 255, 0.07);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 0.75rem;
        padding: 0.625rem 1rem;
        text-align: right;
    }
    .fr-banner-card-lbl {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.075em;
        color: #94a3b8;
    }
    .fr-banner-card-val {
        font-size: 0.9375rem;
        font-weight: 800;
        color: #ffffff;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    /* Filters Bar */
    .fr-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .dark .fr-card {
        background-color: #111827;
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    .fr-filters-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
        align-items: flex-end;
    }
    @media (min-width: 640px) {
        .fr-filters-grid.cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .fr-filters-grid.cols-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (min-width: 1024px) {
        .fr-filters-grid.cols-4 { grid-template-columns: 1fr 1fr 1.2fr auto; }
        .fr-filters-grid.cols-3 { grid-template-columns: 1.2fr 1.5fr auto; }
    }
    .fr-filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }
    .fr-filter-label {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
    }
    .dark .fr-filter-label {
        color: #94a3b8;
    }
    .fr-filter-input, .fr-filter-select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border-radius: 0.625rem;
        border: 1px solid #cbd5e1;
        background-color: #f8fafc;
        color: #0f172a;
        font-size: 0.875rem;
        font-weight: 500;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .dark .fr-filter-input, .dark .fr-filter-select {
        border-color: #334155;
        background-color: #1e293b;
        color: #ffffff;
    }
    .fr-filter-input:focus, .fr-filter-select:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
    }
    .fr-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.55rem 1.25rem;
        border-radius: 0.625rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: #ffffff;
        background-color: #0f172a;
        border: 1px solid #1e293b;
        cursor: pointer;
        transition: background-color 0.15s ease, transform 0.1s ease;
        height: 38px;
    }
    .dark .fr-btn {
        background-color: #1e293b;
        border-color: #334155;
    }
    .fr-btn:hover {
        background-color: #1e293b;
    }
    .dark .fr-btn:hover {
        background-color: #334155;
    }

    /* KPI Summary Cards */
    .fr-kpi-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
    }
    @media (min-width: 640px) {
        .fr-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (min-width: 1024px) {
        .fr-kpi-grid.cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .fr-kpi-grid.cols-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
    }
    .fr-kpi-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .dark .fr-kpi-card {
        background-color: #111827;
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    .fr-kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .fr-kpi-title {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
    }
    .dark .fr-kpi-title {
        color: #94a3b8;
    }
    .fr-kpi-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 0.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .fr-kpi-val {
        margin-top: 0.75rem;
        font-size: 1.625rem;
        font-weight: 900;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        letter-spacing: -0.03em;
        line-height: 1.2;
        color: #0f172a;
    }
    .dark .fr-kpi-val {
        color: #ffffff;
    }
    .fr-kpi-curr {
        font-size: 0.6875rem;
        font-weight: 700;
        color: #94a3b8;
        margin-left: 0.25rem;
    }
    .fr-kpi-desc {
        margin-top: 0.35rem;
        font-size: 0.6875rem;
        font-weight: 500;
        color: #64748b;
    }
    .dark .fr-kpi-desc {
        color: #94a3b8;
    }

    /* Colors */
    .fr-text-emerald { color: #059669; }
    .dark .fr-text-emerald { color: #34d399; }
    .fr-bg-emerald { background-color: rgba(16, 185, 129, 0.1); color: #059669; }
    .dark .fr-bg-emerald { background-color: rgba(16, 185, 129, 0.2); color: #34d399; }

    .fr-text-rose { color: #e11d48; }
    .dark .fr-text-rose { color: #fb7185; }
    .fr-bg-rose { background-color: rgba(244, 63, 94, 0.1); color: #e11d48; }
    .dark .fr-bg-rose { background-color: rgba(244, 63, 94, 0.2); color: #fb7185; }

    .fr-text-indigo { color: #4f46e5; }
    .dark .fr-text-indigo { color: #818cf8; }
    .fr-bg-indigo { background-color: rgba(79, 70, 229, 0.1); color: #4f46e5; }
    .dark .fr-bg-indigo { background-color: rgba(79, 70, 229, 0.2); color: #818cf8; }

    .fr-text-amber { color: #d97706; }
    .dark .fr-text-amber { color: #fbbf24; }
    .fr-bg-amber { background-color: rgba(217, 119, 6, 0.1); color: #d97706; }
    .dark .fr-bg-amber { background-color: rgba(217, 119, 6, 0.2); color: #fbbf24; }

    .fr-text-blue { color: #2563eb; }
    .dark .fr-text-blue { color: #60a5fa; }
    .fr-bg-blue { background-color: rgba(37, 99, 235, 0.1); color: #2563eb; }
    .dark .fr-bg-blue { background-color: rgba(37, 99, 235, 0.2); color: #60a5fa; }

    .fr-text-purple { color: #7c3aed; }
    .dark .fr-text-purple { color: #a78bfa; }
    .fr-bg-purple { background-color: rgba(124, 58, 237, 0.1); color: #7c3aed; }
    .dark .fr-bg-purple { background-color: rgba(124, 58, 237, 0.2); color: #a78bfa; }

    /* Tables */
    .fr-table-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    .dark .fr-table-card {
        background-color: #111827;
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    .fr-table-header-bar {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .dark .fr-table-header-bar {
        border-bottom-color: #1f2937;
    }
    .fr-table-heading {
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: -0.01em;
        margin: 0;
        color: #0f172a;
    }
    .dark .fr-table-heading {
        color: #ffffff;
    }
    .fr-table-subheading {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.25rem;
    }
    .dark .fr-table-subheading {
        color: #94a3b8;
    }
    .fr-table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .fr-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
        text-align: left;
    }
    .fr-table th {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        background-color: #f8fafc;
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .dark .fr-table th {
        color: #94a3b8;
        background-color: #1a2234;
        border-bottom-color: #1f2937;
    }
    .fr-table td {
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .dark .fr-table td {
        border-bottom-color: #1e293b;
        color: #cbd5e1;
    }
    .fr-table tbody tr:hover td {
        background-color: #f8fafc;
    }
    .dark .fr-table tbody tr:hover td {
        background-color: #1a2234;
    }
    .fr-num {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        text-align: right;
        font-weight: 600;
    }
    .fr-table tfoot td {
        padding: 1rem 1.25rem;
        font-weight: 800;
        border-top: 2px solid #e2e8f0;
        border-bottom: 3px double #0f172a;
        background-color: #f8fafc;
        color: #0f172a;
    }
    .dark .fr-table tfoot td {
        border-top-color: #334155;
        border-bottom: 3px double #ffffff;
        background-color: #1a2234;
        color: #ffffff;
    }

    /* 2-Column Balance Sheet */
    .fr-two-col {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    @media (min-width: 1024px) {
        .fr-two-col {
            grid-template-columns: 1fr 1fr;
        }
    }

    /* Status Badges */
    .fr-badge-success {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 800;
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .dark .fr-badge-success {
        background-color: rgba(22, 101, 52, 0.3);
        color: #4ade80;
        border-color: rgba(74, 222, 128, 0.25);
    }
    .fr-badge-danger {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 800;
        background-color: #ffe4e6;
        color: #9f1239;
        border: 1px solid #fecdd3;
    }
    .dark .fr-badge-danger {
        background-color: rgba(159, 18, 57, 0.3);
        color: #fb7185;
        border-color: rgba(251, 113, 133, 0.25);
    }
</style>
