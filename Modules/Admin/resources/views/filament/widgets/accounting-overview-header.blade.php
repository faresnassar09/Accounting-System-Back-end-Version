<div style="
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0f172a 100%);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 1rem;
    padding: 1.75rem 2rem;
    color: #ffffff;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
    margin-bottom: 0.5rem;
">
    {{-- Subtle background decoration --}}
    <div style="
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.12) 0%, rgba(245, 158, 11, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    "></div>

    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem; position: relative; z-index: 1;">
        <div>
            {{-- Status & Tenant Badges --}}
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
                <span style="
                    display: inline-flex;
                    align-items: center;
                    gap: 0.4rem;
                    padding: 0.25rem 0.65rem;
                    border-radius: 9999px;
                    background: rgba(245, 158, 11, 0.15);
                    border: 1px solid rgba(245, 158, 11, 0.3);
                    color: #fbbf24;
                    font-size: 0.75rem;
                    font-weight: 700;
                    letter-spacing: 0.04em;
                    text-transform: uppercase;
                ">
                    <x-filament::icon icon="heroicon-m-building-office-2" style="width: 14px; height: 14px;" />
                    {{ $this->tenantIdentifier }}
                </span>

                <span style="color: #64748b;">&bull;</span>

                @if($this->isYearClosed)
                    <span style="
                        display: inline-flex;
                        align-items: center;
                        gap: 0.4rem;
                        padding: 0.25rem 0.65rem;
                        border-radius: 9999px;
                        background: rgba(244, 63, 94, 0.15);
                        border: 1px solid rgba(244, 63, 94, 0.3);
                        color: #f43f5e;
                        font-size: 0.75rem;
                        font-weight: 700;
                        letter-spacing: 0.04em;
                    ">
                        <x-filament::icon icon="heroicon-m-lock-closed" style="width: 14px; height: 14px;" />
                        Fiscal Year {{ $this->currentYear }}: Closed &amp; Locked
                    </span>
                @else
                    <span style="
                        display: inline-flex;
                        align-items: center;
                        gap: 0.45rem;
                        padding: 0.25rem 0.65rem;
                        border-radius: 9999px;
                        background: rgba(16, 185, 129, 0.15);
                        border: 1px solid rgba(16, 185, 129, 0.3);
                        color: #34d399;
                        font-size: 0.75rem;
                        font-weight: 700;
                        letter-spacing: 0.04em;
                    ">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #34d399; box-shadow: 0 0 8px #34d399;"></span>
                        Fiscal Year {{ $this->currentYear }}: Open for Posting
                    </span>
                @endif
            </div>

            <h1 style="font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 0.35rem 0; color: #ffffff;">
                Welcome back, <span style="color: #fbbf24;">{{ $this->adminName }}</span>
            </h1>
            <p style="font-size: 0.875rem; color: #94a3b8; margin: 0;">
                Executive financial controls, real-time double-entry balances, and fiscal planning overview.
            </p>
        </div>

        {{-- Action Bar --}}
        <div style="display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: center;">
            <a 
                href="{{ url('/admin/journal-entries/create') }}" 
                style="
                    display: inline-flex;
                    align-items: center;
                    gap: 0.45rem;
                    background: #f59e0b;
                    color: #0f172a;
                    font-weight: 700;
                    font-size: 0.8125rem;
                    padding: 0.55rem 1rem;
                    border-radius: 0.5rem;
                    text-decoration: none;
                    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
                    transition: all 0.15s ease;
                "
                onmouseover="this.style.background='#d97706'"
                onmouseout="this.style.background='#f59e0b'"
            >
                <x-filament::icon icon="heroicon-m-plus" style="width: 16px; height: 16px;" />
                <span>New Journal Entry</span>
            </a>

            <a 
                href="{{ url('/admin/reports/trial-balance') }}" 
                style="
                    display: inline-flex;
                    align-items: center;
                    gap: 0.45rem;
                    background: rgba(255, 255, 255, 0.08);
                    color: #e2e8f0;
                    border: 1px solid rgba(255, 255, 255, 0.15);
                    font-weight: 600;
                    font-size: 0.8125rem;
                    padding: 0.55rem 0.9rem;
                    border-radius: 0.5rem;
                    text-decoration: none;
                    transition: all 0.15s ease;
                "
                onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'"
                onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'"
            >
                <x-filament::icon icon="heroicon-m-scale" style="width: 16px; height: 16px;" />
                <span>Trial Balance</span>
            </a>

            <a 
                href="{{ url('/admin/reports/budget-vs-actual') }}" 
                style="
                    display: inline-flex;
                    align-items: center;
                    gap: 0.45rem;
                    background: rgba(255, 255, 255, 0.08);
                    color: #e2e8f0;
                    border: 1px solid rgba(255, 255, 255, 0.15);
                    font-weight: 600;
                    font-size: 0.8125rem;
                    padding: 0.55rem 0.9rem;
                    border-radius: 0.5rem;
                    text-decoration: none;
                    transition: all 0.15s ease;
                "
                onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'"
                onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'"
            >
                <x-filament::icon icon="heroicon-m-presentation-chart-line" style="width: 16px; height: 16px;" />
                <span>Budgets vs. Actuals</span>
            </a>

            <a 
                href="{{ url('/admin/financial-closing') }}" 
                style="
                    display: inline-flex;
                    align-items: center;
                    gap: 0.45rem;
                    background: rgba(255, 255, 255, 0.08);
                    color: #e2e8f0;
                    border: 1px solid rgba(255, 255, 255, 0.15);
                    font-weight: 600;
                    font-size: 0.8125rem;
                    padding: 0.55rem 0.9rem;
                    border-radius: 0.5rem;
                    text-decoration: none;
                    transition: all 0.15s ease;
                "
                onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'"
                onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'"
            >
                <x-filament::icon icon="heroicon-m-lock-closed" style="width: 16px; height: 16px;" />
                <span>Year Closing</span>
            </a>
        </div>
    </div>
</div>
