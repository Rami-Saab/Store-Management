@extends('layouts.app')

@section('title', 'Access Denied')

@section('page_subtitle', 'You do not have permission to access this page.')

@section('content')

@php
    
    
    $message = trim((string) ($exception?->getMessage() ?? ''));
    
    $message = $message !== '' ? $message : 'You do not have permission to access this page.';

@endphp

<div style="max-width: 720px; margin: 0 auto; padding: 1.5rem 0;">
    
    <div style="background: #fff7ed; border: 1px solid rgba(251, 146, 60, 0.35); border-radius: 18px; padding: 1.75rem; box-shadow: 0 16px 28px rgba(15, 23, 42, 0.08);">
        
        <div style="display: flex; align-items: flex-start; gap: 0.9rem;">
            
            <div style="width: 46px; height: 46px; border-radius: 14px; background: rgba(251, 146, 60, 0.16); display: grid; place-items: center; color: #ea580c;">
                
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    
                    <path d="M12 9v4"></path>
                    
                    <path d="M12 17h.01"></path>
                    
                    <path d="M10.3 3.9 2.2 17.9a2 2 0 0 0 1.7 3h16.2a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"></path>
                
                </svg>
            
            </div>
            
            <div>
                
                <h1 style="margin: 0 0 0.35rem; font-size: 1.4rem; font-weight: 800; color: #9a3412;">Access Denied (403)</h1>
                
                <p style="margin: 0; color: #9a3412; font-weight: 600; line-height: 1.6;">{{ $message }}</p>
            
            </div>
        
        </div>
    
    </div>

</div>

@endsection
