@extends('layouts.app')

@section('title', 'Search')

@section('page_subtitle', '')

@section('content')

@php
    
    
    $hasQuery = trim((string) $query) !== '';
    
    
    $hasResults = $sections->first(fn ($section) => ($section['count'] ?? 0) > 0);
    
    
    $intentBadge = $intentLabel ?: 'All results';

@endphp

<style>
    
    .sr-shell { display: grid; gap: 1.5rem; }
    
    .sr-header { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem; }
    
    .sr-title { margin: 0; font-size: 2rem; font-weight: 800; color: var(--ink); }
    
    .sr-subtitle { margin: 0.35rem 0 0; color: var(--muted); }
    
    .sr-intent {
        
        background: rgba(var(--primary-rgb), 0.12);
        
        color: var(--primary);
        
        border-radius: 999px;
        
        padding: 0.35rem 0.9rem;
        
        font-weight: 700;
        
        font-size: 0.85rem;
        
        align-self: flex-start;
    
    }

    
    .sr-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; }
    
    .sr-summary-card {
        
        background: var(--glass-surface-strong);
        
        border-radius: 1rem;
        
        border: 1px solid var(--glass-border);
        
        padding: 1rem 1.25rem;
        
        box-shadow: var(--glass-shadow);
    
    }
    
    .sr-summary-card h4 { margin: 0; font-size: 1.5rem; font-weight: 800; color: var(--ink); }
    
    .sr-summary-card p { margin: 0.25rem 0 0; color: var(--muted); font-weight: 600; }

    
    .sr-section { background: var(--glass-surface-strong); border-radius: 1rem; border: 1px solid var(--glass-border); box-shadow: var(--glass-shadow); overflow: hidden; }
    
    .sr-section-header { padding: 1rem 1.25rem; background: var(--surface-soft); border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
    
    .sr-section-title { margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--ink); }
    
    .sr-section-count { font-weight: 700; color: var(--muted); }
    
    .sr-section-body { padding: 1.25rem; display: grid; gap: 0.75rem; }

    
    .sr-card {
        
        background: var(--surface-soft);
        
        border: 1px solid var(--line);
        
        border-radius: 0.85rem;
        
        padding: 0.85rem 1rem;
        
        display: flex;
        
        align-items: center;
        
        justify-content: space-between;
        
        gap: 1rem;
    
    }
    
    .sr-card h5 { margin: 0; font-size: 1rem; font-weight: 700; color: var(--ink); }
    
    .sr-card p { margin: 0.2rem 0 0; color: var(--muted); font-size: 0.85rem; }
    
    .sr-card-meta { font-weight: 700; color: var(--muted); font-size: 0.85rem; white-space: nowrap; }
    
    .sr-link { color: var(--primary); text-decoration: none; font-weight: 700; }
    
    .sr-link:hover { color: var(--primary-deep); }

    
    .sr-empty {
        
        padding: 2.5rem;
        
        background: var(--glass-surface-strong);
        
        border-radius: 1rem;
        
        border: 1px solid var(--glass-border);
        
        text-align: center;
        
        color: var(--muted);
        
        font-weight: 600;
    
    }

    
    @media (max-width: 1100px) {
        
        .sr-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    
    }
    
    @media (max-width: 720px) {
        
        .sr-summary { grid-template-columns: 1fr; }
        
        .sr-card { flex-direction: column; align-items: flex-start; }
        
        .sr-card-meta { align-self: flex-start; }
    
    }

</style>

<div class="page-shell sr-shell">
    
    <div class="sr-header">
        
        <div>
            
            <h1 class="sr-title">Search results</h1>
            
            <p class="sr-subtitle">
                
                @if ($hasQuery)
                    
                    Showing matches for <strong>{{ $query }}</strong>
                
                @else
                    
                    Search across employees, products, branches, and provinces.
                
                @endif
            
            </p>
        
        </div>
        
        <span class="sr-intent">Smart match: {{ $intentBadge }}</span>
    
    </div>

    
    @if (!$hasQuery)
        
        <div class="sr-empty">Type something in the search bar to start.</div>
    
    @elseif (!$hasResults)
        
        <div class="sr-empty">No results found for your search.</div>
    
    @else
        
        <div class="sr-summary">
            
            @foreach ($sections as $section)
                
                <div class="sr-summary-card">
                    
                    <h4>{{ $section['count'] }}</h4>
                    
                    <p>{{ $section['title'] }}</p>
                
                </div>
            
            @endforeach
        
        </div>

        
        @foreach ($sections as $section)
            
            <div class="sr-section">
                
                <div class="sr-section-header">
                    
                    <h2 class="sr-section-title">{{ $section['title'] }}</h2>
                    
                    <span class="sr-section-count">{{ $section['count'] }} results</span>
                
                </div>
                
                <div class="sr-section-body">
                    
                    @if (($section['count'] ?? 0) === 0)
                        
                        <div class="sr-card">
                            
                            <div>
                                
                                <h5>No matches</h5>
                                
                                <p>Try a different keyword.</p>
                            
                            </div>
                        
                        </div>
                    
                    @else
                        
                        @if ($section['key'] === 'stores')
                            
                            @foreach ($section['items'] as $store)
                                
                                @php
                                    
                                    $storeName = \App\Support\EnglishPlaceNames::branchDisplayName($store->branch_code, $store->name);
                                    
                                    $provinceLabel = $store->province?->code
                                        
                                        ? (\App\Support\EnglishPlaceNames::provinceByCode($store->province->code) ?: $store->province->name)
                                        
                                        : 'Not specified';
                                
                                @endphp
                                
                                <div class="sr-card">
                                    
                                    <div>
                                        
                                        <h5>{{ $storeName }}</h5>
                                        
                                        <p>{{ $store->branch_code }} • {{ $provinceLabel }}</p>
                                    
                                    </div>
                                    
                                    <a class="sr-link" href="{{ route('stores.show', $store, false) }}">Open</a>
                                
                                </div>
                            
                            @endforeach
                        
                        @elseif ($section['key'] === 'users')
                            
                            @foreach ($section['items'] as $user)
                                
                                @php
                                    
                                    $roleLabel = match ($user->role) {
                                        
                                        'admin' => 'System Administrator',
                                        
                                        'department_manager' => 'Department Manager',
                                        
                                        'store_manager' => 'Store Manager',
                                        
                                        'store_employee' => 'Store Employee',
                                        
                                        default => $user->role ?: 'User',
                                    
                                    };
                                    
                                    $storesLabel = $user->stores->map(function ($store) {
                                        
                                        return \App\Support\EnglishPlaceNames::branchDisplayName($store->branch_code, $store->name);
                                    
                                    })->take(2)->implode(', ');
                                
                                @endphp
                                
                                <div class="sr-card">
                                    
                                    <div>
                                        
                                        <h5>{{ $user->name }}</h5>
                                        
                                        <p>{{ $roleLabel }}{{ $storesLabel ? ' • '.$storesLabel : '' }}</p>
                                    
                                    </div>
                                    
                                    <span class="sr-card-meta">{{ \App\Support\UserContact::email($user->email, $user->name, (int) $user->id) }}</span>
                                
                                </div>
                            
                            @endforeach
                        
                        @elseif ($section['key'] === 'products')
                            
                            @foreach ($section['items'] as $product)
                                
                                <div class="sr-card">
                                    
                                    <div>
                                        
                                        <h5>{{ $product->name }}</h5>
                                        
                                        <p>
                                            
                                            {{ $product->sku ? 'SKU: '.$product->sku : 'SKU not set' }}
                                            
                                            {{ $product->status ? ' • '.$product->status : '' }}
                                        
                                        </p>
                                    
                                    </div>
                                    
                                    <a class="sr-link" href="{{ route('products.show', $product, false) }}">Open</a>
                                
                                </div>
                            
                            @endforeach
                        
                        @elseif ($section['key'] === 'provinces')
                            
                            @foreach ($section['items'] as $province)
                                
                                @php
                                    
                                    $provinceLabel = \App\Support\EnglishPlaceNames::provinceByCode($province->code) ?: $province->name;
                                
                                @endphp
                                
                                <div class="sr-card">
                                    
                                    <div>
                                        
                                        <h5>{{ $provinceLabel }}</h5>
                                        
                                        <p>{{ $province->code }}</p>
                                    
                                    </div>
                                    
                                    <span class="sr-card-meta">Province</span>
                                
                                </div>
                            
                            @endforeach
                        
                        @endif
                    
                    @endif
                
                </div>
            
            </div>
        
        @endforeach
    
    @endif

</div>

@endsection
