@extends('layouts.app')

@section('title', 'Branch Directory')

@section('page_subtitle', '')

@push('head')
    
    
    
    <link rel="stylesheet" href="{{ asset('css/store.css') }}?v={{ filemtime(public_path('css/store.css')) }}">

@endpush

@section('content')

@php
    
    
    $currentProvinceFilterId = (string) ($filters['province_id'] ?? '');
    
    
    $currentProvinceFilter = $provinces->firstWhere('id', (int) $currentProvinceFilterId);
    
    
    $statusFilterValue = (string) ($filters['status'] ?? '');
    
    
    $statusFilterLabels = [
        
        'active' => 'Active',
        
        'inactive' => 'Inactive',
        
        'under_maintenance' => 'Under maintenance',
    
    ];
    
    
    $currentStatusLabel = $statusFilterValue !== ''
        
        ? ($statusFilterLabels[$statusFilterValue] ?? $statusFilterValue)
        
        : 'All statuses';

    
    
    $provinceOptions = collect([['value' => '', 'label' => 'All provinces']])
        
        ->merge($provinces->map(function ($province) {
            
            
            $label = \App\Support\EnglishPlaceNames::provinceByCode($province->code) ?: $province->name;
            
            return ['value' => $province->id, 'label' => $label];
        
        }))
        
        ->all();
    
    
    $provinceSelectedLabel = $currentProvinceFilter
        
        ? (\App\Support\EnglishPlaceNames::provinceByCode($currentProvinceFilter->code) ?: $currentProvinceFilter->name)
        
        : 'All provinces';

    
    
    $statusOptions = collect([['value' => '', 'label' => 'All statuses']])
        
        ->merge(collect($statuses)->map(function ($status) use ($statusFilterLabels) {
            
            
            return ['value' => $status, 'label' => $statusFilterLabels[$status] ?? $status];
        
        }))
        
        ->all();

@endphp

<style>
    
    @keyframes brFadeDown {
        
        from { opacity: 0; transform: translateY(-20px); }
        
        to { opacity: 1; transform: translateY(0); }
    
    }

    
    @keyframes brFadeUp {
        
        from { opacity: 0; transform: translateY(20px); }
        
        to { opacity: 1; transform: translateY(0); }
    
    }

    
    .br-shell { padding: 0.5rem; display: grid; gap: 2rem; }
    
    .br-header { animation: brFadeDown 0.5s ease both; }
    
    .br-title { margin: 0 0 0.5rem; font-size: 2.25rem; font-weight: 700; line-height: 1.15; color: var(--ink); }
    
    .br-subtitle { margin: 0; font-size: 1rem; font-weight: 400; line-height: 1.6; color: var(--muted); }

    
    .br-search-card { background: var(--glass-surface-strong); border: 1px solid var(--glass-border); border-radius: 1rem; padding: 1.5rem; box-shadow: var(--glass-shadow); animation: brFadeUp 0.5s ease both; position: relative; z-index: 5; }
    
    .br-grid { position: relative; z-index: 1; }
    
    .br-search-row { display: flex; flex-wrap: nowrap; align-items: flex-end; gap: 1rem; }

    
    .br-search-input-wrap { position: relative; flex: 1 1 420px; min-width: 260px; }
    
    .br-search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--placeholder); pointer-events: none; }
    
    .br-search-icon svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }

    
    .br-input, .br-select { width: 100%; border: 2px solid var(--line); border-radius: 0.85rem; padding: 0.75rem 1rem; outline: none; transition: border-color .2s ease, box-shadow .2s ease; background: var(--surface-soft); color: var(--ink); font-weight: 600; }
    .br-input::placeholder { color: var(--placeholder); font-weight: 500; }
    
    .br-input { padding-left: 2.75rem; padding-right: 1rem; }
    
    .br-input-plain { padding-left: 1rem; }
    
    .br-input:focus, .br-select:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.15); }

    
    .br-filters { display: flex; align-items: flex-end; gap: 1rem; }
    
    .br-filter-block { min-width: 180px; }
    
    .br-field-label { margin: 0 0 0.5rem; font-size: 0.85rem; font-weight: 800; color: var(--muted); }
    
    .br-select-wrap { position: relative; }
    
    .br-select { appearance: none; padding-right: 2.6rem; }
    
    .br-chevron { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--placeholder); pointer-events: none; }
    
    .br-chevron svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }

    
    .br-reset { border: 2px solid var(--line); background: var(--glass-surface-strong); color: var(--ink); border-radius: 0.85rem; padding: 0.75rem 1rem; font-weight: 800; cursor: pointer; transition: transform .15s ease, border-color .2s ease, box-shadow .2s ease; display: inline-flex; align-items: center; justify-content: center; min-width: 140px; }
    
    .br-reset:hover { border-color: rgba(var(--primary-rgb), 0.35); transform: scale(1.02); box-shadow: 0 10px 18px -16px rgba(15, 23, 42, 0.35); }
    
    .br-reset:active { transform: scale(0.98); }

    
    .br-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.5rem; }
    
    .br-card { background: var(--glass-surface-strong); border: 1px solid var(--glass-border); border-radius: 1rem; overflow: hidden; box-shadow: var(--glass-shadow); transition: transform .2s ease, box-shadow .2s ease; animation: brFadeUp .5s ease both; animation-delay: var(--br-delay, 0s); }
    
    .br-card:hover { transform: translateY(-5px); box-shadow: 0 18px 30px -12px rgba(15, 23, 42, 0.16), 0 10px 15px -10px rgba(15, 23, 42, 0.12); }

    
    .br-card-main { display: block; color: inherit; text-decoration: none; }

    
    .br-card-head {
        
        background: #2563eb;
        
        padding: 1.5rem;
        
        color: #ffffff;
        
        position: relative;
        
        overflow: hidden;
        
        min-height: 150px;
        
        display: flex;
        
        flex-direction: column;
        
        justify-content: space-between;
    
    }
    
    .br-card-head::before {
        
        content: '';
        
        position: absolute;
        
        top: -4rem;
        
        right: -4rem;
        
        width: 8rem;
        
        height: 8rem;
        
        background: rgba(255, 255, 255, 0.10);
        
        border-radius: 999px;
    
    }
    
    .br-card-head::after {
        
        content: '';
        
        position: absolute;
        
        bottom: -3rem;
        
        left: -3rem;
        
        width: 6rem;
        
        height: 6rem;
        
        background: rgba(0, 0, 0, 0.10);
        
        border-radius: 999px;
    
    }
    
    .br-card-head > * {
        
        position: relative;
        
        z-index: 1;
    
    }
    
    .br-card-head-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.75rem; }
    
    .br-card-name { margin: 0 0 0.25rem; font-size: 1.15rem; font-weight: 800; line-height: 1.25; }
    
    .br-card-code { margin: 0; font-size: 0.85rem; font-weight: 600; color: rgba(219, 234, 254, 0.95); }

    
    .br-status { display: inline-flex; align-items: center; justify-content: center; padding: 0.35rem 0.75rem; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 900; white-space: nowrap; }
    
    .br-status--active {
        
        background: rgba(255, 255, 255, 0.2);
        
        border: 1px solid rgba(255, 255, 255, 0.24);
        
        color: #ffffff;
    
    }
    
    .br-status--inactive { background: rgba(148, 163, 184, 0.28); border: 1px solid rgba(148, 163, 184, 0.35); color: var(--ink); }
    
    .br-status--attention { background: rgba(245, 158, 11, 0.95); }
    
    .br-status-wrap {
        
        display: flex;
        
        flex-direction: column;
        
        align-items: flex-end;
        
        gap: 0.45rem;
        
        text-align: right;
    
    }
    
    .br-status-tag {
        
        display: inline-flex;
        
        align-items: center;
        
        gap: 0.35rem;
        
        padding: 0.35rem 0.75rem;
        
        border-radius: 999px;
        
        background: #facc15;
        
        color: #7a4e00;
        
        font-size: 0.75rem;
        
        font-weight: 700;
        
        box-shadow: 0 10px 18px rgba(250, 204, 21, 0.35);
        
        white-space: nowrap;
    
    }
    
    .br-status-tag svg {
        
        width: 14px;
        
        height: 14px;
        
        fill: currentColor;
        
        flex: none;
    
    }

    
    .br-manager { margin: 0; font-size: 0.85rem; font-weight: 600; color: rgba(219, 234, 254, 0.95); }

    
    .br-card-body { padding: 1.5rem 1.5rem 2.5rem; display: flex; flex-direction: column; }
    
    .br-card-body > .br-info + .br-info { margin-top: 0.75rem; }
    
    .br-info { display: flex; align-items: center; gap: 0.75rem; color: var(--muted); font-size: 0.875rem; line-height: 1.25rem; }
    
    .br-info-icon { width: 20px; height: 20px; flex: none; display: inline-flex; align-items: center; justify-content: center; line-height: 0; }
    
    .br-info-icon svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }
    
    .br-info-icon--pin { color: #3b82f6; }
    
    .br-info-icon--users { color: #10b981; }
    
    .br-info-icon--clock { color: #a855f7; }
    
    .br-info-icon--file { color: #f97316; }

    
    .br-info-text { font: inherit; color: inherit; min-width: 0; }
    
    .br-info-text.br-line { flex: 1; }
    
    .br-info-text.br-line > bdi { white-space: normal; }

    
    .br-brochure-links { display: inline-flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; }
    
    .br-brochure-link { color: var(--primary); font-weight: 500; text-decoration: none; font-size: inherit; line-height: 1.25rem; display: inline; }
    
    .br-brochure-link--download { color: #0f766e; }
    
    .br-brochure-link--download:hover { color: #0f766e; }
    
    .br-brochure-separator { color: var(--line); font-weight: 700; }
    
    .br-brochure-link:hover { color: var(--primary-deep); }
    
    .br-brochure-empty { color: var(--placeholder); font-weight: 500; }

    
    @supports (gap: 1rem) {
        
        .br-card-body { gap: 0.75rem; }
        
        .br-card-body > .br-info + .br-info { margin-top: 0; }
    
    }

    
    .br-footer { padding: 1rem; background: var(--surface-soft); border-top: 1px solid var(--line); }
    
    .br-footer-row { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.55rem; }
    
    .br-footer-row--double { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    
    .br-footer-row--single { grid-template-columns: minmax(0, 1fr); }

    
    .br-footer-btn {
        
        width: 100%;
        
        border: none;
        
        background: transparent;
        
        padding: 0.7rem 0.85rem;
        
        border-radius: 0.75rem;
        
        display: inline-flex;
        
        align-items: center;
        
        justify-content: center;
        
        gap: 0.5rem;
        
        font-weight: 800;
        
        cursor: pointer;
        
        transition: transform .15s ease, background-color .2s ease, box-shadow .2s ease;
        
        text-decoration: none;
    
    }

    
    .br-footer-btn svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }
    
    .br-footer-btn:hover { transform: scale(1.02); }
    
    .br-footer-btn:active { transform: scale(0.98); }

    
    .br-footer-btn--details {
        
        color: var(--primary);
        
        background: rgba(var(--primary-rgb), 0.12);
    
    }
    
    .br-footer-btn--details:hover { background: rgba(var(--primary-rgb), 0.18); box-shadow: 0 10px 16px -14px rgba(37, 99, 235, 0.45); }

    
    .br-footer-btn--edit {
        
        color: var(--success);
        
        background: rgba(34, 197, 94, 0.14);
    
    }
    
    .br-footer-btn--edit:hover { background: rgba(34, 197, 94, 0.20); box-shadow: 0 10px 16px -14px rgba(16, 185, 129, 0.45); }

    
    .br-footer-btn--delete {
        
        color: var(--danger);
        
        background: rgba(239, 68, 68, 0.12);
    
    }
    
    .br-footer-btn--delete:hover { background: rgba(239, 68, 68, 0.18); box-shadow: 0 10px 16px -14px rgba(220, 38, 38, 0.45); }

    
    .br-empty { padding: 2.25rem 1.5rem; background: var(--glass-surface-strong); border: 1px solid var(--glass-border); border-radius: 1rem; box-shadow: var(--glass-shadow); color: var(--muted); font-weight: 600; text-align: center; animation: brFadeUp .5s ease both; }

    
    @media (max-width: 1100px) {
        
        .br-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    
    }

    
    @media (max-width: 1100px) {
        
        .br-search-row { flex-wrap: wrap; }
        
        .br-search-input-wrap { min-width: 100%; }
        
        .br-filters { width: 100%; flex-wrap: wrap; }
        
        .br-filter-block { flex: 1 1 220px; min-width: 200px; }
        
        .br-reset { width: 100%; }
    
    }

    
    @media (max-width: 820px) {
        
        .br-grid { grid-template-columns: 1fr; }
    
    }

    
    @media (prefers-reduced-motion: reduce) {
        
        .br-header, .br-search-card { animation: none !important; }
        
        .br-input, .br-select, .br-reset, .br-card, .br-action { transition: none !important; }
        
        .br-reset:hover, .br-card:hover, .br-action:hover, .br-action:active { transform: none !important; }
        
        .br-empty { animation: none !important; }
    
    }

</style>

<div class="br-shell">
    
    
    
    <div class="br-header">
        
        <h1 class="br-title">Branch Directory</h1>
        
        <p class="br-subtitle">Manage and monitor all branches</p>
    
    </div>

    
    
    
    <div class="br-search-card" style="animation-delay: 0.1s;">
        
        
        
        <form id="search-form" action="{{ route('stores.index', [], false) }}" method="GET" novalidate>
            
            <div class="br-search-row">
                
                <div class="br-search-input-wrap">
                    
                    
                    
                    <span class="br-search-icon" aria-hidden="true">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <circle cx="11" cy="11" r="8"></circle>
                            
                            <path d="m21 21-4.3-4.3"></path>
                        
                        </svg>
                    
                    </span>
                    
                    
                    
                    <input
                        
                        type="text"
                        
                        name="name"
                        
                        value="{{ $filters['name'] }}"
                        
                        class="br-input"
                        
                        placeholder="Search for a branch..."
                        
                        aria-label="Search for a branch"
                        
                        autocomplete="off"
                    
                    >
                
                </div>

                
                
                
                <div class="br-filters">
                    
                    <div class="br-filter-block">
                        
                        <div class="br-field-label">Province</div>
                        
                        
                        
                        @include('partials.custom-select-native', [
                            
                            'name' => 'province_id',
                            
                            'options' => $provinceOptions,
                            
                            'selectedValue' => $currentProvinceFilterId,
                            
                            'selectedLabel' => $provinceSelectedLabel,
                            
                            'wrapperClass' => 'custom-select',
                            
                            'selectClass' => 'br-select custom-select-native',
                            
                            'triggerClass' => 'br-select custom-select-trigger',
                            
                            'selectAttributes' => 'aria-label="Province filter"',
                        
                        ])
                    
                    </div>

                    
                    <div class="br-filter-block">
                        
                        <div class="br-field-label">Status</div>
                        
                        
                        
                        @include('partials.custom-select-native', [
                            
                            'name' => 'status',
                            
                            'options' => $statusOptions,
                            
                            'selectedValue' => $statusFilterValue,
                            
                            'selectedLabel' => $currentStatusLabel,
                            
                            'wrapperClass' => 'custom-select',
                            
                            'selectClass' => 'br-select custom-select-native',
                            
                            'triggerClass' => 'br-select custom-select-trigger',
                            
                            'selectAttributes' => 'aria-label="Status filter"',
                        
                        ])
                    
                    </div>

                    
                    <div class="br-filter-block">
                        
                        <div class="br-field-label">Phone</div>
                        
                        
                        
                        <input
                            
                            type="text"
                            
                            name="phone"
                            
                            value="{{ $filters['phone'] }}"
                            
                            class="br-input br-input-plain"
                            
                            placeholder="Phone number"
                            
                            aria-label="Phone filter"
                            
                            autocomplete="off"
                        
                        >
                    
                    </div>

                    
                    <div class="br-filter-block" style="align-self: end;">
                        
                        
                        
                        <button type="button" id="reset-filters" class="br-reset">Reset</button>
                    
                    </div>
                
                </div>
            
            </div>
        
        </form>
    
    </div>

    
    
    
    <div id="stores-grid">
        
        @include('stores.partials.grid', ['stores' => $stores, 'provinceNameToEnglish' => $provinceNameToEnglish ?? []])
    
    </div>

</div>

<script>
    
    (function () {
        
        
        if (window.__storeSearchWired) {
            
            return;
        
        }
        
        window.__storeSearchWired = true;
    
    
    let timerId = null;
    
    
    let currentRequest = null;
    
    function refreshStores() {
        
        
        if (currentRequest) {
            
            currentRequest.abort();
        
        }

        
        
        currentRequest = $.get("{{ route('stores.index', [], false) }}", $('#search-form').serialize())
            
            .done(function (html) {
                
                $('#stores-grid').html(html);
            
            })
            
            .always(function () {
                
                
                currentRequest = null;
            
            });
    
    }

    
    
    $('#search-form').on('input change', 'input, select', function () {
        
        clearTimeout(timerId);
        
        timerId = setTimeout(refreshStores, 400);
    
    });

    
    
    $('#reset-filters').on('click', function () {
        
        $('#search-form').find('input[type="text"]').val('');
        
        $('#search-form').find('select').prop('selectedIndex', 0);
        
        refreshStores();
    
    });

    
    })();

</script>

@endsection

@push('scripts')
    
    
    
    <script src="{{ asset('js/store.js') }}?v={{ filemtime(public_path('js/store.js')) }}"></script>

@endpush
