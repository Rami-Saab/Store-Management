@extends('layouts.app')

@section('title', 'Store Brochure')

@section('content')

@php
    
    
    $backUrl = route('stores.index', [], false);
    
    $tabId = request('tab');
    
    $tabId = is_string($tabId) ? trim($tabId) : '';
    
    if ($tabId !== '' && !preg_match('/^[A-Za-z0-9_-]{6,64}$/', $tabId)) {
        
        $tabId = '';
    
    }
    
    $inlineUrlWithTab = $inlineUrl;
    
    $downloadUrlWithTab = $downloadUrl;
    
    if ($tabId !== '') {
        
        $inlineUrlWithTab .= (str_contains($inlineUrlWithTab, '?') ? '&' : '?').'tab='.urlencode($tabId);
        
        $downloadUrlWithTab .= (str_contains($downloadUrlWithTab, '?') ? '&' : '?').'tab='.urlencode($tabId);
    
    }

@endphp

@push('head')
    
    <link rel="preload" href="{{ $inlineUrlWithTab }}" as="document">

@endpush

<div class="page-shell bv-shell">
    
    
    
    <div class="bv-head">
        
        
        
        <a class="bv-back" href="{{ $backUrl }}" aria-label="Back to Branch Directory">
            
            
            
            <svg viewBox="0 0 24 24" aria-hidden="true">
                
                
                
                <path d="M15 18l-6-6 6-6"></path>
            
            </svg>
        
        </a>

        
        
        
        <div class="bv-title">
            
            
            
            <h1 class="bv-h1">Store Brochure</h1>
        
        
        
        <div class="bv-sub">
            
            
            
            <span class="bv-branch">{{ $store->name }}</span>
        
        </div>
        
        </div>

        
        
        
        <div class="bv-actions">
            
            
            
            <a class="bv-action-link" href="{{ $inlineUrlWithTab }}" target="_blank" rel="noopener" data-keep-tab>Open in a new tab</a>
            
            
            
            <a class="bv-action-link" href="{{ $downloadUrlWithTab }}">Download brochure</a>
        
        </div>
    
    </div>

    
    
    
    <section class="surface-card bv-card" aria-label="Brochure PDF viewer">
        
        
        
        <div class="bv-layout" data-bv-root>
            
            
            
            <div class="bv-main">
            
            
            
            <div class="bv-viewer bv-viewer--native" role="region" aria-label="Brochure PDF">
                
                <div class="bv-loading" data-bv-loading aria-live="polite" aria-busy="true">
                    
                    <span class="bv-spinner" aria-hidden="true"></span>
                    
                    <span class="bv-loading-text">Loading brochure…</span>
                
                </div>
                
                
                
                <iframe
                    
                    class="bv-native-frame"
                    
                    src="{{ $inlineUrlWithTab }}"
                    
                    title="Store brochure PDF"
                    
                    loading="eager"
                    
                    fetchpriority="high"
                    
                    allowfullscreen
                
                ></iframe>
            
            </div>
            
            </div>
        
        </div>
    
    </section>

</div>

<style>
    
    
    
    .bv-shell {
        
        
        
        padding: 0;
    
    }
    
    

    
    
    
    .bv-head {
        
        
        
        display: flex;
        
        
        
        flex-wrap: wrap;
        
        
        
        align-items: center;
        
        
        
        gap: 1rem;
        
        
        
        background: transparent;
        
        
        
        border: 0;
        
        
        
        padding: 0.25rem 0;
        
        
        
        box-shadow: none;
    
    }
    
    

    
    
    
    .bv-back {
        
        
        
        width: 44px;
        
        
        
        height: 44px;
        
        
        
        border-radius: 14px;
        
        
        
        display: inline-flex;
        
        
        
        align-items: center;
        
        
        
        justify-content: center;
        
        
        
        background: rgba(148, 163, 184, 0.14);
        
        
        
        color: var(--ink);
        
        
        
        transition: transform 0.15s ease, background 0.15s ease;
    
    }
    
    

    
    
    
    .bv-back:hover {
        
        
        
        transform: translateY(-1px);
        
        
        
        background: rgba(148, 163, 184, 0.22);
    
    }
    
    

    
    
    
    .bv-back svg {
        
        
        
        width: 20px;
        
        
        
        height: 20px;
        
        
        
        fill: none;
        
        
        
        stroke: currentColor;
        
        
        
        stroke-width: 2.4;
        
        
        
        stroke-linecap: round;
        
        
        
        stroke-linejoin: round;
    
    }
    
    

    
    
    
    .bv-title {
        
        
        
        min-width: 0;
    
    }
    
    

    
    
    
    .bv-h1 {
        
        
        
        margin: 0;
        
        
        
        font-size: 1.5rem;
        
        
        
        font-weight: 900;
        
        
        
        letter-spacing: -0.02em;
        
        
        
        color: var(--ink);
    
    }
    
    

    
    
    
    .bv-sub {
        
        
        
        display: flex;
        
        
        
        align-items: center;
        
        
        
        gap: 0.5rem;
        
        
        
        margin-top: 0.2rem;
        
        
        
        color: var(--muted);
        
        
        
        font-weight: 600;
        
        
        
        min-width: 0;
        
        
        
        flex-wrap: wrap;
    
    }
    
    

    
    
    
    .bv-branch {
        
        
        
        display: inline-flex;
        
        
        
        align-items: center;
        
        
        
        justify-content: center;
        
        
        
        padding: 0.25rem 0.7rem;
        
        
        
        border-radius: 999px;
        
        
        
        background: rgba(15, 23, 42, 0.82);
        
        
        
        color: #f8fafc;
        
        
        
        font-weight: 800;
        
        
        
        border: 1px solid rgba(255, 255, 255, 0.14);
        
        
        
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.25);
    
    }
    
    

    
    
    
    .bv-card {
        
        
        
        position: relative;
        
        
        
        padding: 0;
        
        
        
        display: grid;
        
        
        
        gap: 0.9rem;
        
        
        
        background: transparent;
        
        
        
        border: 0;
        
        
        
        box-shadow: none;
    
    }
    
    

    
    
    
    .bv-layout {
        
        
        
        display: grid;
        
        
        
        grid-template-columns: minmax(0, 1fr);
        
        
        
        gap: 1rem;
        
        
        
        align-items: stretch;
    
    }
    
    

    
    
    
    .bv-main {
        
        
        
        min-width: 0;
        
        
        
        display: grid;
        
        
        
        gap: 0.55rem;
    
    }
    
    

    
    
    
    .bv-actions {
        
        
        
        margin-left: auto;
        
        
        
        display: flex;
        
        
        
        flex-wrap: wrap;
        
        
        
        align-items: center;
        
        
        
        gap: 0.4rem;
    
    }
    
    

    
    
    
    .bv-action-link {
        
        
        
        display: inline-flex;
        
        
        
        align-items: center;
        
        
        
        justify-content: center;
        
        
        
        gap: 0.4rem;
        
        
        
        padding: 0.42rem 0.85rem;
        
        
        
        border-radius: 0.75rem;
        
        
        
        border: 1px solid var(--line);
        
        
        
        background: var(--surface-soft);
        
        
        
        color: var(--ink);
        
        
        
        text-decoration: none;
        
        
        
        font-weight: 800;
        
        
        
        transition: transform 0.15s ease, background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    
    }
    
    

    
    
    
    .bv-action-link:hover,
    
    .bv-action-link:focus {
        
        
        
        transform: translateY(-1px);
        
        
        
        background: var(--surface);
        
        
        
        border-color: rgba(var(--primary-rgb), 0.28);
        
        
        
        color: var(--ink);
    
    }
    
    

    
    
    
    @media (max-width: 720px) {
        
        
        
        .bv-actions {
            
            
            
            width: 100%;
            
            
            
            margin-left: 0;
            
            
            
            justify-content: flex-start;
        
        }
    
    }
    
    

    
    
    
    .bv-viewer {
        
        
        
        background: transparent;
        
        
        
        border: 0;
        
        
        
        box-shadow: none;
        
        
        
        border-radius: 18px;
        
        
        
        overflow: hidden;
        
        
        
        height: clamp(720px, 86vh, 1180px);
        
        
        
        min-height: 640px;
        
        
        
        position: relative;
    
    }
    
    

    
    
    
    .bv-native-frame {
        
        
        
        width: 100%;
        
        
        
        height: 100%;
        
        
        
        border: 0;
        
        
        
        background: #f3f6fb;
        
        
        
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
        
        
        
        border-radius: 18px;
    
    }
    
    

    
    .bv-loading {
        
        position: absolute;
        
        inset: 0;
        
        display: flex;
        
        flex-direction: column;
        
        align-items: center;
        
        justify-content: center;
        
        gap: 0.65rem;
        
        background: var(--surface-soft);
        
        color: var(--ink);
        
        font-weight: 700;
        
        letter-spacing: 0.01em;
        
        z-index: 2;
        
        transition: opacity 0.2s ease, visibility 0.2s ease;
    
    }

    
    .bv-loading.is-hidden {
        
        opacity: 0;
        
        visibility: hidden;
        
        pointer-events: none;
    
    }

    
    .bv-spinner {
        
        width: 44px;
        
        height: 44px;
        
        border-radius: 999px;
        
        border: 4px solid rgba(148, 163, 184, 0.4);
        
        border-top-color: #3b82f6;
        
        animation: bv-spin 0.8s linear infinite;
        
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.15);
        
        background: var(--glass-surface-strong);
    
    }

    
    .bv-loading-text {
        
        font-size: 0.95rem;
        
        color: var(--muted);
    
    }

    
    @keyframes bv-spin {
        
        to {
            
            transform: rotate(360deg);
        
        }
    
    }

</style>

@endsection

@push('scripts')

<script>
    
    (function () {
        
        const frame = document.querySelector('.bv-native-frame');
        
        const loading = document.querySelector('[data-bv-loading]');
        
        if (!frame || !loading) return;

        
        const hideLoading = () => {
            
            loading.classList.add('is-hidden');
            
            loading.setAttribute('aria-busy', 'false');
        
        };

        
        frame.addEventListener('load', () => {
            
            requestAnimationFrame(hideLoading);
        
        }, { once: true });

        
        
        setTimeout(hideLoading, 8000);
    
    })();

</script>

@endpush
