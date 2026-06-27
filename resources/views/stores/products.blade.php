@extends('layouts.app')

@section('title', 'Link Products')

@section('page_subtitle', '')

@section('content')

@php
    
    
    $selectedProducts = old('product_ids', $store->products->pluck('id')->all());
    
    
    $selectedProducts = array_values(array_unique(array_map('intval', is_array($selectedProducts) ? $selectedProducts : [])));
    
    
    $selectedSet = array_fill_keys($selectedProducts, true);
    
    
    $currentUser = auth()->user();
    
    $isStoreManager = $currentUser?->hasRole('store_manager') || $currentUser?->hasRole('store_employee');
    
    $canSwitchBranch = $currentUser?->hasRole('admin') ?? false;
    
    
    $storeLabel = \App\Support\EnglishPlaceNames::branchDisplayName($store->branch_code, $store->name);
    
    
    $storeNameById = $stores
        
        ->mapWithKeys(function ($item) {
            
            return [$item->id => \App\Support\EnglishPlaceNames::branchDisplayName($item->branch_code, $item->name)];
        
        })
        
        ->all();
    
    $limitedStoreLabel = $storeLabel;
    
    $branchTitle = $canSwitchBranch ? 'Choose Branch' : 'Branch';

    
    
    $storeOptions = $stores
        
        ->map(function ($item) {
            
            $itemLabel = \App\Support\EnglishPlaceNames::branchDisplayName($item->branch_code, $item->name);
            
            return ['value' => $item->id, 'label' => $itemLabel];
        
        })
        
        ->all();

    
    $usdToSypRate = isset($usdToSypRate) && is_numeric($usdToSypRate) ? (float) $usdToSypRate : null;
    
    $usdToSypRate = $usdToSypRate && $usdToSypRate > 0 ? $usdToSypRate : null;

@endphp

<style>
    
    @keyframes lpFadeDown {
        
        from {
            
            opacity: 0;
            
            transform: translateY(-20px);
        
        }

        
        to {
            
            opacity: 1;
            
            transform: translateY(0);
        
        }
    
    }

    
    @keyframes lpFadeLeft {
        
        from {
            
            opacity: 0;
            
            transform: translateX(-20px);
        
        }

        
        to {
            
            opacity: 1;
            
            transform: translateX(0);
        
        }
    
    }

    
    @keyframes lpFadeRight {
        
        from {
            
            opacity: 0;
            
            transform: translateX(20px);
        
        }

        
        to {
            
            opacity: 1;
            
            transform: translateX(0);
        
        }
    
    }

    
    .lp-shell {
        
        padding: 0.5rem;
        
        display: grid;
        
        gap: 2rem;
    
    }

    
    .lp-header {
        
        animation: lpFadeDown 0.5s ease both;
    
    }

    
    .lp-title {
        
        margin: 0 0 0.5rem;
        
        font-size: 2.25rem;
        
        font-weight: 700;
        
        line-height: 1.15;
        
        color: var(--ink);
    
    }

    
    .lp-subtitle {
        
        margin: 0;
        
        font-size: 1rem;
        
        font-weight: 400;
        
        line-height: 1.6;
        
        color: var(--muted);
    
    }

    
    .lp-grid {
        
        display: grid;
        
        grid-template-columns: 1fr;
        
        gap: 1.5rem;
        
        align-items: start;
        
        width: 100%;
    
    }

    
    .lp-animate-left {
        
        animation: lpFadeLeft 0.5s ease both;
    
    }

    
    .lp-animate-right {
        
        animation: lpFadeRight 0.5s ease both;
    
    }

    
    .lp-card {
        
        background: var(--glass-surface);
        
        border: 1px solid var(--glass-border);
        
        border-radius: 1rem;
        
        padding: 1.5rem;
        
        box-shadow: var(--glass-shadow);
    
    }

    
    .lp-card--list {
        
        padding: 0;
        
        overflow: hidden;
        
        border: 1px solid var(--glass-border);
    
    }

    
    .lp-products-header {
        
        padding: 1.5rem;
        
        border-bottom: 1px solid var(--line);
        
        background: var(--surface-soft);
    
    }

    
    .lp-products-head {
        
        display: flex;
        
        align-items: center;
        
        justify-content: flex-start;
        
        gap: 1rem;
        
        margin-bottom: 1rem;
    
    }

    
    .lp-products-title {
        
        display: flex;
        
        align-items: center;
        
        gap: 0.75rem;
        
        min-width: 0;
    
    }

    
    .lp-products-mark {
        
        width: 40px;
        
        height: 40px;
        
        border-radius: 0.65rem;
        
        display: grid;
        
        place-items: center;
        
        flex: none;
        
        background: #a855f7;
    
    }

    
    .lp-products-mark svg {
        
        width: 20px;
        
        height: 20px;
        
        stroke: #ffffff;
        
        fill: none;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
    
    }

    
    .lp-products-heading {
        
        margin: 0;
        
        font-size: 1.5rem;
        
        font-weight: 700;
        
        color: var(--ink);
        
        line-height: 1.2;
    
    }

    
    .lp-products-count {
        
        font-size: 0.875rem;
        
        color: var(--muted);
    
    }

    
    .lp-search {
        
        position: relative;
        
        margin: 0;
    
    }

    
    .lp-search-icon {
        
        position: absolute;
        
        right: 1rem;
        
        top: 50%;
        
        transform: translateY(-50%);
        
        width: 20px;
        
        height: 20px;
        
        color: var(--placeholder);
        
        pointer-events: none;
    
    }

    
    .lp-search-icon svg {
        
        width: 20px;
        
        height: 20px;
        
        stroke: currentColor;
        
        fill: none;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
        
        display: block;
    
    }

    
    .lp-search-input {
        
        width: 100%;
        
        border: 1px solid var(--line);
        
        border-radius: 0.9rem;
        
        padding: 0.75rem 3rem 0.75rem 1rem;
        
        outline: none;
        
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        
        color: var(--ink);
        
        background: var(--surface-soft);
    
    }
    .lp-search-input::placeholder {
        color: var(--placeholder);
    }

    
    .lp-search-input:focus {
        
        border-color: #a855f7;
        
        box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.15);
    
    }

    
    .lp-products-body {
        
        padding: 1.5rem;
    
    }

    
    .lp-list {
        
        display: grid;
        
        gap: 0.75rem;
        
        max-height: none;
        
        overflow: visible;
        
        padding-right: 0;
    
    }

    
    .lp-item {
        
        width: 100%;
        
        text-align: left;
        
        border: 2px solid var(--line);
        
        border-radius: 0.75rem;
        
        padding: 1rem;
        
        background: var(--glass-surface-strong);
        
        cursor: pointer;
        
        display: flex;
        
        align-items: center;
        
        justify-content: space-between;
        
        gap: 1rem;
        
        transition: border-color 0.2s ease, background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
    
    }
    html.theme-glass .lp-item {
        border-color: var(--glass-border);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
    }

    
    .lp-item-animate {
        
        animation: lpFadeLeft 0.45s ease both;
    
    }

    
    .lp-item:hover {
        
        border-color: #d8b4fe;
        
        transform: scale(1.01);

        box-shadow: var(--shadow-md);
    
    }

    
    .lp-item:active {
        
        transform: scale(0.99);
    
    }

    
    .lp-item.is-selected {
        
        border-color: #a855f7;
        
        background: rgba(168, 85, 247, 0.12);
    
    }

    
    .lp-item-left {
        
        display: flex;
        
        align-items: center;
        
        gap: 1rem;
        
        min-width: 0;
    
    }

    
    .lp-item-icon {
        
        width: 48px;
        
        height: 48px;
        
        border-radius: 0.75rem;
        
        display: grid;
        
        place-items: center;
        
        flex: none;
        
        background: var(--surface-soft);
        
        color: var(--placeholder);
        
        transition: background 0.2s ease, color 0.2s ease;
    
    }

    
    .lp-item.is-selected .lp-item-icon {
        
        background: #a855f7;
        
        color: #ffffff;
    
    }

    
    .lp-item-icon svg {
        
        width: 24px;
        
        height: 24px;
        
        stroke: currentColor;
        
        fill: none;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
        
        display: block;
    
    }

    
    .lp-item-copy {
        
        min-width: 0;
        
        display: grid;
        
        gap: 0.15rem;
    
    }

    
    .lp-item-name {
        
        font-weight: 700;
        
        color: var(--ink);
        
        white-space: nowrap;
        
        overflow: hidden;
        
        text-overflow: ellipsis;
    
    }

    
    .lp-item-meta {
        
        font-size: 0.875rem;
        
        color: var(--muted);
    
    }

    
    .lp-item-branches {
        
        margin-top: 0.2rem;
        
        display: flex;
        
        flex-wrap: wrap;
        
        gap: 0.35rem;
        
        align-items: center;
    
    }

    
    .lp-item-branches-label {
        
        font-size: 0.75rem;
        
        color: var(--placeholder);
        
        font-weight: 600;
    
    }

    
    .lp-chip {
        
        display: inline-flex;
        
        align-items: center;
        
        padding: 0.15rem 0.5rem;
        
        border-radius: 999px;
        
        background: var(--surface-soft);
        
        color: var(--muted);
        
        font-size: 0.75rem;
        
        line-height: 1.2;
        
        white-space: nowrap;
    
    }

    
    .lp-chip--empty {
        
        background: rgba(148, 163, 184, 0.18);
        
        color: var(--placeholder);
    
    }

    
    .lp-item-right {
        
        text-align: right;
        
        display: grid;
        
        gap: 0.15rem;
        
        flex: none;
    
    }

    
    .lp-item-price {
        
        font-weight: 700;
        
        color: var(--ink);
    
    }

    
    .lp-item-stock {
        
        font-size: 0.875rem;
        
        color: var(--muted);
    
    }

    
    .lp-side {
        
        display: grid;
        
        gap: 1.5rem;
    
    }

    
    .lp-side-actions {
        
        display: grid;
        
        gap: 1.5rem;
        
        align-content: start;
    
    }

    
    @media (min-width: 1100px) {
        
        .lp-grid {
            
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            
            gap: 1.5rem;
        
        }

        
        .lp-side {
            
            align-content: start;
        
        }
    
    }

    
    .lp-side-title {
        
        margin: 0 0 1rem;
        
        font-size: 1.25rem;
        
        font-weight: 700;
        
        color: var(--ink);
    
    }

    
    .lp-empty {
        
        text-align: center;
        
        padding: 2rem 1rem;
        
        color: var(--muted);
    
    }

    
    .lp-empty-icon {
        
        width: 64px;
        
        height: 64px;
        
        border-radius: 999px;
        
        background: var(--surface-soft);
        
        display: grid;
        
        place-items: center;
        
        margin: 0 auto 0.75rem;
        
        color: var(--placeholder);
    
    }

    
    .lp-empty-icon svg {
        
        width: 32px;
        
        height: 32px;
        
        stroke: currentColor;
        
        fill: none;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
        
        display: block;
    
    }

    
    .lp-empty-text {
        
        margin: 0;
        
        font-size: 0.9rem;
    
    }

    
    .lp-selected-list {
        
        display: grid;
        
        gap: 0.75rem;
    
    }

    
    .lp-selected-item {
        
        display: flex;
        
        align-items: center;
        
        justify-content: space-between;
        
        gap: 0.75rem;
        
        padding: 0.75rem;
        
        border-radius: 0.75rem;
        
        background: var(--surface-soft);
    
    }

    
    .lp-selected-name {
        
        font-size: 0.9rem;
        
        font-weight: 600;
        
        color: var(--ink);
        
        line-height: 1.2;
    
    }

    
    .lp-selected-meta {
        
        font-size: 0.8rem;
        
        color: var(--muted);
        
        margin-top: 0.1rem;
    
    }

    
    .lp-selected-remove {
        
        width: 24px;
        
        height: 24px;
        
        border-radius: 999px;
        
        border: none;
        
        background: #ef4444;
        
        color: #ffffff;
        
        display: grid;
        
        place-items: center;
        
        transition: background 0.15s ease, transform 0.15s ease;
        
        flex: none;
    
    }

    
    .lp-selected-remove:hover {
        
        background: #dc2626;
        
        transform: scale(1.03);
    
    }

    
    .lp-selected-remove svg {
        
        width: 14px;
        
        height: 14px;
        
        stroke: currentColor;
        
        fill: none;
        
        stroke-width: 2.4;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
        
        display: block;
    
    }

    
    .lp-select {
        
        width: 100%;
        
        border: 2px solid var(--line);
        
        border-radius: 0.75rem;
        
        padding: 0.75rem 1rem;
        
        outline: none;
        
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        
        background: var(--surface-soft);
        
        color: var(--ink);
        
        font-weight: 600;
    
    }

    
    .lp-select:focus {
        
        border-color: #a855f7;
        
        box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.15);
    
    }

    
    .lp-submit {
        
        width: 100%;
        
        border: none;
        
        border-radius: 0.75rem;
        
        padding: 1rem 1.25rem;
        
        font-weight: 700;
        
        color: #ffffff;
        
        background: #9333ea;
        
        box-shadow: 0 10px 15px -3px rgba(147, 51, 234, 0.22), 0 4px 6px -4px rgba(219, 39, 119, 0.22);
        
        transition: box-shadow 0.2s ease, transform 0.15s ease;
        
        cursor: pointer;
    
    }

    
    .lp-submit:hover {
        
        box-shadow: 0 16px 22px -6px rgba(147, 51, 234, 0.25), 0 10px 16px -8px rgba(219, 39, 119, 0.25);
        
        transform: scale(1.02);
    
    }

    
    .lp-submit:not(:disabled):active {
        
        transform: scale(0.98);
    
    }

    
    .lp-submit:disabled {
        
        opacity: 0.55;
        
        cursor: not-allowed;
        
        transform: none;
        
        box-shadow: none;
    
    }

    
    .lp-submit-inner {
        
        display: inline-flex;
        
        align-items: center;
        
        justify-content: center;
        
        gap: 0.5rem;
    
    }

    
    .lp-submit-icon {
        
        width: 20px;
        
        height: 20px;
        
        display: inline-flex;
        
        align-items: center;
        
        justify-content: center;
    
    }

    
    .lp-submit-icon svg {
        
        width: 20px;
        
        height: 20px;
        
        stroke: currentColor;
        
        fill: none;
        
        stroke-width: 2.2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
        
        display: block;
    
    }

    
    @media (max-width: 1100px) {
        
        .lp-list {
            
            max-height: none;
        
        }
    
    }

    
    @media (prefers-reduced-motion: reduce) {
        
        .lp-header,
        
        .lp-animate-left,
        
        .lp-animate-right,
        
        .lp-item-animate {
            
            animation: none !important;
        
        }

        
        .lp-item,
        
        .lp-selected-remove,
        
        .lp-submit {
            
            transition: none !important;
        
        }

        
        .lp-item:hover,
        
        .lp-item:active,
        
        .lp-submit:hover,
        
        .lp-submit:active,
        
        .lp-selected-remove:hover {
            
            transform: none !important;
        
        }
    
    }

</style>

<div class="lp-shell">
    
    <div class="lp-header">
        
        <h1 class="lp-title">Link Products</h1>
        
        <p class="lp-subtitle">Link products to store branches</p>
    
    </div>

    
    <form action="{{ route('stores.products.update', $store, false) }}" method="POST" class="lp-grid">
        
        @csrf
        
        @method('PUT')

        
        <section class="lp-card lp-card--list lp-animate-left" style="animation-delay: 0.1s;">
            
            <div class="lp-products-header">
                
                <div class="lp-products-head">
                    
                    <div class="lp-products-title">
                        
                        <span class="lp-products-mark" aria-hidden="true">
                            
                            <svg viewBox="0 0 24 24">
                                
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"></path>
                                
                                <path d="M3.3 7.3L12 12l8.7-4.7"></path>
                                
                                <path d="M12 22V12"></path>
                            
                            </svg>
                        
                        </span>
                        
                        <div style="min-width: 0;">
                            
                            <h2 class="lp-products-heading">Available Products</h2>
                            
                            <div class="lp-products-count"><span data-selected-count>{{ count($selectedProducts) }}</span> selected</div>
                        
                        </div>
                    
                    </div>
                
                </div>

                
                <div class="lp-search">
                    
                    <span class="lp-search-icon" aria-hidden="true">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <circle cx="11" cy="11" r="8"></circle>
                            
                            <path d="m21 21-4.3-4.3"></path>
                        
                        </svg>
                    
                    </span>
                    
                    <input type="search" class="lp-search-input" placeholder="Search for a product or SKU" data-product-search>
                
                </div>
            
            </div>

            
            <div class="lp-products-body">
                
                <div class="lp-list" data-product-list>
                
                @foreach ($products as $product)
                    
                    @php
                        
                        $isSelected = isset($selectedSet[$product->id]);
                        
                        $storeNames = $product->stores
                            
                            ->map(function ($linkedStore) use ($storeNameById) {
                                
                                return $storeNameById[$linkedStore->id]
                                    
                                    ?? \App\Support\EnglishPlaceNames::branchDisplayName($linkedStore->branch_code, $linkedStore->name);
                            
                            })
                            
                            ->filter()
                            
                            ->values();
                        
                        $priceLabel = ($product->price !== null && $usdToSypRate)
                            
                            ? '$'.number_format(((float) $product->price) / $usdToSypRate, 2)
                            
                            : '—';
                        
                        $linkedBranchesLabel = 'Linked: '.$storeNames->count().' branches';
                    
                    @endphp
                    
                    <button
                        
                        type="button"
                        
                        class="lp-item lp-item-animate {{ $isSelected ? 'is-selected' : '' }}"
                        
                        style="animation-delay: {{ number_format(0.2 + ($loop->index * 0.05), 2) }}s;"
                        
                        data-product-item
                        
                        data-product-id="{{ $product->id }}"
                        
                        data-product-name="{{ $product->name }}"
                        
                        data-product-sku="{{ $product->sku }}"
                        
                        data-product-price="{{ $priceLabel }}"
                        
                        aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                    
                    >
                        
                        <div class="lp-item-left">
                            
                            <span class="lp-item-icon" aria-hidden="true">
                                
                                <svg viewBox="0 0 24 24">
                                    
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"></path>
                                    
                                    <path d="M3.3 7.3L12 12l8.7-4.7"></path>
                                    
                                    <path d="M12 22V12"></path>
                                
                                </svg>
                            
                            </span>
                            
                            <div class="lp-item-copy">
                                
                                <div class="lp-item-name">{{ $product->name }}</div>
                                
                                <div class="lp-item-meta">SKU: {{ $product->sku }}</div>
                                
                                <div class="lp-item-branches" aria-label="Linked branches">
                                    
                                    <span class="lp-item-branches-label">Branches:</span>
                                    
                                    @forelse ($storeNames as $storeName)
                                        
                                        <span class="lp-chip">{{ $storeName }}</span>
                                    
                                    @empty
                                        
                                        <span class="lp-chip lp-chip--empty">None</span>
                                    
                                    @endforelse
                                
                                </div>
                            
                            </div>
                        
                        </div>

                        
                        <div class="lp-item-right">
                            
                            <div class="lp-item-price">{{ $priceLabel }}</div>
                            
                            <div class="lp-item-stock">{{ $linkedBranchesLabel }}</div>
                        
                        </div>
                    
                    </button>
                
                @endforeach
                
                </div>
            
            </div>
        
        </section>

        
        <aside class="lp-side lp-animate-right" style="animation-delay: 0.2s;">
            
            <div class="lp-side-actions">
                
                <section class="lp-card">
                    
                    <h3 class="lp-side-title">{{ $branchTitle }}</h3>

                    
                    @if ($canSwitchBranch)
                        
                        @include('partials.custom-select-native', [
                            
                            'options' => $storeOptions,
                            
                            'selectedValue' => (string) $store->id,
                            
                            'selectedLabel' => $storeLabel,
                            
                            'wrapperClass' => 'custom-select',
                            
                            'selectClass' => 'lp-select custom-select-native',
                            
                            'triggerClass' => 'lp-select custom-select-trigger',
                            
                            'selectAttributes' => 'data-store-select aria-label="Choose branch"',
                        
                        ])
                    
                    @else
                        
                        <div class="lp-select" aria-label="Assigned branch">
                            
                            {{ $limitedStoreLabel }}
                        
                        </div>
                    
                    @endif
                
                </section>

                
                <button type="submit" class="lp-submit" data-submit-button {{ count($selectedProducts) ? '' : 'disabled' }}>
                    
                    <span class="lp-submit-inner">
                        
                        <span class="lp-submit-icon" aria-hidden="true">
                            
                            <svg viewBox="0 0 24 24">
                                
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"></path>
                                
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                
                                <polyline points="7 3 7 8 15 8"></polyline>
                            
                            </svg>
                        
                        </span>
                        
                        Save Changes
                    
                    </span>
                
                </button>
            
            </div>

            
            <section class="lp-card">
                
                <h3 class="lp-side-title">Selected Products</h3>

                
                <div class="lp-empty" data-selected-empty style="{{ count($selectedProducts) ? 'display:none;' : '' }}">
                    
                    <div class="lp-empty-icon" aria-hidden="true">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"></path>
                            
                            <path d="M3.3 7.3L12 12l8.7-4.7"></path>
                            
                            <path d="M12 22V12"></path>
                        
                        </svg>
                    
                    </div>
                    
                    <p class="lp-empty-text">No products selected yet</p>
                
                </div>

                
                <div class="lp-selected-list" data-selected-list>
                    
                    @foreach ($products as $product)
                        
                        @if (isset($selectedSet[$product->id]))
                            
                            @php
                                
                                $selectedPriceLabel = ($product->price !== null && $usdToSypRate)
                                    
                                    ? '$'.number_format(((float) $product->price) / $usdToSypRate, 2)
                                    
                                    : '—';
                            
                            @endphp
                            
                            <div class="lp-selected-item" data-selected-item data-product-id="{{ $product->id }}">
                                
                                <div>
                                    
                                    <div class="lp-selected-name">{{ $product->name }}</div>
                                    
                                    <div class="lp-selected-meta">{{ $selectedPriceLabel }}</div>
                                
                                </div>
                                
                                <button type="button" class="lp-selected-remove" data-remove-selected aria-label="Remove product">
                                    
                                    <svg viewBox="0 0 24 24">
                                        
                                        <path d="M18 6 6 18"></path>
                                        
                                        <path d="M6 6l12 12"></path>
                                    
                                    </svg>
                                
                                </button>
                                
                                <input type="hidden" name="product_ids[]" value="{{ $product->id }}">
                            
                            </div>
                        
                        @endif
                    
                    @endforeach
                
                </div>
            
            </section>
        
        </aside>
    
    </form>

</div>

<script>
    
    document.addEventListener('DOMContentLoaded', function () {
        
        const productList = document.querySelector('[data-product-list]');
        
        const searchInput = document.querySelector('[data-product-search]');
        
        const selectedList = document.querySelector('[data-selected-list]');
        
        const emptyState = document.querySelector('[data-selected-empty]');
        
        const submitButton = document.querySelector('[data-submit-button]');
        
        const selectedCountEls = Array.from(document.querySelectorAll('[data-selected-count]'));

        
        const selectedInputs = selectedList
            
            ? Array.from(selectedList.querySelectorAll('input[name="product_ids[]"]'))
            
            : [];
        
        const selectedIds = new Set(selectedInputs.map(function (input) { return String(input.value || ''); }).filter(Boolean));
        
        const initialSelectedCount = selectedIds.size;

        
        function setCount(count) {
            
            selectedCountEls.forEach(function (el) {
                
                el.textContent = String(count);
            
            });
        
        }

        
        function updateSummaryState() {
            
            const count = selectedIds.size;
            
            setCount(count);
            
            if (submitButton) {
                
                submitButton.disabled = count === 0 && initialSelectedCount === 0;
            
            }
            
            if (emptyState) {
                
                emptyState.style.display = count === 0 ? '' : 'none';
            
            }
        
        }

        
        function setProductSelected(button, selected) {
            
            if (!button) return;
            
            button.classList.toggle('is-selected', selected);
            
            button.setAttribute('aria-pressed', selected ? 'true' : 'false');
        
        }

        
        function getProductMeta(button) {
            
            return {
                
                id: String(button?.dataset.productId || ''),
                
                name: String(button?.dataset.productName || '').trim(),
                
                sku: String(button?.dataset.productSku || '').trim(),
                
                price: String(button?.dataset.productPrice || '').trim() || '—',
            
            };
        
        }

        
        function createSelectedItem(meta) {
            
            const wrapper = document.createElement('div');
            
            wrapper.className = 'lp-selected-item';
            
            wrapper.dataset.selectedItem = '1';
            
            wrapper.dataset.productId = meta.id;

            
            const copy = document.createElement('div');
            
            const name = document.createElement('div');
            
            name.className = 'lp-selected-name';
            
            name.textContent = meta.name || 'Product';
            
            const price = document.createElement('div');
            
            price.className = 'lp-selected-meta';
            
            price.textContent = meta.price || '—';
            
            copy.appendChild(name);
            
            copy.appendChild(price);

            
            const remove = document.createElement('button');
            
            remove.type = 'button';
            
            remove.className = 'lp-selected-remove';
            
            remove.setAttribute('data-remove-selected', '');
            
            remove.setAttribute('aria-label', 'Remove product');
            
            remove.innerHTML = '<svg viewBox="0 0 24 24"><path d="M18 6 6 18"></path><path d="M6 6l12 12"></path></svg>';

            
            const input = document.createElement('input');
            
            input.type = 'hidden';
            
            input.name = 'product_ids[]';
            
            input.value = meta.id;

            
            wrapper.appendChild(copy);
            
            wrapper.appendChild(remove);
            
            wrapper.appendChild(input);
            
            return wrapper;
        
        }

        
        function getSelectedItem(id) {
            
            if (!selectedList) return null;
            
            return selectedList.querySelector('[data-selected-item][data-product-id="' + CSS.escape(id) + '"]');
        
        }

        
        function toggleSelection(button) {
            
            const meta = getProductMeta(button);
            
            if (!meta.id) return;

            
            if (selectedIds.has(meta.id)) {
                
                selectedIds.delete(meta.id);
                
                setProductSelected(button, false);
                
                const existing = getSelectedItem(meta.id);
                
                if (existing) {
                    
                    existing.remove();
                
                }
            
            } else {
                
                selectedIds.add(meta.id);
                
                setProductSelected(button, true);
                
                if (selectedList && !getSelectedItem(meta.id)) {
                    
                    selectedList.prepend(createSelectedItem(meta));
                
                }
            
            }

            
            updateSummaryState();
        
        }

        
        productList?.querySelectorAll('[data-product-item]').forEach(function (button) {
            
            const id = String(button.dataset.productId || '');
            
            setProductSelected(button, selectedIds.has(id));
        
        });

        
        productList?.addEventListener('click', function (event) {
            
            const button = event.target.closest('[data-product-item]');
            
            if (!button) return;
            
            toggleSelection(button);
        
        });

        
        selectedList?.addEventListener('click', function (event) {
            
            const remove = event.target.closest('[data-remove-selected]');
            
            if (!remove) return;
            
            const item = remove.closest('[data-selected-item]');
            
            const id = String(item?.dataset.productId || '');
            
            if (!id) return;
            
            const button = productList?.querySelector('[data-product-item][data-product-id="' + CSS.escape(id) + '"]');
            
            if (button) {
                
                toggleSelection(button);
            
            }
        
        });

        
        function applySearch(query) {
            
            const normalized = String(query || '').trim().toLowerCase();
            
            productList?.querySelectorAll('[data-product-item]').forEach(function (button) {
                
                const name = String(button.dataset.productName || '').toLowerCase();
                
                const sku = String(button.dataset.productSku || '').toLowerCase();
                
                const visible = !normalized || name.includes(normalized) || sku.includes(normalized);
                
                button.style.display = visible ? '' : 'none';
            
            });
        
        }

        
        searchInput?.addEventListener('input', function () {
            
            applySearch(searchInput.value);
        
        });

        
        const storeSelect = document.querySelector('[data-store-select]');
        
        storeSelect?.addEventListener('change', function () {
            
            const id = storeSelect.value;
            
            if (!id) return;
            
            const target = "{{ route('stores.products', '__STORE__', false) }}".replace('__STORE__', id);
            
            window.location.href = target;
        
        });

        
        updateSummaryState();
    
    });

</script>

@endsection
