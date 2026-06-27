@extends('layouts.app')

@section('title', 'Dashboard')

@section('page_subtitle', '')

@section('content')

<style>
    
    @keyframes dashFadeDown {
        
        from {
            
            opacity: 0;
            
            transform: translateY(-20px);
        
        }

        
        to {
            
            opacity: 1;
            
            transform: translateY(0);
        
        }
    
    }

    
    @keyframes dashFadeUp {
        
        from {
            
            opacity: 0;
            
            transform: translateY(20px);
        
        }

        
        to {
            
            opacity: 1;
            
            transform: translateY(0);
        
        }
    
    }

    
    @keyframes dashSlideIn {
        
        from {
            
            opacity: 0;
            
            transform: translateX(-20px);
        
        }

        
        to {
            
            opacity: 1;
            
            transform: translateX(0);
        
        }
    
    }

    
    .dashboard-shell {
        
        display: block;
        
        max-width: 100%;
    
    }

    
    .dashboard-header {
        
        margin-bottom: 2rem;
        
        animation: dashFadeDown 0.5s ease both;
        
        will-change: opacity, transform;
    
    }

    
    .dashboard-title {
        
        margin: 0 0 0.5rem;
        
        font-size: 2.25rem;
        
        font-weight: 700;
        
        line-height: 1.15;
        
        color: var(--ink);
    
    }

    
    .dashboard-subtitle {
        
        margin: 0;
        
        font-size: 1rem;
        
        font-weight: 400;
        
        line-height: 1.6;
        
        color: var(--muted);
    
    }

    
    .dashboard-metrics {
        
        display: grid;
        
        grid-template-columns: repeat(4, minmax(0, 1fr));
        
        gap: 1.5rem;
        
        margin-bottom: 2rem;
    
    }

    
    .dashboard-metric {
        
        background: var(--glass-surface-strong);
        
        border: 1px solid var(--glass-border);
        
        border-radius: 1rem;
        
        padding: 1.5rem;
        
        box-shadow: var(--glass-shadow);
        
        transition: box-shadow 0.2s ease, transform 0.2s ease;
        
        overflow: hidden;
        
        animation: dashFadeUp 0.5s ease both;
        
        will-change: opacity, transform;
    
    }

    
    .dashboard-metrics .dashboard-metric:nth-child(1) {
        
        animation-delay: 0.05s;
    
    }

    
    .dashboard-metrics .dashboard-metric:nth-child(2) {
        
        animation-delay: 0.15s;
    
    }

    
    .dashboard-metrics .dashboard-metric:nth-child(3) {
        
        animation-delay: 0.25s;
    
    }

    
    .dashboard-metrics .dashboard-metric:nth-child(4) {
        
        animation-delay: 0.35s;
    
    }

    
    .dashboard-metric:hover {
        
        box-shadow: var(--shadow-lg);
        
        transform: translateY(-5px);
    
    }

    
    .dashboard-metric-head {
        
        display: flex;
        
        align-items: flex-start;
        
        justify-content: space-between;
        
        gap: 0.9rem;
        
        margin-bottom: 1rem;
    
    }

    
    .dashboard-metric-icon {
        
        width: 48px;
        
        height: 48px;
        
        border-radius: 0.75rem;
        
        display: grid;
        
        place-items: center;
        
        background: var(--surface-soft);
        
        border: 1px solid var(--line);
        
        color: var(--ink);
        
        box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.08);
        
        flex: none;
    
    }

    
    .dashboard-metric-icon svg {
        
        width: 24px;
        
        height: 24px;
        
        display: block;
        
        fill: none;
        
        stroke: currentColor;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
    
    }

    
    .dashboard-metric.metric--branches .dashboard-metric-icon {
        
        color: #2563eb;
    
    }

    
    .dashboard-metric.metric--employees .dashboard-metric-icon {
        
        color: #059669;
    
    }

    
    .dashboard-metric.metric--products .dashboard-metric-icon {
        
        color: #7c3aed;
    
    }

    
    .dashboard-metric.metric--growth .dashboard-metric-icon {
        
        color: #ea580c;
    
    }

    
    .dashboard-metric-chip {
        
        display: inline-flex;
        
        align-items: center;
        
        gap: 0.25rem;
        
        padding: 0.25rem 0.5rem;
        
        border-radius: 0.5rem;
        
        font-size: 0.75rem;
        
        font-weight: 700;
        
        white-space: nowrap;
    
    }

    
    .dashboard-metric-chip svg {
        
        width: 12px;
        
        height: 12px;
        
        display: block;
        
        fill: none;
        
        stroke: currentColor;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
    
    }

    
    .dashboard-metric-chip--up {
        
        background: rgba(34, 197, 94, 0.18);
        
        color: var(--success);
    
    }

    
    .dashboard-metric-chip--down {
        
        background: rgba(239, 68, 68, 0.16);
        
        color: var(--danger);
    
    }

    
    .dashboard-metric-label {
        
        margin: 0 0 0.25rem;
        
        font-size: 0.875rem;
        
        font-weight: 400;
        
        color: var(--muted);
    
    }

    
    .dashboard-metric-value {
        
        margin: 0;
        
        font-size: 1.875rem;
        
        font-weight: 700;
        
        line-height: 1.15;
        
        color: var(--ink);
    
    }

    
    .dashboard-table-card {
        
        background: var(--glass-surface-strong);
        
        border: 1px solid var(--glass-border);
        
        border-radius: 1rem;
        
        box-shadow: var(--glass-shadow);
        
        overflow: hidden;
        
        animation: dashFadeUp 0.5s ease both;
        
        animation-delay: 0.45s;
        
        will-change: opacity, transform;
    
    }

    
    .dashboard-table-card-header {
        
        padding: 1.5rem;
        
        border-bottom: 1px solid var(--line);
    
    }

    
    .dashboard-table-card-title {
        
        margin: 0;
        
        font-size: 1.5rem;
        
        font-weight: 700;
        
        color: var(--ink);
    
    }

    
    .dashboard-table-wrap {
        
        overflow-x: auto;
    
    }

    
    .dashboard-table {
        
        width: 100%;
        
        border-collapse: separate;
        
        border-spacing: 0;
        
        min-width: 640px;
    
    }

    
    .dashboard-table thead tr {
        
        background: var(--surface-soft);
    
    }

    
    .dashboard-table th {
        
        padding: 1rem 1.5rem;
        
        text-align: left;
        
        font-size: 0.875rem;
        
        font-weight: 700;
        
        color: var(--muted);
        
        white-space: nowrap;
    
    }

    
    .dashboard-table td {
        
        padding: 1rem 1.5rem;
        
        font-size: 0.95rem;
        
        color: var(--muted);
        
        border-top: 1px solid var(--line);
        
        vertical-align: middle;
    
    }

    
    .dashboard-table tbody tr:hover {
        
        background: var(--surface-soft);
    
    }

    
    .dashboard-table tbody tr {
        
        animation: dashSlideIn 0.45s ease both;
        
        animation-delay: var(--row-delay, 0.55s);
        
        will-change: opacity, transform;
    
    }

    
    .dashboard-table td.table-branch-name {
        
        color: var(--ink);
        
        font-weight: 500;
    
    }

    
    .dashboard-status-pill {
        
        display: inline-flex;
        
        align-items: center;
        
        padding: 0.25rem 0.75rem;
        
        border-radius: 999px;
        
        font-size: 0.875rem;
        
        font-weight: 500;
        
        white-space: nowrap;
    
    }

    
    .dashboard-status-pill--active {
        
        background: rgba(34, 197, 94, 0.18);
        
        color: var(--success);
    
    }

    
    .dashboard-status-pill--pending {
        
        background: rgba(245, 158, 11, 0.18);
        
        color: var(--accent);
    
    }

    
    @media (max-width: 1100px) {
        
        .dashboard-metrics {
            
            grid-template-columns: repeat(2, minmax(0, 1fr));
        
        }
    
    }

    
    @media (max-width: 640px) {
        
        .dashboard-title {
            
            font-size: 2rem;
        
        }

        
        .dashboard-metrics {
            
            grid-template-columns: 1fr;
        
        }

        
        .dashboard-table {
            
            min-width: 560px;
        
        }

    
    }

    
    @media (prefers-reduced-motion: reduce) {
        
        .dashboard-header,
        
        .dashboard-metric,
        
        .dashboard-table-card,
        
        .dashboard-table tbody tr {
            
            animation: none !important;
            
            transform: none !important;
        
        }

        
        .dashboard-metric {
            
            transition: none !important;
        
        }
    
    }

</style>

<div class="dashboard-shell">
    
    <div class="dashboard-header">
        
        <h1 class="dashboard-title">Dashboard</h1>
        
        <p class="dashboard-subtitle">Welcome to the Branch Management System</p>
    
    </div>

    
    <div class="dashboard-metrics">
        
        <div class="dashboard-metric metric--branches">
            
            <div class="dashboard-metric-head">
                
                <div class="dashboard-metric-icon" aria-hidden="true">
                    
                    <svg viewBox="0 0 24 24">
                        
                        <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path>
                        
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                        
                        <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path>
                        
                        <path d="M2 7h20"></path>
                        
                        <path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"></path>
                    
                    </svg>
                
                </div>
                
                <div class="dashboard-metric-chip dashboard-metric-chip--up" title="Change indicator">
                    
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        
                        <path d="M12 19V5"></path>
                        
                        <path d="M5 12l7-7 7 7"></path>
                    
                    </svg>
                    
                    +{{ $activeRatio }}%
                
                </div>
            
            </div>
            
            <p class="dashboard-metric-label">Total Branches</p>
            
            <p class="dashboard-metric-value">{{ number_format($storeCount) }}</p>
        
        </div>

        
        <div class="dashboard-metric metric--employees">
            
            <div class="dashboard-metric-head">
                
                <div class="dashboard-metric-icon" aria-hidden="true">
                    
                    <svg viewBox="0 0 24 24">
                        
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        
                        <circle cx="9" cy="7" r="4"></circle>
                        
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    
                    </svg>
                
                </div>
                
                <div class="dashboard-metric-chip dashboard-metric-chip--up" title="Change indicator">
                    
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        
                        <path d="M12 19V5"></path>
                        
                        <path d="M5 12l7-7 7 7"></path>
                    
                    </svg>
                    
                    +{{ $employeeActiveRatio }}%
                
                </div>
            
            </div>
            
            <p class="dashboard-metric-label">Active Employees</p>
            
            <p class="dashboard-metric-value">{{ number_format($employeeCount) }}</p>
        
        </div>

        
        <div class="dashboard-metric metric--products">
            
            <div class="dashboard-metric-head">
                
                <div class="dashboard-metric-icon" aria-hidden="true">
                    
                    <svg viewBox="0 0 24 24">
                        
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"></path>
                        
                        <path d="M3.3 7.3L12 12l8.7-4.7"></path>
                        
                        <path d="M12 22V12"></path>
                    
                    </svg>
                
                </div>
                
                <div class="dashboard-metric-chip dashboard-metric-chip--up" title="Change indicator">
                    
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        
                        <path d="M12 19V5"></path>
                        
                        <path d="M5 12l7-7 7 7"></path>
                    
                    </svg>
                    
                    +{{ $productCoveragePercent }}%
                
                </div>
            
            </div>
            
            <p class="dashboard-metric-label">Linked Products</p>
            
            <p class="dashboard-metric-value">{{ number_format($linkedProductsCount) }}</p>
        
        </div>

        
        @php
            $growthRate = $storeCount > 0 ? round(($activeCount / $storeCount) * 100, 1) : 0;
            $inactiveRate = $storeCount > 0 ? max(0, round(100 - $growthRate, 1)) : 0;
            $growthDownIndicator = $inactiveRate;
        @endphp
        
        <div class="dashboard-metric metric--growth">
            
            <div class="dashboard-metric-head">
                
                <div class="dashboard-metric-icon" aria-hidden="true">
                    
                    <svg viewBox="0 0 24 24">
                        
                        <path d="M3 17l6-6 4 4 7-7"></path>
                        
                        <path d="M14 8h6v6"></path>
                    
                    </svg>
                
                </div>
                
                <div class="dashboard-metric-chip dashboard-metric-chip--down" title="Change indicator">
                    
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        
                        <path d="M12 5v14"></path>
                        
                        <path d="M19 12l-7 7-7-7"></path>
                    
                    </svg>
                    
                    -{{ $growthDownIndicator }}%
                
                </div>
            
            </div>
            
            <p class="dashboard-metric-label">Growth Rate</p>
            
            <p class="dashboard-metric-value">{{ $growthRate }}%</p>
        
        </div>
    
    </div>

    
    <div class="dashboard-table-card">
        
        <div class="dashboard-table-card-header">
            
            <h2 class="dashboard-table-card-title">Recent Branches</h2>
        
        </div>
        
        <div class="dashboard-table-wrap">
            
            <table class="dashboard-table" aria-label="Recent branches">
                
                <thead>
                    
                    <tr>
                        
                        <th>Branch Name</th>
                        
                        <th>Code</th>
                        
                        <th>Status</th>
                        
                        <th>Employees</th>
                    
                    </tr>
                
                </thead>
                
                <tbody>
                    
                    @forelse(($recentStores ?? []) as $store)
                        
                        @php
                            $statusRaw = (string) ($store['status'] ?? '');
                            $isActive = $statusRaw === 'active';
                            $statusLabel = $isActive ? 'Active' : 'Under Review';
                            $branchCode = (string) ($store['branch_code'] ?? '');
                            $branchName = \App\Support\EnglishPlaceNames::branchDisplayName($branchCode, $store['name'] ?? '');
                        @endphp
                        
                        <tr style="--row-delay: {{ 0.55 + ($loop->index * 0.05) }}s">
                            
                            <td class="table-branch-name">{{ $branchName }}</td>
                            
                            <td>{{ $branchCode }}</td>
                            
                            <td>
                                
                                <span class="dashboard-status-pill {{ $isActive ? 'dashboard-status-pill--active' : 'dashboard-status-pill--pending' }}">
                                    
                                    {{ $statusLabel }}
                                
                                </span>
                            
                            </td>
                            
                            <td>{{ number_format((int) ($store['employees'] ?? 0)) }}</td>
                        
                        </tr>
                    
                    @empty
                        
                        <tr>
                            
                            <td colspan="4" style="padding: 1.25rem 1.5rem; color: #64748b;">
                                
                                No branches found.
                            
                            </td>
                        
                        </tr>
                    
                    @endforelse
                
                </tbody>
            
            </table>
        
        </div>
    
    </div>

</div>

<script>
    
    window.addEventListener('pageshow', function (event) {
        
        if (event.persisted) {
            
            window.location.reload();
        
        }
    
    });

</script>

@endsection
