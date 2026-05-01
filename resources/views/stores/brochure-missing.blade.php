@extends('layouts.app')

@section('title', 'Brochure Unavailable')

@section('page_subtitle', '')

@section('content')

@php
    
    
    $canEdit = auth()->user()?->can('update', $store) ?? false;
    
    
    $branchName = $store->name ?: 'Branch';
    
    $branchCode = $store->branch_code ?: '';

@endphp

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
                
                <span class="bv-branch">{{ $branchName }}</span>
            
            </div>
        
        </div>
    
    </div>

    
    
    
    <div class="container py-4">
        
        <div class="card shadow-sm border-0">
            
            <div class="card-body p-4">
                
                <div class="d-flex align-items-start gap-3">
                    
                    <div class="rounded-4 d-flex align-items-center justify-content-center" style="width:54px;height:54px;background:#fff4e5;border:1px solid #fed7aa;">
                        
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            
                            <path d="M12 9v4"></path>
                            
                            <path d="M12 17h.01"></path>
                            
                            <path d="M10 2h4l6 6v8a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8l6-6Z"></path>
                        
                        </svg>
                    
                    </div>
                    
                    <div style="min-width:0;">
                        
                        <h3 class="mb-1 fw-bold">Sorry, there is no brochure uploaded for this branch yet.</h3>
                        
                        <div class="text-muted">
                            
                            {{ $branchName }}{{ $branchCode ? ' • '.$branchCode : '' }}
                        
                        </div>
                        
                        <p class="mt-2 mb-0 text-muted">
                            
                            You can return to the Branch Directory or upload the brochure later when it is ready.
                        
                        </p>
                    
                    </div>
                
                </div>

                
                
                
                @if ($canEdit)
                    
                    <div class="mt-4 d-flex flex-wrap gap-2 justify-content-end">
                        
                        <a class="btn btn-primary px-4" href="{{ route('stores.edit', $store->id, false) }}">Upload brochure</a>
                    
                    </div>
                
                @endif
            
            </div>
        
        </div>
    
    </div>

</div>

<style>
    
    .bv-shell { padding: 0; }
    
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
    
    .bv-title { min-width: 0; }
    
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

</style>

@endsection
