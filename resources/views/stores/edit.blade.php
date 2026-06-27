@extends('layouts.app')

@section('title', 'Edit Branch')

@section('page_subtitle', '')

@section('content')

@php
    
    
    $statusLabels = $statusLabels ?? [
        
        'active' => 'Active',
        
        'inactive' => 'Inactive',
        
        'under_maintenance' => 'Under maintenance',
    
    ];

    
    $currentUser = auth()->user();
    
    $isAdmin = $currentUser && $currentUser->role === 'admin';
    
    $canEditManager = $isAdmin;

    
    
    $currentProvinceId = (string) old('province_id', $store->province_id ?? '');
    
    
    $currentProvince = isset($provinces)
        
        ? $provinces->firstWhere('id', (int) $currentProvinceId)
        
        : null;
    
    
    $currentStatus = (string) old('status', $store->status ?: 'active');

    
    
    $workdayStarts = (string) old(
        
        'workday_starts_at',
        
        $store->workday_starts_at ? \Illuminate\Support\Str::of($store->workday_starts_at)->substr(0, 5) : ''
    
    );
    
    
    $workdayEnds = old('workday_ends_at');
    
    if ($workdayEnds === null) {
        
        if ($store->workday_ends_at) {
            
            $workdayEnds = \Illuminate\Support\Str::of($store->workday_ends_at)->substr(0, 5);
        
        } elseif (! $store->exists) {
            
            $workdayEnds = '00:00';
        
        } else {
            
            $workdayEnds = '';
        
        }
    
    }
    
    $workdayEnds = (string) $workdayEnds;

    
    
    
    $to12Hour = function (?string $value): string {
        
        $value = $value ? substr((string) $value, 0, 5) : '';
        
        if (! preg_match('/^(\\d{2}):(\\d{2})$/', $value, $matches)) {
            
            return '';
        
        }
        
        $h = (int) $matches[1];
        
        $m = $matches[2];
        
        $ampm = $h >= 12 ? 'PM' : 'AM';
        
        $h12 = $h % 12;
        
        if ($h12 === 0) {
            
            $h12 = 12;
        
        }
        
        return sprintf('%02d:%s %s', $h12, $m, $ampm);
    
    };

    
    $storeNameValue = old('name', $store->name);

    
    $addressValue = old('address');
    
    if ($addressValue === null) {
        
        $storedAddress = trim((string) ($store->address ?? ''));
        
        $addressValue = $storedAddress !== '' ? $storedAddress : '';
    
    }

    
    $phoneValue = \App\Support\UserContact::phone(old('phone', $store->phone), false);

    
    $openingDate = old('opening_date', optional($store->opening_date)->format('Y-m-d'));
    
    $currentManagerId = (string) old('manager_id', $store->manager?->id ?? '');
    
    $currentManager = $currentManagerId !== ''
        
        ? $managers->firstWhere('id', (int) $currentManagerId)
        
        : $store->manager;

    
    $provinceOptions = $provinces
        
        ->map(function ($province) {
            
            return ['value' => $province->id, 'label' => $province->name];
        
        })
        
        ->all();
    
    $provinceSelectedLabel = $currentProvince
        
        ? $currentProvince->name
        
        : 'Select a province';

    
    $statusOptions = collect($statuses)
        
        ->map(fn ($status) => ['value' => $status, 'label' => $statusLabels[$status] ?? $status])
        
        ->all();
    
    $statusSelectedLabel = $statusLabels[$currentStatus] ?? $currentStatus;

    
    $managerOptions = $managers
        
        ->map(fn ($manager) => [
            
            'value' => $manager->id,
            
            'label' => $manager->name,
            
            'label_html' => '<bdi dir="ltr">'.e($manager->name).'</bdi>',
        
        ])
        
        ->all();
    
    $managerSelectedLabel = $currentManager ? $currentManager->name : 'Select a manager';
    
    $managerSelectedLabelHtml = $currentManager
        
        ? '<bdi dir="ltr">'.e($currentManager->name).'</bdi>'
        
        : null;

    
    $selectedEmployees = old('employee_ids', $store->employees->pluck('id')->all() ?? []);
    
    $selectedEmployees = array_values(array_unique(array_map('intval', is_array($selectedEmployees) ? $selectedEmployees : [])));

    
    $modifiedByName = $store->updatedBy?->name
        
        ?? $store->createdBy?->name
        
        ?? 'Not available';
    
    $lastChangeLabel = $store->updated_at?->diffForHumans() ?? 'Not available';
    
    $createdLabel = $store->created_at?->format('M j, Y') ?? 'Not available';
    
    $storeDraftVersion = (string) ($store->updated_at?->timestamp ?? $store->created_at?->timestamp ?? $store->id ?? '0');

@endphp

<style>
    
    .eb-shell { display: grid; gap: 1.5rem; }
    
    .eb-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
    
    .eb-header-left { display: flex; align-items: center; gap: 1rem; }
    
    @keyframes ebFadeDown { from { opacity: 0; transform: translateY(-12px); } to { opacity: 1; transform: translateY(0); } }
    
    @keyframes ebFadeLeft { from { opacity: 0; transform: translateX(-12px); } to { opacity: 1; transform: translateX(0); } }
    
    @keyframes ebFadeRight { from { opacity: 0; transform: translateX(12px); } to { opacity: 1; transform: translateX(0); } }
    
    @keyframes ebFadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    
    .eb-animate-down { animation: ebFadeDown 0.55s ease both; }
    
    .eb-animate-left { animation: ebFadeLeft 0.55s ease both; }
    
    .eb-animate-right { animation: ebFadeRight 0.55s ease both; }
    
    .eb-animate-up { animation: ebFadeUp 0.55s ease both; }
    
    .eb-back {
        
        width: 42px;
        
        height: 42px;
        
        border-radius: 12px;
        
        background: var(--surface-soft);
        
        display: inline-flex;
        
        align-items: center;
        
        justify-content: center;
        
        color: var(--placeholder);
        
        border: none;
        
        transition: all .2s ease;
    
    }
    
    .eb-back:hover { background: rgba(var(--primary-rgb), 0.12); color: var(--ink); }
    
    .eb-title { margin: 0; font-size: 1.875rem; font-weight: 800; color: var(--ink); }
    
    .eb-subtitle { margin: 0.25rem 0 0; color: var(--muted); font-size: 0.95rem; }
    
    .eb-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }
    
    .eb-footer-actions { display: flex; justify-content: stretch; margin-top: 1.25rem; grid-column: 1 / -1; width: 100%; }
    
    .eb-footer-actions .eb-btn { width: 100%; justify-content: center; }
    
    .eb-btn {
        
        border-radius: 1rem;
        
        padding: 0.95rem 1.5rem;
        
        font-weight: 700;
        
        display: inline-flex;
        
        align-items: center;
        
        gap: 0.5rem;
        
        border: 2px solid transparent;
        
        text-decoration: none;
        
        transition: all .2s ease;
        
        cursor: pointer;
    
    }
    
    .eb-btn-ghost {
        
        background: var(--glass-surface-strong);
        
        border-color: var(--line);
        
        color: var(--ink);
    
    }
    
    .eb-btn-ghost:hover { background: var(--surface-soft); }
    
    .eb-btn-primary {
        
        background: var(--primary-deep, #2563eb);
        
        color: #ffffff;
        
        box-shadow: 0 18px 30px rgba(37, 99, 235, 0.35);
        
        font-weight: 800;
        
        letter-spacing: 0.01em;
    
    }
    
    .eb-btn-primary:hover {
        
        transform: translateY(-1px);
        
        box-shadow: 0 22px 36px rgba(37, 99, 235, 0.45);
        
        background: var(--primary, #3b82f6);
    
    }
    
    .eb-btn svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    
    .eb-form { display: grid; gap: 1.5rem; }
    
    @media (min-width: 1024px) {
        
        .eb-form { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        
        .eb-main { grid-column: span 2; }
    
    }
    
    .eb-side { align-content: start; align-items: start; }

    
    .eb-card {
        
        background: var(--glass-surface-strong);
        
        border-radius: 1rem;
        
        border: 1px solid var(--glass-border);
        
        box-shadow: var(--glass-shadow);
        
        overflow: hidden;
        
        transition: transform .2s ease, box-shadow .2s ease;
    
    }
    
    .eb-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
    
    .eb-card--floating { overflow: visible; }
    
    .eb-card-header {
        
        padding: 1rem 1.5rem;
        
        border-bottom: 1px solid var(--line);
        
        display: flex;
        
        align-items: center;
        
        gap: 0.75rem;
        
        background: var(--surface-soft);
    
    }
    
    .eb-card-header--purple { background: var(--surface-soft); }
    
    .eb-card-header--green { background: rgba(34, 197, 94, 0.12); }
    
    .eb-card-icon {
        
        width: 40px;
        
        height: 40px;
        
        border-radius: 12px;
        
        background: var(--primary-deep, #2563eb);
        
        display: inline-flex;
        
        align-items: center;
        
        justify-content: center;
    
    }
    
    .eb-card-icon--purple { background: var(--primary-deep, #2563eb); }
    
    .eb-card-icon--green { background: #10b981; }
    
    .eb-card-icon svg { width: 20px; height: 20px; stroke: #fff; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    
    .eb-card-title { margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--ink); }
    
    .eb-card-body { padding: 1.5rem; display: grid; gap: 1.5rem; }
    
    .eb-card-body--compact { padding-bottom: 1rem; }

    
    .eb-grid-2 { display: grid; gap: 1.5rem; }
    
    @media (min-width: 768px) {
        
        .eb-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    
    }

    
    .eb-label { display: block; font-size: 0.875rem; font-weight: 600; color: var(--muted); margin-bottom: 0.5rem; }

    
    .eb-input,
    
    .eb-textarea,
    
    .eb-select .custom-select-trigger {
        
        width: 100%;
        
        padding: 0.75rem 1rem;
        
        border-radius: 0.75rem;
        
        border: 1px solid var(--line);
        
        background: var(--surface-soft);
        
        color: var(--ink);
        
        font-weight: 600;
        
        transition: border-color .2s ease, box-shadow .2s ease;
        
        box-shadow: none;
    
    }

    .eb-input::placeholder,
    .eb-textarea::placeholder {
        color: var(--placeholder);
        font-weight: 500;
    }
    
    .eb-input--static { background: var(--surface-soft); color: var(--placeholder); cursor: default; }
    
    .eb-input:focus,
    
    .eb-textarea:focus,
    
    .eb-select .custom-select-trigger:focus {
        
        outline: none;
        
        border-color: var(--primary);
        
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.18);
    
    }
    
    .eb-input.is-invalid,
    
    .eb-textarea.is-invalid,
    
    .eb-select .custom-select-trigger.is-invalid {
        
        border-color: rgba(239, 68, 68, 0.55);
        
        background: rgba(239, 68, 68, 0.10);
    
    }
    
    .eb-textarea { resize: none; }

    
    .eb-error { margin-top: 0.5rem; font-size: 0.875rem; color: var(--danger); }

    
    .eb-select .custom-select-trigger { text-align: left; justify-content: space-between; position: relative; }
    
    .eb-select .custom-select-trigger::before {
        
        border-bottom: 2px solid var(--primary);
        
        border-right: 2px solid var(--primary);
    
    }

    
    .eb-brochure-note {
        
        display: flex;
        
        align-items: center;
        
        justify-content: space-between;
        
        gap: 0.75rem;
        
        flex-wrap: wrap;
        
        padding: 0.85rem 1rem;
        
        border-radius: 1rem;
        
        border: 1px solid var(--line);
        
        background: var(--surface-soft);
        
        font-size: 0.9rem;
        
        color: var(--muted);
        
        margin-bottom: 1rem;
    
    }
    
    .eb-brochure-note strong { color: var(--ink); }
    
    .eb-brochure-links { display: inline-flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }
    
    .eb-brochure-link { color: var(--primary); font-weight: 800; text-decoration: none; }
    
    .eb-brochure-link:hover { text-decoration: underline; }
    
    .eb-brochure-hint { margin: 0.75rem 0 0; font-size: 0.85rem; color: var(--placeholder); }
    
    .eb-brochure-hint strong { color: var(--ink); }
    
    .eb-brochure-alert {
        
        display: flex;
        
        align-items: flex-start;
        
        gap: 0.6rem;
        
        padding: 0.85rem 1rem;
        
        border-radius: 1rem;
        
        border: 1px solid rgba(251, 146, 60, 0.35);
        
        background: rgba(245, 158, 11, 0.14);
        
        color: var(--accent);
        
        font-size: 0.9rem;
        
        font-weight: 600;
        
        margin: 0.75rem 0 1rem;
    
    }
    
    .eb-brochure-alert strong { color: var(--accent); }
    
    .eb-brochure-alert-icon {
        
        width: 32px;
        
        height: 32px;
        
        border-radius: 0.75rem;
        
        background: rgba(251, 146, 60, 0.16);
        
        display: grid;
        
        place-items: center;
        
        flex: none;
        
        color: var(--accent);
    
    }
    
    .eb-brochure-alert-icon svg {
        
        width: 18px;
        
        height: 18px;
        
        stroke: currentColor;
        
        fill: none;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
        
        display: block;
    
    }

    
    .eb-upload {
        
        border: 2px dashed var(--line);
        
        border-radius: 0.95rem;
        
        padding: 1.25rem;

        background: var(--surface-soft);
        
        text-align: center;
        
        transition: border-color .2s ease;
    
    }
    
    .eb-upload:hover { border-color: var(--primary); }
    
    .eb-upload-input { position: absolute; opacity: 0; width: 1px; height: 1px; overflow: hidden; }
    
    .eb-upload-label { cursor: pointer; display: block; }
    
    .eb-upload-icon {
        
        width: 48px;
        
        height: 48px;
        
        border-radius: 0.95rem;
        
        background: var(--primary-deep, #2563eb);
        
        display: grid;
        
        place-items: center;
        
        margin: 0 auto 0.75rem;
    
    }
    
    .eb-upload-icon svg { width: 22px; height: 22px; stroke: #ffffff; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }
    
    .eb-upload-name { margin: 0; font-size: 0.85rem; font-weight: 700; color: var(--muted); }
    
    .eb-upload-btn {
        
        margin-top: 0.85rem;
        
        border: none;
        
        border-radius: 0.95rem;
        
        padding: 0.55rem 1.25rem;
        
        font-weight: 800;
        
        background: var(--primary-deep, #2563eb);
        
        color: #ffffff;
        
        cursor: pointer;
        
        box-shadow: 0 12px 20px -10px rgba(37, 99, 235, 0.45);
        
        transition: transform .15s ease, box-shadow .2s ease;
    
    }
    
    .eb-upload-btn:hover { transform: scale(1.05); box-shadow: 0 16px 26px -12px rgba(37, 99, 235, 0.55); }
    
    .eb-upload-btn:active { transform: scale(0.95); }

.eb-reminder {
        
        background: rgba(var(--primary-rgb), 0.12);
        
        border: 1px solid rgba(var(--primary-rgb), 0.25);
        
        border-radius: 1rem;
        
        padding: 1.5rem;
    
    }
    
    .eb-reminder { transition: transform .2s ease, box-shadow .2s ease; }
    
    .eb-reminder:hover { transform: translateY(-2px); box-shadow: 0 18px 28px rgba(37, 99, 235, 0.12); }
    
    .eb-reminder-title { margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 700; color: var(--ink); }
    
    .eb-reminder-text { margin: 0 0 1rem; font-size: 0.9rem; color: var(--muted); }
    
    .eb-reminder-dots { display: flex; gap: 0.5rem; }
    
    .eb-dot { width: 8px; height: 8px; border-radius: 999px; background: var(--primary); animation: ebPulse 1.2s ease-in-out infinite; }
    
    .eb-dot:nth-child(2) { background: #6366f1; animation-delay: .2s; }
    
    .eb-dot:nth-child(3) { background: #8b5cf6; animation-delay: .4s; }
    
    @keyframes ebPulse { 0%, 100% { opacity: .5; transform: scale(1); } 50% { opacity: 1; transform: scale(1.1); } }

    
    .eb-log { padding: 1.5rem; }
    
    .eb-log-row { display: flex; justify-content: space-between; font-size: 0.9rem; }
    
    .eb-log-row span:first-child { color: var(--muted); }
    
    .eb-log-row span:last-child { color: var(--ink); font-weight: 600; }
    
    .eb-draft-note {
        
        display: none;
        
        align-items: center;
        
        gap: 0.6rem;
        
        padding: 0.9rem 1rem;
        
        border-radius: 1rem;
        
        border: 1px solid rgba(var(--primary-rgb), 0.25);
        
        background: rgba(var(--primary-rgb), 0.12);
        
        color: var(--primary);
        
        font-size: 0.9rem;
        
        font-weight: 700;
    
    }
    
    .eb-draft-note.is-visible { display: flex; }
    
    .eb-draft-note svg {
        
        width: 18px;
        
        height: 18px;
        
        stroke: currentColor;
        
        fill: none;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
        
        flex: none;
    
    }
    
    @media (prefers-reduced-motion: reduce) {
        
        .eb-animate-down,
        
        .eb-animate-left,
        
        .eb-animate-right,
        
        .eb-animate-up { animation: none !important; transform: none !important; }
        
        .eb-card,
        
        .eb-reminder { transition: none !important; }
    
    }

</style>

<div class="page-shell page-edit-store">
    
    <div class="eb-shell">
        
        <div class="eb-header eb-animate-down">
            
            <div class="eb-header-left">
                
                <a href="{{ route('stores.index', [], false) }}" class="eb-back" aria-label="Back">
                    
                    <svg viewBox="0 0 24 24">
                        
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>
                    
                    </svg>
                
                </a>
                
                <div>
                    
                    <h1 class="eb-title">Edit Branch</h1>
                    
                    <p class="eb-subtitle">Update branch information</p>
                
                </div>
            
            </div>
            
            <div class="eb-actions"></div>
        
        </div>

        
        <div class="eb-draft-note" id="eb-draft-note" role="status" aria-live="polite">
            
            <svg viewBox="0 0 24 24">
                
                <path d="M12 8v4l3 3"></path>
                
                <circle cx="12" cy="12" r="9"></circle>
            
            </svg>
            
            <span>Draft restored after refresh.</span>
        
        </div>

        
        <form id="edit-store-form" action="{{ route('stores.update', $store, false) }}" method="POST" enctype="multipart/form-data" novalidate data-store-form class="eb-form">
            
            @csrf
            
            @method('PUT')

            
            <div class="eb-main eb-animate-left" style="display:grid; gap:1.5rem; animation-delay: 0.05s;">
                
                <div class="eb-card eb-card--floating">
                    
                    <div class="eb-card-header">
                        
                        <div class="eb-card-icon">
                            
                            <svg viewBox="0 0 24 24">
                                
                                <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path>
                                
                                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                                
                                <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path>
                                
                                <path d="M2 7h20"></path>
                                
                                <path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"></path>
                            
                            </svg>
                        
                        </div>
                        
                        <h2 class="eb-card-title">Branch Details</h2>
                    
                    </div>
                    
                    <div class="eb-card-body">
                        
                        <div class="eb-grid-2">
                            
                            <div>
                                
                                <label class="eb-label">Store name</label>
                                
                                <input
                                    
                                    type="text"
                                    
                                    name="name"
                                    
                                    class="eb-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    
                                    value="{{ $storeNameValue }}"
                                    
                                    required
                                
                                >
                                
                                @error('name')
                                    
                                    <div class="eb-error">{{ $message }}</div>
                                
                                @enderror
                            
                            </div>
                            
                            <div>
                                
                                <label class="eb-label">Branch code</label>
                                
                                <input
                                    
                                    type="text"
                                    
                                    name="branch_code"
                                    
                                    class="eb-input {{ $errors->has('branch_code') ? 'is-invalid' : '' }}"
                                    
                                    value="{{ old('branch_code', $store->branch_code) }}"
                                    
                                    required
                                
                                >
                                
                                @error('branch_code')
                                    
                                    <div class="eb-error">{{ $message }}</div>
                                
                                @enderror
                            
                            </div>
                            
                            <div>
                                
                                <label class="eb-label">Start time</label>
                                
                                <input
                                    
                                    type="time"
                                    
                                    name="workday_starts_at"
                                    
                                    class="eb-input {{ $errors->has('workday_starts_at') ? 'is-invalid' : '' }}"
                                    
                                    value="{{ $workdayStarts }}"
                                    
                                    required
                                
                                >
                                
                                @error('workday_starts_at')
                                    
                                    <div class="eb-error">{{ $message }}</div>
                                
                                @enderror
                            
                            </div>
                            
                            <div>
                                
                                <label class="eb-label">End time</label>
                                
                                <input
                                    
                                    type="time"
                                    
                                    name="workday_ends_at"
                                    
                                    class="eb-input {{ $errors->has('workday_ends_at') ? 'is-invalid' : '' }}"
                                    
                                    value="{{ $workdayEnds }}"
                                    
                                    required
                                
                                >
                                
                                @error('workday_ends_at')
                                    
                                    <div class="eb-error">{{ $message }}</div>
                                
                                @enderror
                            
                            </div>
                            
                            <div>
                                
                                <label class="eb-label">Province</label>
                                
                                @php
                                    
                                    $provinceHasError = $errors->has('province_id');
                                
                                @endphp
                                
                                @include('partials.custom-select-hidden', [
                                    
                                    'name' => 'province_id',
                                    
                                    'options' => $provinceOptions,
                                    
                                    'selectedValue' => (string) $currentProvinceId,
                                    
                                    'selectedLabel' => $provinceSelectedLabel,
                                    
                                    'wrapperClass' => 'custom-select eb-select',
                                    
                                    'triggerClass' => 'custom-select-trigger',
                                    
                                    'hasError' => $provinceHasError,
                                
                                ])
                                
                                @error('province_id')
                                    
                                    <div class="eb-error">{{ $message }}</div>
                                
                                @enderror
                            
                            </div>
                            
                            <div>
                                
                                <label class="eb-label">Status</label>
                                
                                @php
                                    
                                    $statusHasError = $errors->has('status');
                                
                                @endphp
                                
                                @include('partials.custom-select-hidden', [
                                    
                                    'name' => 'status',
                                    
                                    'options' => $statusOptions,
                                    
                                    'selectedValue' => (string) $currentStatus,
                                    
                                    'selectedLabel' => $statusSelectedLabel,
                                    
                                    'wrapperClass' => 'custom-select eb-select',
                                    
                                    'triggerClass' => 'custom-select-trigger',
                                    
                                    'hasError' => $statusHasError,
                                
                                ])
                                
                                @error('status')
                                    
                                    <div class="eb-error">{{ $message }}</div>
                                
                                @enderror
                            
                            </div>
                        
                        </div>

                        
                        <div>
                            
                            <label class="eb-label">Detailed address</label>
                            
                            <textarea
                                
                                name="address"
                                
                                rows="3"
                                
                                class="eb-textarea {{ $errors->has('address') ? 'is-invalid' : '' }}"
                                
                                required
                            
                            >{{ $addressValue }}</textarea>
                            
                            @error('address')
                                
                                <div class="eb-error">{{ $message }}</div>
                            
                            @enderror
                        
                        </div>
                    
                    </div>
                
                </div>

                
                <div class="eb-card">
                    
                    <div class="eb-card-header eb-card-header--purple">
                        
                        <div class="eb-card-icon eb-card-icon--purple">
                            
                            <svg viewBox="0 0 24 24">
                                
                                <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 0 1 16 0Z"></path>
                                
                                <circle cx="12" cy="10" r="3"></circle>
                            
                            </svg>
                        
                        </div>
                        
                        <h2 class="eb-card-title">Contact &amp; Description</h2>
                    
                    </div>
                    
                    <div class="eb-card-body">
                    
                    @include('stores.partials.store-contact-fields', [
                        
                        'gridClass' => 'eb-grid-2',
                        
                        'labelClass' => 'eb-label',
                        
                        'inputClass' => 'eb-input',
                        
                        'textareaClass' => 'eb-textarea',
                        
                        'errorClass' => 'eb-error',
                        
                        'phoneValue' => $phoneValue,
                        
                        'emailValue' => old('email', $store->email),
                        
                        'openingDateValue' => $openingDate,
                        
                        'descriptionValue' => old('description', $store->description),
                        
                        'descriptionRows' => 4,
                    
                    ])
                    
                    </div>
                
                </div>
            
            </div>

            
            <div class="eb-side eb-animate-right" style="display:grid; gap:1.5rem; animation-delay: 0.1s;">
                
                @if ($canEditManager)
                    
                    <div class="eb-card eb-card--floating">
                        
                        <div class="eb-card-header eb-card-header--green">
                            
                            <div class="eb-card-icon eb-card-icon--green">
                                
                                <svg viewBox="0 0 24 24">
                                    
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    
                                    <circle cx="9" cy="7" r="4"></circle>
                                    
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                
                                </svg>
                            
                            </div>
                            
                            <h2 class="eb-card-title">Store Manager</h2>
                        
                        </div>
                        
                        <div class="eb-card-body">
                            
                            @php
                                
                                $managerHasError = $errors->has('manager_id');
                            
                            @endphp
                            
                            @include('partials.custom-select-hidden', [
                                
                                'name' => 'manager_id',
                                
                                'options' => $managerOptions,
                                
                                'selectedValue' => (string) $currentManagerId,
                                
                                'selectedLabel' => $managerSelectedLabel,
                                
                                'selectedLabelHtml' => $managerSelectedLabelHtml,
                                
                                'wrapperClass' => 'custom-select eb-select',
                                
                                'triggerClass' => 'custom-select-trigger',
                                
                                'hasError' => $managerHasError,
                            
                            ])
                            
                            @error('manager_id')
                                
                                <div class="eb-error">{{ $message }}</div>
                            
                            @enderror
                        
                        </div>
                    
                    </div>
                
                @endif

                
                <div class="eb-card eb-card--floating">
                    
                    <div class="eb-card-header eb-card-header--purple">
                        
                        <div class="eb-card-icon eb-card-icon--purple">
                            
                            <svg viewBox="0 0 24 24">
                                
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                
                                <polyline points="17 8 12 3 7 8"></polyline>
                                
                                <line x1="12" x2="12" y1="3" y2="15"></line>
                            
                            </svg>
                        
                        </div>
                        
                        <h2 class="eb-card-title">Store Brochure</h2>
                    
                    </div>
                    
                    <div class="eb-card-body eb-card-body--compact">
                        
                        @php
                            
                            $currentBrochureName = $store->brochure_path ? basename($store->brochure_path) : '';
                        
                        @endphp
                        
                        <input
                            
                            type="hidden"
                            
                            data-eb-brochure-note
                            
                            data-current-name="{{ $currentBrochureName }}"
                            
                            data-has-current="{{ $currentBrochureName !== '' ? '1' : '0' }}"
                        
                        >
                        
                        <div class="eb-upload">
                            
                            <input type="file" name="brochure" id="eb-brochure" class="eb-upload-input" accept="application/pdf">
                            
                            <input type="hidden" name="brochure_path" id="eb-brochure-path" value="{{ old('brochure_path', $store->brochure_path) }}">
                            
                            <label for="eb-brochure" class="eb-upload-label">
                                
                                <div class="eb-upload-icon" aria-hidden="true">
                                    
                                    <svg viewBox="0 0 24 24">
                                        
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        
                                        <line x1="12" x2="12" y1="3" y2="15"></line>
                                    
                                    </svg>
                                
                                </div>
                                
                                <p class="eb-upload-name" id="eb-brochure-file-name">{{ $currentBrochureName !== '' ? $currentBrochureName : 'No file selected yet' }}</p>
                                
                                <button type="button" class="eb-upload-btn" data-eb-brochure-trigger>Choose another file</button>
                            
                            </label>
                        
                        </div>
                        
                        <p class="eb-brochure-hint" data-eb-brochure-hint hidden></p>
                        
                        <div class="eb-brochure-hint" id="eb-brochure-progress" hidden></div>
                        
                        @error('brochure')
                            
                            <div class="eb-error" role="alert">{{ $message }}</div>
                        
                        @enderror
                    
                    </div>
                
                </div>

                
                <div class="eb-footer-actions eb-animate-up" style="animation-delay: 0.15s;">
                    
                    <button type="submit" class="eb-btn eb-btn-primary">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"></path>
                            
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            
                            <polyline points="7 3 7 8 15 8"></polyline>
                        
                        </svg>
                        
                        Save Changes
                    
                    </button>
                
                </div>
            
            </div>

            
            <input type="hidden" name="city" value="{{ old('city', $store->city ?? '') }}">
            
            @foreach ($selectedEmployees as $employeeId)
                
                <input type="hidden" name="employee_ids[]" value="{{ $employeeId }}">
            
            @endforeach

        
        </form>
    
    </div>

</div>

<script>
    
    document.addEventListener('DOMContentLoaded', function () {
        
        const brochureInput = document.getElementById('eb-brochure');
        
        const brochurePathInput = document.getElementById('eb-brochure-path');
        
        const brochureFileName = document.getElementById('eb-brochure-file-name');
        
        const brochureTrigger = document.querySelector('[data-eb-brochure-trigger]');
        
        const brochureNote = document.querySelector('[data-eb-brochure-note]');
        
        const brochureHint = document.querySelector('[data-eb-brochure-hint]');
        
        const brochureProgress = document.getElementById('eb-brochure-progress');
        
        const form = document.getElementById('edit-store-form');
        
        const submitBtn = form ? form.querySelector('button[type=\"submit\"]') : null;
        
        const hasCurrent = brochureNote && brochureNote.dataset.hasCurrent === '1';
        
        const defaultLabel = brochureFileName ? brochureFileName.textContent : '';
        
        const uploadUrl = "{{ route('stores.brochure.uploadChunk', [], false) }}";
        
        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';
        
        const draftNote = document.getElementById('eb-draft-note');
        
        const draftKey = 'store-edit-draft:v1:{{ $store->id }}';
        
        const draftVersion = '{{ $storeDraftVersion }}';
        
        const draftFieldNames = [
            
            'name',
            
            'branch_code',
            
            'workday_starts_at',
            
            'workday_ends_at',
            
            'province_id',
            
            'status',
            
            'address',
            
            'phone',
            
            'email',
            
            'opening_date',
            
            'description',
            
            'manager_id',
            
            'city',
            
            'brochure_path'
        
        ];
        
        let brochureUploading = false;
        
        let draftSaveTimer = null;

        
        function showDraftNote() {
            
            if (!draftNote) {
                
                return;
            
            }

            
            draftNote.classList.add('is-visible');
            
            window.setTimeout(function () {
                
                draftNote.classList.remove('is-visible');
            
            }, 2600);
        
        }

        
        function findDraftField(name) {
            
            return form ? form.querySelector('[name="' + name + '"]') : null;
        
        }

        
        function saveDraftNow() {
            
            if (!form || !window.localStorage) {
                
                return;
            
            }

            
            const values = {};
            
            draftFieldNames.forEach(function (name) {
                
                const field = findDraftField(name);
                
                if (!field) {
                    
                    return;
                
                }

                
                values[name] = field.value ?? '';
            
            });

            
            window.localStorage.setItem(draftKey, JSON.stringify({
                
                version: draftVersion,
                
                savedAt: Date.now(),
                
                values: values,
            
            }));
        
        }

        
        function scheduleDraftSave() {
            
            if (draftSaveTimer) {
                
                window.clearTimeout(draftSaveTimer);
            
            }

            
            draftSaveTimer = window.setTimeout(saveDraftNow, 120);
        
        }

        
        function restoreDraft() {
            
            if (!form || !window.localStorage) {
                
                return;
            
            }

            
            const raw = window.localStorage.getItem(draftKey);
            
            if (!raw) {
                
                return;
            
            }

            
            let payload = null;
            
            try {
                
                payload = JSON.parse(raw);
            
            } catch (error) {
                
                window.localStorage.removeItem(draftKey);
                
                return;
            
            }

            
            if (!payload || payload.version !== draftVersion || typeof payload.values !== 'object' || payload.values === null) {
                
                window.localStorage.removeItem(draftKey);
                
                return;
            
            }

            
            let restored = false;
            
            Object.entries(payload.values).forEach(function ([name, value]) {
                
                const field = findDraftField(name);
                
                if (!field) {
                    
                    return;
                
                }

                
                const normalizedValue = typeof value === 'string' ? value : String(value ?? '');
                
                if ((field.value ?? '') === normalizedValue) {
                    
                    return;
                
                }

                
                field.value = normalizedValue;
                
                field.dispatchEvent(new Event('change', { bubbles: true }));
                
                field.dispatchEvent(new Event('input', { bubbles: true }));
                
                restored = true;
            
            });

            
            if (restored) {
                
                showDraftNote();
            
            }
        
        }

        
        if (form) {
            
            restoreDraft();

            
            draftFieldNames.forEach(function (name) {
                
                const field = findDraftField(name);
                
                if (!field) {
                    
                    return;
                
                }

                
                field.addEventListener('input', scheduleDraftSave);
                
                field.addEventListener('change', scheduleDraftSave);
            
            });

            
            window.addEventListener('beforeunload', saveDraftNow);

            
            form.addEventListener('submit', function (event) {
                
                if (!brochureUploading) {
                    
                    saveDraftNow();
                    
                    return;
                
                }

                
                event.preventDefault();
                
                if (brochureProgress) {
                    
                    brochureProgress.hidden = false;
                    
                    brochureProgress.textContent = 'Please wait until the brochure upload finishes.';
                
                }
            
            });
        
        }

        
        if (brochureInput && brochureFileName) {
            
            brochureInput.addEventListener('change', function () {
                
                const hasFile = brochureInput.files && brochureInput.files.length;
                
                const selectedName = hasFile ? brochureInput.files[0].name : '';

                
                
                brochureFileName.textContent = selectedName !== '' ? selectedName : (defaultLabel || 'No file selected yet');

                
                
                if (brochureHint) {
                    
                    if (selectedName !== '') {
                        
                        brochureHint.hidden = false;
                        
                        brochureHint.textContent = hasCurrent
                            
                            ? 'A new brochure is selected and will replace the current one after you save changes.'
                            
                            : 'A brochure file is selected and will be uploaded after you save changes.';
                    
                    } else {
                        
                        brochureHint.hidden = true;
                        
                        brochureHint.textContent = '';
                    
                    }
                
                }

                
                
                if (!hasFile || !brochurePathInput) {
                    
                    return;
                
                }

                
                const file = brochureInput.files[0];
                
                if (!file) {
                    
                    return;
                
                }

                
                
                brochureInput.removeAttribute('name');
                
                brochurePathInput.value = '';

                
                const uploadId = (window.crypto && window.crypto.randomUUID)
                    
                    ? window.crypto.randomUUID()
                    
                    : (Date.now().toString(36) + '-' + Math.random().toString(16).slice(2));

                
                const chunkSize = 1024 * 1024; // 1MB chunks (safe under common 2MB upload limit)
                
                const totalChunks = Math.max(1, Math.ceil(file.size / chunkSize));

                
                if (submitBtn) {
                    
                    submitBtn.disabled = true;
                
                }
                
                brochureUploading = true;

                
                if (brochureProgress) {
                    
                    brochureProgress.hidden = false;
                    
                    brochureProgress.textContent = 'Uploading brochure... 0%';
                
                }

                
                (async function uploadChunks() {
                    
                    try {
                        
                        for (let i = 0; i < totalChunks; i++) {
                            
                            const start = i * chunkSize;
                            
                            const end = Math.min(file.size, start + chunkSize);
                            
                            const blob = file.slice(start, end);

                            
                            const fd = new FormData();
                            
                            fd.append('upload_id', uploadId);
                            
                            fd.append('chunk_index', String(i));
                            
                            fd.append('total_chunks', String(totalChunks));
                            
                            fd.append('file_name', file.name);
                            
                            fd.append('chunk', blob, file.name);

                            
                            const res = await fetch(uploadUrl, {
                                
                                method: 'POST',
                                
                                headers: {
                                    
                                    'X-CSRF-TOKEN': csrfToken,
                                    
                                    'Accept': 'application/json',
                                
                                },
                                
                                body: fd,
                                
                                credentials: 'same-origin',
                            
                            });

                            
                            let payload = null;
                            
                            try { payload = await res.json(); } catch (e) { payload = null; }

                            
                            if (!res.ok) {
                                
                                const msg = (payload && payload.message) ? payload.message : 'Upload failed. Please try again.';
                                
                                throw new Error(msg);
                            
                            }

                            
                            const percent = Math.round(((i + 1) / totalChunks) * 100);
                            
                            if (brochureProgress) {
                                
                                brochureProgress.textContent = `Uploading brochure... ${percent}%`;
                            
                            }

                            
                            if (payload && payload.complete && payload.brochure_path) {
                                
                                brochurePathInput.value = payload.brochure_path;
                            
                            }
                        
                        }

                        
                        if (brochureProgress) {
                            
                            brochureProgress.textContent = 'Brochure uploaded. Click \"Save Changes\" to apply it.';
                        
                        }
                    
                    } catch (err) {
                        
                        if (brochureProgress) {
                            
                            brochureProgress.textContent = err && err.message ? err.message : 'Upload failed. Please try again.';
                        
                        }
                        
                        
                        brochurePathInput.value = '';
                    
                    } finally {
                        
                        if (submitBtn) {
                            
                            submitBtn.disabled = false;
                        
                        }
                        
                        brochureUploading = false;
                    
                    }
                
                })();
            
            });
        
        }

        
        if (brochureInput && brochureTrigger) {
            
            brochureTrigger.addEventListener('click', function (event) {
                
                event.preventDefault();
                
                brochureInput.click();
            
            });
        
        }
    
    });

</script>

@endsection
