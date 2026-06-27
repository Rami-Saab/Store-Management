@extends('layouts.app')

@section('title', 'Add New Branch')

@section('page_subtitle', '')

@section('content')

@php
    
    
    $currentUser = auth()->user();
    
    
    $isAdmin = $currentUser && $currentUser->role === 'admin';
    
    
    $canAssignStaff = $currentUser?->hasPermission('assign_staff_to_store') ?? false;
    
    $canManageStaff = $currentUser?->hasPermission('manage_store_staff') ?? false;
    
    $canEditAssignments = $isAdmin || $canAssignStaff || $canManageStaff;
    
    $canEditManager = $isAdmin;
    
    
    
    $requiresManager = $canEditManager;

@endphp

@php
    
    
    $statusLabels = $statusLabels ?? [
        
        'active' => 'Active',
        
        'inactive' => 'Inactive',
        
        'under_maintenance' => 'Under maintenance',
    
    ];

    
    
    $currentProvinceId = (string) old('province_id', $store->province_id ?? '');
    
    
    $currentProvince = null;
    
    if (isset($provinces)) {
        $currentProvince = $provinces->firstWhere('id', (int) $currentProvinceId);
    }
    
    
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
            
            $workdayEnds = '';
        
        } else {
            
            $workdayEnds = '';
        
        }
    
    }
    
    $workdayEnds = (string) $workdayEnds;

    
    
    $branchHours = \App\Support\EnglishPlaceNames::branchWorkHoursByCode(old('branch_code', $store->branch_code));
    
    $shouldAutofillHours = (bool) $store->exists;
    
    if ($shouldAutofillHours && $workdayStarts === '' && ($branchHours['start'] ?? '') !== '') {
        
        $workdayStarts = (string) $branchHours['start'];
    
    }
    
    if ($shouldAutofillHours && $workdayEnds === '' && ($branchHours['end'] ?? '') !== '') {
        
        $workdayEnds = (string) $branchHours['end'];
    
    }

    
    
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
    
    $storeNameValue = $storeNameValue;

    
    $addressValue = old('address');
    
    if ($addressValue === null) {
        
        $storedAddress = trim((string) ($store->address ?? ''));
        
        if ($storedAddress !== '') {
            
            $addressValue = $storedAddress;
        
        } else {
            
            $addressValue = '';
        
        }
    
    }

    
    $phoneValue = \App\Support\UserContact::phone(old('phone', $store->phone), false);

    
    $managerIdValue = (string) old('manager_id', '');
    
    $currentManager = null;
    
    if ($managerIdValue !== '') {
        $currentManager = $managers->firstWhere('id', (int) $managerIdValue);
    }

    
    $provinceOptions = $provinces
        
        ->map(fn ($province) => ['value' => $province->id, 'label' => $province->name])
        
        ->all();
    
    $provinceSelectedLabel = 'Select a province';
    
    if ($currentProvince) {
        $provinceSelectedLabel = $currentProvince->name;
    }

    
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
    
    $managerPlaceholder = $requiresManager ? 'Select a manager' : 'No manager assigned';
    
    $managerSelectedLabel = $currentManager ? $currentManager->name : $managerPlaceholder;
    
    $managerSelectedLabelHtml = null;
    
    if ($currentManager) {
        $managerSelectedLabelHtml = '<bdi dir="ltr">'.e($currentManager->name).'</bdi>';
    }

    
    $selectedEmployees = old('employee_ids', []);
    
    $selectedEmployees = array_values(array_unique(array_map('intval', is_array($selectedEmployees) ? $selectedEmployees : [])));
    
    $employeesById = isset($employees) ? $employees->keyBy('id') : collect();

@endphp

<style>
    
    @keyframes abFadeDown {
        
        from { opacity: 0; transform: translateY(-20px); }
        
        to { opacity: 1; transform: translateY(0); }
    
    }

    
    @keyframes abFadeLeft {
        
        from { opacity: 0; transform: translateX(-20px); }
        
        to { opacity: 1; transform: translateX(0); }
    
    }

    
    @keyframes abFadeRight {
        
        from { opacity: 0; transform: translateX(20px); }
        
        to { opacity: 1; transform: translateX(0); }
    
    }

    
    @keyframes abFadeUp {
        
        from { opacity: 0; transform: translateY(20px); }
        
        to { opacity: 1; transform: translateY(0); }
    
    }

    
    .ab-shell { padding: 0.5rem; display: grid; gap: 2rem; }
    
    .ab-header { animation: abFadeDown 0.5s ease both; }
    
    .ab-title { margin: 0 0 0.5rem; font-size: 2.25rem; font-weight: 700; line-height: 1.15; color: var(--ink); }
    
    .ab-subtitle { margin: 0; font-size: 1rem; font-weight: 400; line-height: 1.6; color: var(--muted); }

    
    .ab-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 1.5rem; align-items: start; }
    
    .ab-animate-left { animation: abFadeLeft 0.5s ease both; }
    
    .ab-animate-right { animation: abFadeRight 0.5s ease both; }
    
    .ab-animate-up { animation: abFadeUp 0.5s ease both; }

    
    .ab-card { background: var(--glass-surface-strong); border: 1px solid var(--glass-border); border-radius: 1rem; box-shadow: var(--glass-shadow); }
    
    .ab-card--main { padding: 0; overflow: hidden; border: 1px solid var(--glass-border); }
    
    .ab-card--side { padding: 1.5rem; }

    
    .ab-card-head { display: flex; align-items: center; gap: 0.75rem; margin: 0; }
    
    .ab-card-icon { width: 40px; height: 40px; border-radius: 0.75rem; background: var(--primary-deep, #2563eb); display: grid; place-items: center; flex: none; }
    
    .ab-card-icon--emerald { background: #10b981; }
    
    .ab-card-icon svg { width: 20px; height: 20px; stroke: #ffffff; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }
    
    .ab-card-title { margin: 0; font-size: 1.5rem; font-weight: 800; color: var(--ink); line-height: 1.15; }
    
    .ab-card-subtitle { margin: 0.2rem 0 0; font-size: 0.95rem; color: var(--muted); }

    
    .ab-section-header {
        
        padding: 1.5rem;
        
        border-bottom: 1px solid var(--line);
        
        background: var(--surface-soft);
    
    }

    
    .ab-section-headline {
        
        display: flex;
        
        align-items: center;
        
        gap: 0.75rem;
        
        margin-bottom: 0;
    
    }

    
    .ab-card-body { padding: 2rem; }

    
    .ab-fields { display: grid; gap: 1.5rem; }
    
    .ab-row-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; }
    
    .ab-row-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.5rem; }

    
    .ab-label { display: block; margin: 0 0 0.5rem; font-size: 0.85rem; font-weight: 800; color: var(--muted); }
    
    .ab-input, .ab-select, .ab-textarea { width: 100%; border: 2px solid var(--line); border-radius: 0.85rem; padding: 0.75rem 1rem; outline: none; transition: border-color .2s ease, box-shadow .2s ease; background: var(--surface-soft); color: var(--ink); font-weight: 600; }
    .ab-input::placeholder, .ab-textarea::placeholder { color: var(--placeholder); font-weight: 500; }
    
    .ab-input--static { background: var(--surface-soft); color: var(--placeholder); cursor: default; }
    
    .ab-textarea { resize: none; }
    
    .ab-input:focus, .ab-select:focus, .ab-textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.15); }
    
    .ab-input.is-invalid, .ab-select.is-invalid, .ab-textarea.is-invalid { border-color: var(--danger); box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12); }
    
    .ab-field-error { margin-top: 0.5rem; font-size: 0.85rem; font-weight: 600; color: var(--danger); }
    .ab-field-feedback { margin-top: 0.5rem; font-size: 0.85rem; font-weight: 600; color: var(--muted); }
    .ab-field-feedback[data-state="success"] { color: var(--ink); }
    .ab-field-feedback[data-state="error"] { color: var(--danger); }

    
    .ab-select-wrap { position: relative; }
    
    .ab-select { appearance: none; padding-right: 2.6rem; }
    
    .ab-chevron { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--placeholder); }
    
    .ab-chevron svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }

    
    .ab-time-wrap { position: relative; }
    
    .ab-time-icon { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--placeholder); }
    
    .ab-time-icon svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }
    
    .ab-time-hint { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-size: 0.85rem; color: var(--placeholder); pointer-events: none; }
    
    .ab-time-input { padding-right: 2.6rem; padding-left: 6.5rem; }

    
    .ab-side { display: grid; gap: 1.5rem; }
    
    .ab-side-title { margin: 0 0 1rem; font-size: 1.25rem; font-weight: 800; color: var(--ink); }
    
    .ab-side-sub { margin: -0.5rem 0 1rem; font-size: 0.85rem; color: var(--muted); font-weight: 600; }

    
    .ab-chip-list { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; }
    
    .ab-chip { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0.6rem; border-radius: 999px; background: var(--surface-soft); border: 1px solid var(--line); color: var(--ink); font-weight: 700; font-size: 0.8rem; }
    
    .ab-chip-remove { border: none; background: transparent; color: var(--placeholder); font-size: 1rem; line-height: 1; cursor: pointer; padding: 0; }
    
    .ab-chip-remove:hover { color: var(--ink); }

    
    .ab-upload { border: 2px dashed var(--line); background: var(--surface-soft); border-radius: 0.85rem; padding: 1.5rem; text-align: center; transition: border-color .2s ease; }
    
    .ab-upload:hover { border-color: #3b82f6; }
    
    .ab-upload-input { position: absolute; opacity: 0; width: 1px; height: 1px; overflow: hidden; }
    
    .ab-upload-label { cursor: pointer; display: block; }
    
    .ab-upload-icon { width: 48px; height: 48px; border-radius: 0.85rem; background: var(--primary-deep, #2563eb); display: grid; place-items: center; margin: 0 auto 0.75rem; }
    
    .ab-upload-icon svg { width: 22px; height: 22px; stroke: #ffffff; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }
    
    .ab-upload-name { margin: 0; font-size: 0.85rem; font-weight: 600; color: var(--muted); }
    
    .ab-upload-btn { margin-top: 0.85rem; border: none; border-radius: 0.85rem; padding: 0.55rem 1.25rem; font-weight: 700; background: var(--primary-deep, #2563eb); color: #ffffff; cursor: pointer; box-shadow: 0 12px 20px -10px rgba(37, 99, 235, 0.45); transition: transform .15s ease, box-shadow .2s ease; }
    
    .ab-upload-btn:hover { transform: scale(1.05); box-shadow: 0 16px 26px -12px rgba(37, 99, 235, 0.55); }
    
    .ab-upload-btn:active { transform: scale(0.95); }

    
    .ab-actions { display: flex; gap: 1.5rem; flex-wrap: wrap; justify-content: stretch; margin-top: 1.25rem; animation: abFadeUp 0.5s ease both; animation-delay: 0.3s; }
    
    .ab-actions .ab-btn { width: 100%; }
    
    .ab-btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.95rem 2rem; border-radius: 0.9rem; font-weight: 800; border: 2px solid transparent; transition: transform .15s ease, box-shadow .2s ease, border-color .2s ease; }
    
    .ab-btn--primary { background: var(--primary-deep, #2563eb); color: #ffffff; box-shadow: 0 12px 22px -12px rgba(37, 99, 235, 0.55); }
    
    .ab-btn--secondary { background: var(--glass-surface-strong); color: var(--ink); border-color: var(--line); }
    
    .ab-btn:hover { transform: scale(1.02); }
    
    .ab-btn:active { transform: scale(0.98); }

    
    @media (max-width: 1100px) {
        
        .ab-grid { grid-template-columns: 1fr; }
    
    }

    
    @media (max-width: 820px) {
        
        .ab-row-2 { grid-template-columns: 1fr; }
        
        .ab-row-3 { grid-template-columns: 1fr; }
    
    }

    
    @media (prefers-reduced-motion: reduce) {
        
        .ab-header, .ab-animate-left, .ab-animate-right, .ab-animate-up, .ab-actions { animation: none !important; }
        
        .ab-input, .ab-select, .ab-textarea, .ab-btn, .ab-upload-btn { transition: none !important; }
        
        .ab-btn:hover, .ab-upload-btn:hover { transform: none !important; }
    
    }

</style>

<div class="ab-shell">
    
    <div class="ab-header">
        
        <h1 class="ab-title">Add New Branch</h1>
        
        <p class="ab-subtitle">Add a new branch to the store management system</p>
    
    </div>

    
    <form action="{{ route('stores.store', [], false) }}" method="POST" enctype="multipart/form-data" novalidate data-store-form>
        
        @csrf

        
        <div class="ab-grid">
            
            @php
                
                $timeError = $errors->first('workday_ends_at') ?: $errors->first('workday_starts_at');
            
            @endphp

            
            <div class="ab-card ab-card--main ab-animate-left" style="animation-delay: 0.1s;">
                
                <div class="ab-section-header">
                    
                    <div class="ab-section-headline">
                        
                        <div class="ab-card-icon" aria-hidden="true">
                            
                            <svg viewBox="0 0 24 24">
                                
                                <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 0 1 16 0Z"></path>
                                
                                <circle cx="12" cy="10" r="3"></circle>
                            
                            </svg>
                        
                        </div>
                        
                        <div style="min-width:0;">
                            
                            <h2 class="ab-card-title">Branch Details</h2>
                            
                            <p class="ab-card-subtitle">Fill in the branch information below</p>
                        
                        </div>
                    
                    </div>
                
                </div>

                
                <div class="ab-card-body">
                    
                    <div class="ab-fields">
                    
                    <div class="ab-row-2">
                        
                        <div>
                            
                            <label class="ab-label">Store name</label>
                            
                            <input type="text" name="name" class="ab-input {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ $storeNameValue }}" required>
                            
                            @error('name')
                                
                                <div class="ab-field-error" role="alert">{{ $message }}</div>
                            
                            @enderror
                        
                        </div>
                        
                        <div>
                            
                            <label class="ab-label">Branch code</label>
                            
                            <input type="text" name="branch_code" class="ab-input {{ $errors->has('branch_code') ? 'is-invalid' : '' }}" value="{{ old('branch_code', $store->branch_code) }}" required>
                            
                            @error('branch_code')
                                
                                <div class="ab-field-error" role="alert">{{ $message }}</div>
                            
                            @enderror
                        
                        </div>
                    
                    </div>

                    
                    <div>
                        
                        <label class="ab-label">Business hours</label>
                        
                        <div
                            
                            class="ab-row-2"
                            
                            data-time-format-scope
                            
                            data-shift-min-hours="{{ (int) config('store.shift_min_hours', 8) }}"
                            
                            data-shift-max-hours="{{ (int) (config('store.shift_max_hours') ?? 0) }}"
                        
                        >
                            
                            @include('stores.partials.ab-time-picker', [
                                
                                'name' => 'workday_starts_at',
                                
                                'label' => 'Start time',
                                
                                'value' => $workdayStarts,
                                
                                'display' => $to12Hour($workdayStarts),
                                
                                'hasError' => $errors->has('workday_starts_at') || $errors->has('workday_ends_at'),
                            
                            ])
                            
                            @include('stores.partials.ab-time-picker', [
                                
                                'name' => 'workday_ends_at',
                                
                                'label' => 'End time',
                                
                                'value' => $workdayEnds,
                                
                                'display' => $to12Hour($workdayEnds),
                                
                                'hasError' => $errors->has('workday_starts_at') || $errors->has('workday_ends_at'),
                            
                            ])
                        
                        </div>
                        
                        <div class="ab-field-error mt-2 {{ $timeError ? '' : 'd-none' }}" data-time-error role="alert">
                            
                            {{ $timeError }}
                        
                        </div>
                    
                    </div>

                    
                    <div class="ab-row-2">
                        
                        <div>
                            
                            <label class="ab-label">Province</label>
                            
                            @php
                                
                                $provinceHasError = $errors->has('province_id');
                                
                                $provinceSelectOptions = $provinceOptions;
                                
                                array_unshift($provinceSelectOptions, ['value' => '', 'label' => 'Select a province']);
                            
                            @endphp
                            
                            @include('partials.custom-select-native', [
                                
                                'name' => 'province_id',
                                
                                'options' => $provinceSelectOptions,
                                
                                'menuOptions' => $provinceOptions,
                                
                                'selectedValue' => (string) $currentProvinceId,
                                
                                'selectedLabel' => $provinceSelectedLabel,
                                
                                'wrapperClass' => 'custom-select',
                                
                                'selectClass' => 'ab-select custom-select-native'.($provinceHasError ? ' is-invalid' : ''),
                                
                                'triggerClass' => 'ab-select custom-select-trigger'.($provinceHasError ? ' is-invalid' : ''),
                                
                                'selectAttributes' => 'required',
                            
                            ])
                            
                            @error('province_id')
                                
                                <div class="ab-field-error" role="alert">{{ $message }}</div>
                            
                            @enderror
                        
                        </div>
                        
                        <div>
                            
                            <label class="ab-label">Status</label>
                            
                            @php
                                
                                $statusHasError = $errors->has('status');
                            
                            @endphp
                            
                            @include('partials.custom-select-native', [
                                
                                'name' => 'status',
                                
                                'options' => $statusOptions,
                                
                                'menuOptions' => $statusOptions,
                                
                                'selectedValue' => (string) $currentStatus,
                                
                                'selectedLabel' => $statusSelectedLabel,
                                
                                'wrapperClass' => 'custom-select',
                                
                                'selectClass' => 'ab-select custom-select-native'.($statusHasError ? ' is-invalid' : ''),
                                
                                'triggerClass' => 'ab-select custom-select-trigger'.($statusHasError ? ' is-invalid' : ''),
                                
                                'selectAttributes' => 'required',
                            
                            ])
                            
                            @error('status')
                                
                                <div class="ab-field-error" role="alert">{{ $message }}</div>
                            
                            @enderror
                        
                        </div>
                    
                    </div>

                    
                    <div>
                        
                        <label class="ab-label">Detailed address</label>
                        
                        <textarea name="address" class="ab-textarea {{ $errors->has('address') ? 'is-invalid' : '' }}" rows="4" required>{{ $addressValue }}</textarea>
                        
                        @error('address')
                            
                            <div class="ab-field-error" role="alert">{{ $message }}</div>
                        
                        @enderror
                    
                    </div>

                    
                    @include('stores.partials.store-contact-fields', [
                        
                        'gridClass' => 'ab-row-3',
                        
                        'labelClass' => 'ab-label',
                        
                        'inputClass' => 'ab-input',
                        
                        'textareaClass' => 'ab-textarea',
                        
                        'errorClass' => 'ab-field-error',
                        
                        'errorRole' => 'alert',
                        
                        'phoneValue' => $phoneValue,
                        
                        'emailValue' => old('email', $store->email),
                        
                        'openingDateValue' => old('opening_date', optional($store->opening_date)->format('Y-m-d')),
                        
                        'descriptionValue' => old('description', $store->description),
                        
                        'descriptionRows' => 5,
                    
                    ])

                    
                    <input type="hidden" name="city" value="{{ old('city', $store->city ?? '') }}">
                    
                    </div>
                
                </div>
            
            </div>

            
            <div class="ab-side ab-animate-right" style="animation-delay: 0.2s;">
                
                <div class="ab-card ab-card--side">
                    
                    <h3 class="ab-side-title">Store Brochure</h3>
                    
                    <div class="ab-upload">
                        
                        <input type="file" name="brochure" id="brochure" class="ab-upload-input" accept="application/pdf">
                        
                        <input type="hidden" name="brochure_path" id="brochure_path" value="{{ old('brochure_path') }}">
                        
                        <label for="brochure" class="ab-upload-label">
                            
                            <div class="ab-upload-icon" aria-hidden="true">
                                
                                <svg viewBox="0 0 24 24">
                                    
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    
                                    <line x1="12" x2="12" y1="3" y2="15"></line>
                                
                                </svg>
                            
                            </div>
                            
                            <p class="ab-upload-name" id="brochure-file-name">No file selected yet</p>
                            
                            <button type="button" class="ab-upload-btn">Choose file</button>
                        
                        </label>
                    
                    </div>
                    
                    @error('brochure')
                        
                        <div class="ab-field-error" role="alert">{{ $message }}</div>
                    
                    @enderror
                    
                    <div class="ab-field-feedback" id="brochure-upload-progress" data-state="idle" style="display:none"></div>
                
                </div>

                
                <div class="ab-actions">
                    
                    <button type="submit" class="ab-btn ab-btn--primary" data-explicit-save>Save Branch</button>
                
                </div>
            
            </div>
        
        </div>
    
    </form>

</div>

<script>
    
    document.addEventListener('DOMContentLoaded', function () {
        
        const brochureInput = document.getElementById('brochure');
        
        const brochurePathInput = document.getElementById('brochure_path');
        
        const brochureFileName = document.getElementById('brochure-file-name');
        
        const brochureTrigger = document.querySelector('.ab-upload-btn');
        
        const brochureProgress = document.getElementById('brochure-upload-progress');
        
        const form = document.querySelector('[data-store-form]');
        
        const submitBtn = form ? form.querySelector('[data-explicit-save]') : null;
        
        const uploadUrl = "{{ route('stores.brochure.uploadChunk', [], false) }}";
        
        const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';
        
        let brochureUploading = false;
        function setBrochureProgress(message, state) {
            if (!brochureProgress) {
                return;
            }
            if (!message) {
                brochureProgress.style.display = 'none';
                brochureProgress.textContent = '';
                brochureProgress.dataset.state = 'idle';
                return;
            }
            brochureProgress.style.display = 'block';
            brochureProgress.textContent = message;
            brochureProgress.dataset.state = state || 'info';
        }

        
        if (form) {
            
            form.addEventListener('submit', function (event) {
                
                if (!brochureUploading) {
                    
                    return;
                
                }

                
                event.preventDefault();
                
                setBrochureProgress('Please wait until the brochure upload finishes.', 'info');
            
            });
        
        }

        
        if (brochureInput && brochureFileName) {
            
            brochureInput.addEventListener('change', function () {
                
                const hasFile = brochureInput.files && brochureInput.files.length;
                
                const selectedName = hasFile ? brochureInput.files[0].name : '';

                
                brochureFileName.textContent = selectedName !== '' ? selectedName : 'No file selected yet';

                
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
                            
                            setBrochureProgress('Uploading brochure... 0%', 'info');
                        
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
                                
                                setBrochureProgress(`Uploading brochure... ${percent}%`, 'info');
                            
                            }

                            
                            if (payload && payload.complete && payload.brochure_path) {
                                
                                brochurePathInput.value = payload.brochure_path;
                            
                            }
                        
                        }

                        
                        if (brochureProgress) {
                            
                            setBrochureProgress('Brochure uploaded. You can submit the form now.', 'success');
                        
                        }
                    
                    } catch (err) {
                        
                        if (brochureProgress) {
                            
                            setBrochureProgress(err && err.message ? err.message : 'Upload failed. Please try again.', 'error');
                        
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

        
        const employeeSelect = document.getElementById('ab-employee-select');
        
        const chips = document.getElementById('ab-employee-chips');
        
        const countEl = document.getElementById('ab-employee-count');

        
        if (!employeeSelect || !chips || !countEl) {
            
            return;
        
        }

        
        function updateCount() {
            
            const selectedCount = chips.querySelectorAll('.ab-chip').length;
            
            countEl.textContent = selectedCount + ' selected';
        
        }

        
        function addEmployee(id, label) {
            
            if (!id || document.getElementById('ab-employee-input-' + id)) {
                
                return;
            
            }

            
            const chip = document.createElement('span');
            
            chip.className = 'ab-chip';
            
            chip.dataset.id = id;

            
            const chipLabel = document.createElement('span');
            
            chipLabel.className = 'ab-chip-label';
            
            chipLabel.textContent = label || id;

            
            const removeBtn = document.createElement('button');
            
            removeBtn.type = 'button';
            
            removeBtn.className = 'ab-chip-remove';
            
            removeBtn.setAttribute('aria-label', 'Remove');
            
            removeBtn.textContent = '\u00d7';

            
            chip.appendChild(chipLabel);
            
            chip.appendChild(removeBtn);

            
            const hiddenInput = document.createElement('input');
            
            hiddenInput.type = 'hidden';
            
            hiddenInput.name = 'employee_ids[]';
            
            hiddenInput.value = id;
            
            hiddenInput.id = 'ab-employee-input-' + id;

            
            chips.appendChild(chip);
            
            chips.appendChild(hiddenInput);
            
            updateCount();
        
        }

        
        function removeEmployee(id) {
            
            const chip = chips.querySelector('.ab-chip[data-id="' + id + '"]');
            
            const input = document.getElementById('ab-employee-input-' + id);
            
            if (chip) chip.remove();
            
            if (input) input.remove();
            
            updateCount();
        
        }

        
        employeeSelect.addEventListener('change', function () {
            
            const id = employeeSelect.value;
            
            if (!id) return;

            
            const option = employeeSelect.options[employeeSelect.selectedIndex];
            
            const label = option ? option.textContent.trim() : id;

            
            addEmployee(id, label);
            
            employeeSelect.value = '';
            
            employeeSelect.dispatchEvent(new Event('change', { bubbles: true }));
        
        });

        
        chips.addEventListener('click', function (event) {
            
            const target = event.target;
            
            if (!target) return;

            
            const removeBtn = target.closest('.ab-chip-remove');
            
            if (!removeBtn) return;

            
            const chip = removeBtn.closest('.ab-chip');
            
            const id = chip ? chip.getAttribute('data-id') : null;
            
            if (id) {
                
                removeEmployee(id);
            
            }
        
        });

        
        updateCount();
    
    });

</script>

<script>
    
    document.addEventListener('DOMContentLoaded', function () {
        
        const timeScope = document.querySelector('[data-time-format-scope]');
        
        if (!timeScope) return;

        
        const startInput = timeScope.querySelector('input[name="workday_starts_at"]');
        
        const endInput = timeScope.querySelector('input[name="workday_ends_at"]');

        
        function openTimePicker(input) {
            
            if (!input) return;
            
            input.focus({ preventScroll: true });
            
            if (typeof input.showPicker === 'function') {
                
                try {
                    
                    input.showPicker();
                
                } catch (error) {
                    
                
                }
            
            }
        
        }

        
        timeScope.addEventListener('pointerdown', function (event) {
            
            const trigger = event.target.closest('[data-time-trigger]');
            
            if (!trigger) return;
            
            const input = trigger.querySelector('input[type="time"]');
            
            if (!input) return;
            
            if (event.target !== input) {
                
                event.preventDefault();
                
                openTimePicker(input);
            
            }
        
        });

        
        timeScope.addEventListener('focusin', function (event) {
            
            const input = event.target;
            
            if (input && input.tagName === 'INPUT' && input.type === 'time') {
                
                openTimePicker(input);
            
            }
        
        });

        
        function parseTimeMinutes(value) {
            
            const match = String(value || '').match(/^(\d{2}):(\d{2})$/);
            
            if (!match) return null;
            
            const h = Number(match[1]);
            
            const m = Number(match[2]);
            
            if (Number.isNaN(h) || Number.isNaN(m) || h < 0 || h > 23 || m < 0 || m > 59) return null;
            
            return (h * 60) + m;
        
        }

        
        function setTimeError(message) {
            
            const errorEl = document.querySelector('[data-time-error]');
            
            if (errorEl) {
                
                errorEl.textContent = message || '';
                
                errorEl.classList.toggle('d-none', !message);
            
            }

            
            [startInput, endInput].forEach(function (el) {
                
                if (!el) return;
                
                el.classList.toggle('is-invalid', !!message);
            
            });
        
        }

        
        const form = timeScope.closest('form');
        
        const minHours = Number(timeScope.dataset.shiftMinHours || 8);
        
        const maxHours = Number(timeScope.dataset.shiftMaxHours || 0);
        
        const safeMin = Number.isFinite(minHours) && minHours > 0 ? minHours : 8;
        
        const safeMax = Number.isFinite(maxHours) && maxHours > 0 ? maxHours : 0;
        
        const minMinutes = safeMin * 60;
        
        const maxMinutes = safeMax * 60;

        
        function validateShiftRange(scrollOnError, showMissing) {
            
            const start = parseTimeMinutes(startInput?.value);
            
            const end = parseTimeMinutes(endInput?.value);

            
            if (start === null || end === null) {
                
                if (showMissing || scrollOnError) {
                    
                    setTimeError('Please select both the start time and end time.');
                
                } else {
                    
                    setTimeError('');
                
                }

                
                if (scrollOnError) {
                    
                    timeScope.scrollIntoView({ block: 'center', behavior: 'smooth' });
                
                }
                
                return false;
            
            }

            
            let duration = end - start;
            
            if (duration <= 0) duration += 1440;

            
            if (duration < minMinutes) {
                
                setTimeError('The working time is too short. Business hours must be at least ' + safeMin + ' hours.');
                
                if (scrollOnError) {
                    
                    timeScope.scrollIntoView({ block: 'center', behavior: 'smooth' });
                
                }
                
                return false;
            
            }

            
            if (maxMinutes > 0 && duration > maxMinutes) {
                
                setTimeError('The working time is too long. Business hours must not exceed ' + safeMax + ' hours.');
                
                if (scrollOnError) {
                    
                    timeScope.scrollIntoView({ block: 'center', behavior: 'smooth' });
                
                }
                
                return false;
            
            }

            
            setTimeError('');
            
            return true;
        
        }

        
        startInput?.addEventListener('change', function () {
            
            validateShiftRange(false, true);
        
        });

        
        endInput?.addEventListener('change', function () {
            
            validateShiftRange(false, true);
        
        });

        
        const errorEl = document.querySelector('[data-time-error]');
        
        const hasServerTimeError = !!(errorEl && !errorEl.classList.contains('d-none') && String(errorEl.textContent || '').trim());
        
        if (hasServerTimeError) {
            
            validateShiftRange(false, true);
        
        }

        
        form?.addEventListener('submit', function (event) {
            
            if (!validateShiftRange(true, true)) {
                
                event.preventDefault();
            
            }
        
        });
    
    });

</script>

@endsection
