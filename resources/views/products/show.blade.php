@extends('layouts.app')

@section('title', 'Product Details')

@section('page_subtitle', 'Stores linked to this product')

@section('content')

@php
    
    
    $stores = $product->stores ?? collect();

@endphp

<style>
    
    .product-shell { display: grid; gap: 1.5rem; }
    
    .product-header {
        
        background: var(--glass-surface-strong);
        
        border: 1px solid var(--glass-border);
        
        border-radius: 1rem;
        
        padding: 1.25rem 1.5rem;
        
        box-shadow: var(--glass-shadow);
    
    }
    
    .product-title { margin: 0; font-size: 1.6rem; font-weight: 800; color: var(--ink); }
    
    .product-meta { margin: 0.35rem 0 0; color: var(--muted); font-weight: 600; }

    
    .stores-grid { display: grid; gap: 1rem; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    
    .store-card {
        
        background: var(--glass-surface-strong);
        
        border: 1px solid var(--glass-border);
        
        border-radius: 1rem;
        
        padding: 1rem 1.25rem;
        
        box-shadow: var(--glass-shadow);
        
        display: grid;
        
        gap: 0.5rem;
    
    }
    
    .store-card h4 { margin: 0; font-size: 1.05rem; font-weight: 800; color: var(--ink); }
    
    .store-card p { margin: 0; color: var(--muted); font-weight: 600; font-size: 0.9rem; }
    
    .store-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.25rem; }
    
    .store-link {
        
        display: inline-flex;
        
        align-items: center;
        
        justify-content: center;
        
        padding: 0.4rem 0.85rem;
        
        border-radius: 999px;
        
        border: 1px solid rgba(var(--primary-rgb), 0.22);
        
        color: var(--primary);
        
        background: rgba(var(--primary-rgb), 0.12);
        
        text-decoration: none;
        
        font-weight: 700;
        
        font-size: 0.85rem;
    
    }
    
    .store-link:hover { color: var(--primary-deep); }

    
    .stores-empty {
        
        padding: 2.5rem;
        
        background: var(--glass-surface-strong);
        
        border-radius: 1rem;
        
        border: 1px solid var(--glass-border);
        
        text-align: center;
        
        color: var(--muted);
        
        font-weight: 600;
    
    }

    
    @media (max-width: 1100px) {
        
        .stores-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    
    }
    
    @media (max-width: 700px) {
        
        .stores-grid { grid-template-columns: 1fr; }
    
    }

</style>

<div class="page-shell product-shell">
    
    <div class="product-header">
        
        <h1 class="product-title">{{ $product->name }}</h1>
        
        <p class="product-meta">
            
            {{ $product->sku ? 'SKU: '.$product->sku : 'SKU not set' }}
            
            {{ $product->status ? ' • '.$product->status : '' }}
        
        </p>
    
    </div>

    
    @if ($stores->isEmpty())
        
        <div class="stores-empty">No stores are linked to this product yet.</div>
    
    @else
        
        <div class="stores-grid">
            
            @foreach ($stores as $store)
                
                @php
                    
                    $storeName = \App\Support\EnglishPlaceNames::branchDisplayName($store->branch_code ?? '', $store->name ?? '');
                    
                    $provinceLabel = $store->province?->code
                        
                        ? (\App\Support\EnglishPlaceNames::provinceByCode($store->province->code) ?: $store->province->name)
                        
                        : ($store->province?->name ?? 'Not specified');
                    
                    $managerName = $store->manager?->name ?: '-';
                
                @endphp
                
                <div class="store-card">
                    
                    <h4>{{ $storeName }}</h4>
                    
                    <p>{{ $store->branch_code }} • {{ $provinceLabel }}</p>
                    
                    <p>Manager: {{ $managerName }}</p>
                    
                    <div class="store-actions">
                        
                        <a class="store-link" href="{{ route('stores.show', $store, false) }}">View Store</a>
                        
                        <a class="store-link" href="{{ route('stores.brochure.view', $store, false) }}">View Brochure</a>
                    
                    </div>
                
                </div>
            
            @endforeach
        
        </div>
    
    @endif

</div>

@endsection
