@extends('layouts.app')

@section('title', 'Assign Staff')

@section('page_subtitle', '')

@section('content')

@php
    
    
    $currentUser = auth()->user();
    
    $isSystemAdmin = $currentUser && $currentUser->role === 'admin';
    
    $isStoreManager = $currentUser && $currentUser->role === 'store_manager';
    
    $canManageBranchSelection = $isSystemAdmin;
    
    
    $selectedEmployees = old('employee_ids', $store->employees->pluck('id')->all());
    
    $selectedEmployees = array_values(array_unique(array_map('intval', is_array($selectedEmployees) ? $selectedEmployees : [])));

    
    
    $removedEmployees = old('removed_employee_ids', []);
    
    $removedEmployees = array_values(array_unique(array_map('intval', is_array($removedEmployees) ? $removedEmployees : [])));
    
    $removedEmployeeSet = array_fill_keys($removedEmployees, true);

    
    
    $employeesById = $employees->keyBy('id');
    
    $allowedEmployeeIds = $employees->pluck('id')->map(fn ($id) => (int) $id)->all();
    
    $selectedEmployees = array_values(array_intersect($selectedEmployees, $allowedEmployeeIds));
    
    $selectedEmployeeSet = array_fill_keys($selectedEmployees, true);
    
    $vacantCount = $employees->filter(function ($employee) {
        
        return $employee->stores->isEmpty() && ! $employee->assignedStore;
    
    })->count();
    
    
    $storeLabel = \App\Support\EnglishPlaceNames::branchDisplayName($store->branch_code, $store->name);
    
    
    $storeNameById = $stores
        
        ->mapWithKeys(function ($item) {
            
            return [$item->id => \App\Support\EnglishPlaceNames::branchDisplayName($item->branch_code, $item->name)];
        
        })
        
        ->all();

    
    
    $assignedManager = $store->manager;
    
    $managerIdValue = old('manager_id', $assignedManager?->id ?? '');
    
    
    $managerDisplay = function ($manager) use ($storeNameById) {
        
        if (! $manager) {
            
            return ['label' => 'Select a manager', 'label_html' => null];
        
        }

        
        $managerStore = $manager->managedStore ?? $manager->assignedStore;
        
        $managerStoreLabel = $managerStore
            
            ? ($storeNameById[$managerStore->id] ?? \App\Support\EnglishPlaceNames::branchDisplayName($managerStore->branch_code, $managerStore->name))
            
            : 'Vacant manager';
        
        $descPrefix = $managerStore ? 'Manages:' : '';
        
        $descText = $managerStore ? ($descPrefix.' '.$managerStoreLabel) : $managerStoreLabel;
        
        $descClass = $managerStore ? 'as-manager-desc as-manager-desc--assigned' : 'as-manager-desc as-manager-desc--vacant';

        
        return [
            
            'label' => $manager->name,
            
            'label_html' => '<div class="as-manager-option"><span class="as-manager-name">'.e($manager->name).'</span><span class="'.$descClass.'">'.e($descText).'</span></div>',
        
        ];
    
    };
    
    $managerDisplayById = $managers
        
        ->mapWithKeys(function ($manager) use ($managerDisplay) {
            
            return [(int) $manager->id => $managerDisplay($manager)];
        
        })
        
        ->all();
    
    $currentManagerName = $managerIdValue !== ''
        
        ? ($managerDisplayById[(int) $managerIdValue]['label'] ?? 'Select a manager')
        
        : 'Select a manager';
    
    $currentManagerLabelHtml = $managerIdValue !== ''
        
        ? ($managerDisplayById[(int) $managerIdValue]['label_html'] ?? null)
        
        : null;
    
    
    $limitedManagerName = $currentUser?->name ?: ($assignedManager?->name ?? 'Unassigned');
    
    $limitedManagerId = $currentUser?->id ?? ($assignedManager?->id ?? '');
    
    
    $limitedStore = $currentUser
        
        ? ($currentUser->managedStore ?: $currentUser->stores()->first())
        
        : null;
    
    $limitedStoreLabel = $limitedStore
        
        ? \App\Support\EnglishPlaceNames::branchDisplayName($limitedStore->branch_code, $limitedStore->name)
        
        : $storeLabel;

    
    
    $storeOptions = $stores
        
        ->map(function ($item) {
            
            $itemLabel = \App\Support\EnglishPlaceNames::branchDisplayName($item->branch_code, $item->name);
            
            return ['value' => $item->id, 'label' => $itemLabel];
        
        })
        
        ->all();

    
    
    $managerOptions = collect([['value' => '', 'label' => 'Select a manager']])
        
        ->merge($managers->map(function ($manager) use ($managerDisplay) {
            
            return array_merge(['value' => $manager->id], $managerDisplay($manager));
        
        }))
        
        ->all();

@endphp

<style>
    
    @keyframes asFadeDown {
        
        from {
            
            opacity: 0;
            
            transform: translateY(-20px);
        
        }

        
        to {
            
            opacity: 1;
            
            transform: translateY(0);
        
        }
    
    }

    
    @keyframes asFadeLeft {
        
        from {
            
            opacity: 0;
            
            transform: translateX(-20px);
        
        }

        
        to {
            
            opacity: 1;
            
            transform: translateX(0);
        
        }
    
    }

    
    @keyframes asFadeRight {
        
        from {
            
            opacity: 0;
            
            transform: translateX(20px);
        
        }

        
        to {
            
            opacity: 1;
            
            transform: translateX(0);
        
        }
    
    }

    
    @keyframes asFadeUp {
        
        from {
            
            opacity: 0;
            
            transform: translateY(20px);
        
        }

        
        to {
            
            opacity: 1;
            
            transform: translateY(0);
        
        }
    
    }

    
    .as-shell {
        
        padding: 0.5rem;
        
        display: grid;
        
        gap: 2rem;
    
    }

    
    .as-header {
        
        animation: asFadeDown 0.5s ease both;
    
    }

    
    .as-title {
        
        margin: 0 0 0.5rem;
        
        font-size: 2.25rem;
        
        font-weight: 700;
        
        line-height: 1.15;
        
        color: var(--ink);
    
    }

    
    .as-subtitle {
        
        margin: 0;
        
        font-size: 1rem;
        
        font-weight: 400;
        
        line-height: 1.6;
        
        color: var(--muted);
    
    }

    
    .as-grid {
        
        display: grid;
        
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        
        gap: 1.5rem;
        
        align-items: start;
    
    }

    
    .as-animate-left {
        
        animation: asFadeLeft 0.5s ease both;
    
    }

    
    .as-animate-right {
        
        animation: asFadeRight 0.5s ease both;
    
    }

    
    .as-card {
        
        background: var(--glass-surface-strong);
        
        border: 1px solid var(--glass-border);
        
        border-radius: 1rem;
        
        padding: 1.5rem;
        
        box-shadow: var(--glass-shadow);
    
    }

    
    .as-card--list {
        
        padding: 0;
        
        overflow: hidden;
        
        border: 1px solid var(--glass-border);
    
    }

    
    .as-list-header {
        
        padding: 1.5rem;
        
        border-bottom: 1px solid var(--line);
        
        background: var(--surface-soft);
    
    }

    
    .as-list-head {
        
        display: flex;
        
        align-items: center;
        
        justify-content: flex-start;
        
        gap: 1rem;
        
        margin-bottom: 1rem;
    
    }

    
    .as-list-title {
        
        display: flex;
        
        align-items: center;
        
        gap: 0.75rem;
        
        min-width: 0;
    
    }

    
    .as-list-mark {
        
        width: 40px;
        
        height: 40px;
        
        border-radius: 0.65rem;
        
        display: grid;
        
        place-items: center;
        
        flex: none;
        
        background: #10b981;
    
    }

    
    .as-list-mark svg {
        
        width: 20px;
        
        height: 20px;
        
        stroke: #ffffff;
        
        fill: none;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
        
        display: block;
    
    }

    
    .as-list-heading {
        
        margin: 0;
        
        font-size: 1.5rem;
        
        font-weight: 700;
        
        color: var(--ink);
        
        line-height: 1.2;
    
    }

    
    .as-selected-count {
        
        font-size: 0.875rem;
        
        color: var(--muted);
        
        display: inline-flex;
        
        align-items: center;
        
        gap: 0.4rem;
    
    }

    
    .as-selected-divider {
        
        color: var(--line);
    
    }

    
    .as-selected-vacant {
        
        font-weight: 600;
        
        color: #0f766e;
    
    }

    
    .as-search {
        
        position: relative;
        
        margin: 0;
    
    }

    
    .as-search-icon {
        
        position: absolute;
        
        right: 1rem;
        
        top: 50%;
        
        transform: translateY(-50%);
        
        width: 20px;
        
        height: 20px;
        
        color: var(--placeholder);
        
        pointer-events: none;
    
    }

    
    .as-search-icon svg {
        
        width: 20px;
        
        height: 20px;
        
        stroke: currentColor;
        
        fill: none;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
        
        display: block;
    
    }

    
    .as-search-input {
        
        width: 100%;
        
        border: 1px solid var(--line);
        
        border-radius: 0.75rem;
        
        padding: 0.75rem 3rem 0.75rem 1rem;
        
        outline: none;
        
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        
        color: var(--ink);
        
        background: var(--surface-soft);
    
    }
    .as-search-input::placeholder {
        color: var(--placeholder);
    }

    
    .as-search-input:focus {
        
        border-color: var(--success);
        
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.18);
    
    }

    
    .as-list-body {
        
        padding: 1.5rem;
    
    }

    
    .as-staff-grid {
        
        display: grid;
        
        grid-template-columns: repeat(2, minmax(0, 1fr));
        
        gap: 1rem;
    
    }

    
    .as-staff-card {
        
        width: 100%;
        
        text-align: left;
        
        border: 2px solid var(--line);
        
        border-radius: 0.75rem;
        
        padding: 1.25rem;
        
        background: var(--glass-surface-strong);
        
        cursor: pointer;
        
        transition: border-color 0.2s ease, background 0.2s ease, transform 0.15s ease;
        
        display: grid;
        
        gap: 0.9rem;
    
    }

    
    .as-staff-animate {
        
        animation: asFadeUp 0.45s ease both;
    
    }

    
    .as-staff-card:hover {
        
        border-color: rgba(34, 197, 94, 0.55);
        
        transform: scale(1.02);
    
    }

    
    .as-staff-card:active {
        
        transform: scale(0.99);
    
    }

    
    .as-staff-card.is-selected {
        
        border-color: var(--success);
        
        background: rgba(34, 197, 94, 0.12);
    
    }

    
    .as-staff-top {
        
        display: flex;
        
        align-items: flex-start;
        
        gap: 0.9rem;
        
        min-width: 0;
    
    }

    
    .as-avatar {
        
        width: 48px;
        
        height: 48px;
        
        border-radius: 999px;
        
        display: grid;
        
        place-items: center;
        
        flex: none;
        
        font-weight: 800;
        
        color: #ffffff;
        
        background: rgba(148, 163, 184, 0.55);
    
    }

    
    .as-staff-card.is-selected .as-avatar {
        
        background: #10b981;
    
    }

    
    .as-staff-name {
        
        margin-bottom: 0.2rem;
        
        font-weight: 800;
        
        color: var(--ink);
        
        line-height: 1.2;
        
        white-space: nowrap;
        
        overflow: hidden;
        
        text-overflow: ellipsis;
    
    }

    
    .as-staff-role {
        
        font-size: 0.875rem;
        
        color: var(--muted);
        
        margin: 0 0 0.75rem;
        
        white-space: nowrap;
        
        overflow: hidden;
        
        text-overflow: ellipsis;
    
    }

    
    .as-meta {
        
        display: grid;
        
        gap: 0.5rem;
        
        color: var(--muted);
        
        font-size: 0.75rem;
    
    }

    
    .as-meta-row {
        
        display: flex;
        
        align-items: center;
        
        gap: 0.45rem;
        
        min-width: 0;
    
    }

    
    .as-meta-row svg {
        
        width: 12px;
        
        height: 12px;
        
        stroke: currentColor;
        
        fill: none;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
        
        flex: none;
    
    }

    
    .as-meta-row span {
        
        white-space: nowrap;
        
        overflow: hidden;
        
        text-overflow: ellipsis;
    
    }

    
    .as-branch-pill {
        
        display: inline-flex;
        
        align-items: center;
        
        padding: 0.25rem 0.5rem;
        
        border-radius: 0.5rem;
        
        font-size: 0.75rem;
        
        font-weight: 500;
        
        width: fit-content;
    
    }

    
    .as-branch-pill--vacant {
        
        background: rgba(34, 197, 94, 0.14);
        
        color: var(--success);
    
    }

    
    .as-branch-pill--assigned {
        
        background: rgba(var(--primary-rgb), 0.14);
        
        color: var(--primary);
    
    }

    
    .as-manager-option {
        
        display: grid;
        
        gap: 0.2rem;
        
        width: 100%;
    
    }

    
    .as-manager-name {
        
        font-weight: 700;
        
        color: var(--ink);
        
        min-width: 0;
        
        overflow: hidden;
        
        text-overflow: ellipsis;
        
        white-space: nowrap;
    
    }

    
    .as-manager-desc {
        
        font-size: 0.75rem;
        
        font-weight: 600;
        
        color: var(--placeholder);
        
        white-space: nowrap;
    
    }

    
    .as-manager-desc--assigned {
        
        color: var(--success);
    
    }

    
    .as-manager-desc--vacant {
        
        color: var(--success);
    
    }

    
    .as-side {
        
        display: grid;
        
        gap: 1.5rem;
    
    }

    
    .as-side-actions {
        
        display: grid;
        
        gap: 1.5rem;
    
    }

    
    [data-removed-inputs] {
        
        display: contents;
    
    }

    
    .as-side-title {
        
        margin: 0 0 1rem;
        
        font-size: 1.25rem;
        
        font-weight: 700;
        
        color: var(--ink);
    
    }

    
    .as-empty {
        
        text-align: center;
        
        padding: 2rem 1rem;
        
        color: var(--muted);
    
    }

    
    .as-empty-icon {
        
        width: 64px;
        
        height: 64px;
        
        border-radius: 999px;
        
        background: var(--surface-soft);
        
        display: grid;
        
        place-items: center;
        
        margin: 0 auto 0.75rem;
        
        color: var(--placeholder);
    
    }

    
    .as-empty-icon svg {
        
        width: 32px;
        
        height: 32px;
        
        stroke: currentColor;
        
        fill: none;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
        
        display: block;
    
    }

    
    .as-empty-text {
        
        margin: 0;
        
        font-size: 0.9rem;
    
    }

    
    .as-selected-list {
        
        display: grid;
        
        gap: 0.6rem;
    
    }

    
    .as-selected-item {
        
        display: flex;
        
        align-items: center;
        
        gap: 0.75rem;
        
        padding: 0.75rem;
        
        border-radius: 0.5rem;
        
        background: var(--surface-soft);
    
    }

    
    .as-selected-avatar {
        
        width: 32px;
        
        height: 32px;
        
        border-radius: 999px;
        
        display: grid;
        
        place-items: center;
        
        flex: none;
        
        color: #ffffff;
        
        font-weight: 800;
        
        font-size: 0.85rem;
        
        background: #10b981;
    
    }

    
    .as-selected-name {
        
        font-size: 0.875rem;
        
        font-weight: 500;
        
        color: var(--ink);
        
        line-height: 1.2;
    
    }

    
    .as-selected-role {
        
        font-size: 0.75rem;
        
        color: var(--muted);
        
        margin-top: 0.1rem;
    
    }

    
    .as-field-label {
        
        display: block;
        
        font-size: 0.875rem;
        
        font-weight: 700;
        
        color: var(--muted);
        
        margin: 1rem 0 0.5rem;
    
    }

    
    .as-select {
        
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

    
    .as-select:focus {
        
        border-color: var(--success);
        
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.18);
    
    }

    
    .as-static-field {
        
        width: 100%;
        
        min-height: 48px;
        
        border: 2px solid var(--line);
        
        border-radius: 0.75rem;
        
        padding: 0.75rem 1rem;
        
        background: var(--surface-soft);
        
        color: var(--ink);
        
        font-weight: 600;
        
        display: flex;
        
        align-items: center;
    
    }

    
    .as-submit {
        
        width: 100%;
        
        border: none;
        
        border-radius: 0.75rem;
        
        padding: 1rem 1.25rem;
        
        font-weight: 700;
        
        color: #ffffff;
        
        background: #059669;
        
        box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.22), 0 4px 6px -4px rgba(13, 148, 136, 0.22);
        
        transition: box-shadow 0.2s ease, transform 0.15s ease;
        
        cursor: pointer;
    
    }

    
    .as-submit:hover {
        
        box-shadow: 0 16px 22px -6px rgba(5, 150, 105, 0.25), 0 10px 16px -8px rgba(13, 148, 136, 0.25);
        
        transform: scale(1.02);
    
    }

    
    .as-submit:not(:disabled):active {
        
        transform: scale(0.98);
    
    }

    
    .as-submit:disabled {
        
        opacity: 0.55;
        
        cursor: not-allowed;
        
        transform: none;
        
        box-shadow: none;
    
    }

    
    .as-submit-inner {
        
        display: inline-flex;
        
        align-items: center;
        
        justify-content: center;
        
        gap: 0.5rem;
    
    }

    
    .as-submit-inner svg {
        
        width: 20px;
        
        height: 20px;
        
        stroke: currentColor;
        
        fill: none;
        
        stroke-width: 2.2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
        
        display: block;
    
    }

    
    .as-hint {
        
        margin: -0.5rem 0 0;
        
        color: var(--muted);
        
        font-size: 0.85rem;
        
        line-height: 1.5;
    
    }

    
    @media (max-width: 1100px) {
        
        .as-grid {
            
            grid-template-columns: 1fr;
        
        }
    
    }

    
    @media (max-width: 820px) {
        
        .as-staff-grid {
            
            grid-template-columns: 1fr;
        
        }
    
    }

    
    @media (prefers-reduced-motion: reduce) {
        
        .as-animate-left,
        
        .as-animate-right,
        
        .as-staff-animate,
        
        .as-header {
            
            animation: none !important;
        
        }

        
        .as-staff-card,
        
        .as-submit {
            
            transition: none !important;
        
        }

        
        .as-staff-card:hover,
        
        .as-staff-card:active,
        
        .as-submit:hover,
        
        .as-submit:active {
            
            transform: none !important;
        
        }
    
    }

</style>

<div class="as-shell">
    
    <div class="as-header">
        
        <h1 class="as-title">Assign Staff</h1>
        
        <p class="as-subtitle">Manage and assign employees to branches</p>
    
    </div>

    
    <form
        
        action="{{ route('stores.assignments.update', $store, false) }}"
        
        method="POST"
        
        class="as-grid"
        
        data-assignments-form
    
    >
        
        @csrf
        
        @method('PUT')

        
        <section class="as-card as-card--list as-animate-left" style="animation-delay: 0.1s;" aria-label="Staff list">
            
            <div class="as-list-header">
                
                <div class="as-list-head">
                    
                    <div class="as-list-title">
                        
                        <span class="as-list-mark" aria-hidden="true">
                            
                            <svg viewBox="0 0 24 24">
                                
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                
                                <circle cx="9" cy="7" r="4"></circle>
                                
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            
                            </svg>
                        
                        </span>
                        
                        <div style="min-width:0;">
                            
                            <h2 class="as-list-heading">Staff List</h2>
                            
                            <div class="as-selected-count">
                                
                                <span data-selected-count>{{ count($selectedEmployees) }}</span> selected
                                
                                <span class="as-selected-divider">•</span>
                                
                                <span class="as-selected-vacant">{{ $vacantCount }} vacant</span>
                            
                            </div>
                        
                        </div>
                    
                    </div>
                
                </div>

                
                <div class="as-search">
                    
                    <span class="as-search-icon" aria-hidden="true">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <circle cx="11" cy="11" r="8"></circle>
                            
                            <path d="m21 21-4.3-4.3"></path>
                        
                        </svg>
                    
                    </span>
                    
                    <input type="search" class="as-search-input" placeholder="Search for an employee..." data-staff-search>
                
                </div>
            
            </div>

            
            <div class="as-list-body">
                
                <div class="as-staff-grid" data-staff-grid>
                
                @foreach ($employees as $employee)
                    
                    @php
                        
                        $isSelected = isset($selectedEmployeeSet[$employee->id]);
                        
                        $employeeStore = $employee->stores->first() ?? $employee->assignedStore;
                        
                        $employeeStoreLabel = $employeeStore
                            
                            ? ($storeNameById[$employeeStore->id] ?? \App\Support\EnglishPlaceNames::branchDisplayName($employeeStore->branch_code, $employeeStore->name))
                            
                            : 'Vacant';
                        
                        $employeeRoleLabel = ucwords(str_replace('_', ' ', (string) $employee->role));
                        
                        $employeeEmail = \App\Support\UserContact::email($employee->email, $employee->name, (int) $employee->id);
                        
                        $employeePhone = \App\Support\UserContact::phone($employee->phone);
                        
                        $initial = mb_substr((string) $employee->name, 0, 1);
                    
                    @endphp
                    
                    <button
                        
                        type="button"
                        
                        class="as-staff-card as-staff-animate {{ $isSelected ? 'is-selected' : '' }}"
                        
                        style="animation-delay: {{ number_format(0.2 + ($loop->index * 0.05), 2) }}s;"
                        
                        data-staff-card
                        
                        data-employee-id="{{ $employee->id }}"
                        
                        data-employee-name="{{ $employee->name }}"
                        
                        data-employee-role="{{ $employeeRoleLabel }}"
                        
                        data-employee-email="{{ $employeeEmail }}"
                        
                        data-employee-phone="{{ $employeePhone }}"
                        
                        data-employee-store-id="{{ $employeeStore?->id ?? '' }}"
                        
                        data-employee-store-name="{{ $employeeStoreLabel }}"
                        
                        data-employee-is-vacant="{{ $employeeStore ? '0' : '1' }}"
                        
                        aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                    
                    >
                        
                        <div class="as-staff-top">
                            
                            <div class="as-avatar" aria-hidden="true">{{ $initial ?: '?' }}</div>
                            
                            <div style="min-width: 0;">
                                
                                <div class="as-staff-name">{{ $employee->name }}</div>
                                
                                <div class="as-staff-role">{{ $employeeRoleLabel }}</div>
                            
                            </div>
                        
                        </div>

                        
                        <div class="as-meta">
                            
                            <div class="as-meta-row">
                                
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    
                                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                    
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                
                                </svg>
                                
                                <span>{{ $employeeEmail }}</span>
                            
                            </div>
                            
                            <div class="as-meta-row">
                                
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                
                                </svg>
                                
                                <span>{{ $employeePhone }}</span>
                            
                            </div>
                            
                            <div class="as-meta-row">
                                
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    
                                    <path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                    
                                    <rect width="20" height="14" x="2" y="6" rx="2"></rect>
                                
                                </svg>
                        
                        <span>{{ $employeeRoleLabel }}</span>
                            
                            </div>
                        
                        </div>

                        
                        <span class="as-branch-pill {{ $employeeStore ? 'as-branch-pill--assigned' : 'as-branch-pill--vacant' }}">
                            
                            {{ $employeeStoreLabel }}
                        
                        </span>
                    
                    </button>
                
                @endforeach
                
                </div>
            
            </div>
        
        </section>

        
        <aside class="as-side as-animate-right" style="animation-delay: 0.2s;">
            
            <div class="as-side-actions">
                
                <section class="as-card">
                    
                    <h3 class="as-side-title">Choose Branch</h3>
                    
                    @if ($canManageBranchSelection)
                        
                        @include('partials.custom-select-native', [
                            
                            'options' => $storeOptions,
                            
                            'selectedValue' => (string) $store->id,
                            
                            'selectedLabel' => $storeLabel,
                            
                            'wrapperClass' => 'custom-select',
                            
                            'selectClass' => 'as-select custom-select-native',
                            
                            'triggerClass' => 'as-select custom-select-trigger',
                            
                            'selectAttributes' => 'data-store-select aria-label="Choose branch"',
                        
                        ])
                    
                    @else
                        
                        <div class="as-static-field" aria-label="Current branch">
                            
                            {{ $limitedStoreLabel }}
                        
                        </div>
                    
                    @endif

                    
                    <label class="as-field-label" for="manager_id">Store Manager</label>
                    
                    @if ($canManageBranchSelection)
                        
                        @include('partials.custom-select-native', [
                            
                            'name' => 'manager_id',
                            
                            'options' => $managerOptions,
                            
                            'selectedValue' => (string) $managerIdValue,
                            
                            'selectedLabel' => $currentManagerName,
                            
                            'selectedLabelHtml' => $currentManagerLabelHtml,
                            
                            'wrapperClass' => 'custom-select',
                            
                            'selectClass' => 'as-select custom-select-native',
                            
                            'triggerClass' => 'as-select custom-select-trigger',
                            
                            'selectAttributes' => 'id="manager_id" aria-label="Choose manager" required',
                        
                        ])
                    
                    @else
                        
                        <input type="hidden" name="manager_id" value="{{ $limitedManagerId }}">
                        
                        <div class="as-static-field" aria-label="Current manager">
                            
                            {{ $limitedManagerName }}
                        
                        </div>
                    
                    @endif

                
                </section>

                
                <div data-removed-inputs>
                    
                    @foreach ($removedEmployees as $removedId)
                        
                        <input type="hidden" name="removed_employee_ids[]" value="{{ $removedId }}" id="removed-employee-{{ $removedId }}">
                    
                    @endforeach
                
                </div>

                
                <button type="submit" class="as-submit">
                    
                    <span class="as-submit-inner">
                        
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"></path>
                            
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            
                            <polyline points="7 3 7 8 15 8"></polyline>
                        
                        </svg>
                        
                        Save Changes
                    
                    </span>
                
                </button>

            
            </div>

            
            <section class="as-card">
                
                <h3 class="as-side-title">Selected Staff</h3>

                
                <div class="as-empty" data-selected-empty style="{{ count($selectedEmployees) ? 'display:none;' : '' }}">
                    
                    <div class="as-empty-icon" aria-hidden="true">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            
                            <circle cx="9" cy="7" r="4"></circle>
                            
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        
                        </svg>
                    
                    </div>
                    
                    <p class="as-empty-text">No staff selected yet</p>
                
                </div>

                
                <div class="as-selected-list" data-selected-list>
                    
                    @foreach ($selectedEmployees as $employeeId)
                        
                        @php
                            
                            $employee = $employeesById->get($employeeId);
                        
                        @endphp
                        
                        @if ($employee)
                            
                            @php
                                
                                $roleLabel = ucwords(str_replace('_', ' ', (string) $employee->role));
                                
                                $initial = mb_substr((string) $employee->name, 0, 1);
                            
                            @endphp
                            
                            <div class="as-selected-item" data-selected-item data-employee-id="{{ $employeeId }}">
                                
                                <div class="as-selected-avatar" aria-hidden="true">{{ $initial ?: '?' }}</div>
                                
                                <div style="min-width: 0;">
                                    
                                    <div class="as-selected-name">{{ $employee->name }}</div>
                                    
                                    <div class="as-selected-role">{{ $roleLabel }}</div>
                                
                                </div>
                                
                                <input type="hidden" name="employee_ids[]" value="{{ $employeeId }}">
                            
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
        
        const form = document.querySelector('[data-assignments-form]');

        
        const staffGrid = document.querySelector('[data-staff-grid]');
        
        const staffSearch = document.querySelector('[data-staff-search]');
        
        const selectedList = document.querySelector('[data-selected-list]');
        
        const emptyState = document.querySelector('[data-selected-empty]');
        
        const removedInputsRoot = document.querySelector('[data-removed-inputs]');
        
        const selectedCountEls = Array.from(document.querySelectorAll('[data-selected-count]'));

        
        const selectedIds = new Set(
            
            Array.from(selectedList?.querySelectorAll('input[name="employee_ids[]"]') || [])
                
                .map(function (input) { return String(input.value || ''); })
                
                .filter(Boolean)
        
        );

        
        function setCount(count) {
            
            selectedCountEls.forEach(function (el) {
                
                el.textContent = String(count);
            
            });
        
        }

        
        function updateEmptyState() {
            
            const count = selectedIds.size;
            
            setCount(count);
            
            if (emptyState) {
                
                emptyState.style.display = count === 0 ? '' : 'none';
            
            }
        
        }

        
        function roleFromCard(card) {
            
            return String(card?.dataset.employeeRole || '').trim() || 'Employee';
        
        }

        
        function initialFromName(name) {
            
            const trimmed = String(name || '').trim();
            
            return trimmed ? trimmed.slice(0, 1) : '?';
        
        }

        
        function getSelectedItem(id) {
            
            if (!selectedList) return null;
            
            return selectedList.querySelector('[data-selected-item][data-employee-id="' + CSS.escape(id) + '"]');
        
        }

        
        function removeRemovedInput(id) {
            
            const existing = document.getElementById('removed-employee-' + id);
            
            existing?.remove();
        
        }

        
        function ensureRemovedInput(id) {
            
            if (!removedInputsRoot) return;
            
            if (document.getElementById('removed-employee-' + id)) return;
            
            const input = document.createElement('input');
            
            input.type = 'hidden';
            
            input.name = 'removed_employee_ids[]';
            
            input.value = id;
            
            input.id = 'removed-employee-' + id;
            
            removedInputsRoot.appendChild(input);
        
        }

        
        function createSelectedItem(card) {
            
            const id = String(card?.dataset.employeeId || '');
            
            const name = String(card?.dataset.employeeName || '').trim() || 'Employee';
            
            const role = roleFromCard(card);
            
            const initial = initialFromName(name);

            
            const wrapper = document.createElement('div');
            
            wrapper.className = 'as-selected-item';
            
            wrapper.dataset.selectedItem = '1';
            
            wrapper.dataset.employeeId = id;

            
            const avatar = document.createElement('div');
            
            avatar.className = 'as-selected-avatar';
            
            avatar.setAttribute('aria-hidden', 'true');
            
            avatar.textContent = initial;

            
            const copy = document.createElement('div');
            
            copy.style.minWidth = '0';
            
            const nameEl = document.createElement('div');
            
            nameEl.className = 'as-selected-name';
            
            nameEl.textContent = name;
            
            const roleEl = document.createElement('div');
            
            roleEl.className = 'as-selected-role';
            
            roleEl.textContent = role;
            
            copy.appendChild(nameEl);
            
            copy.appendChild(roleEl);

            
            const input = document.createElement('input');
            
            input.type = 'hidden';
            
            input.name = 'employee_ids[]';
            
            input.value = id;

            
            wrapper.appendChild(avatar);
            
            wrapper.appendChild(copy);
            
            wrapper.appendChild(input);
            
            return wrapper;
        
        }

        
        function setCardSelected(card, selected) {
            
            if (!card) return;
            
            card.classList.toggle('is-selected', selected);
            
            card.setAttribute('aria-pressed', selected ? 'true' : 'false');
        
        }

        
        function toggleCard(card) {
            
            const id = String(card?.dataset.employeeId || '');
            
            if (!id) return;

            
            if (selectedIds.has(id)) {
                
                selectedIds.delete(id);
                
                setCardSelected(card, false);
                
                getSelectedItem(id)?.remove();
                
                ensureRemovedInput(id);
            
            } else {
                
                selectedIds.add(id);
                
                setCardSelected(card, true);
                
                removeRemovedInput(id);
                
                if (selectedList && !getSelectedItem(id)) {
                    
                    selectedList.appendChild(createSelectedItem(card));
                
                }
            
            }

            
            updateEmptyState();
        
        }

        
        staffGrid?.querySelectorAll('[data-staff-card]').forEach(function (card) {
            
            const id = String(card.dataset.employeeId || '');
            
            setCardSelected(card, selectedIds.has(id));
        
        });

        
        staffGrid?.addEventListener('click', function (event) {
            
            const card = event.target.closest('[data-staff-card]');
            
            if (!card) return;
            
            toggleCard(card);
        
        });

        
        function applySearch(query) {
            
            const normalized = String(query || '').trim().toLowerCase();
            
            staffGrid?.querySelectorAll('[data-staff-card]').forEach(function (card) {
                
                const name = String(card.dataset.employeeName || '').toLowerCase();
                
                const role = String(card.dataset.employeeRole || '').toLowerCase();
                
                const email = String(card.dataset.employeeEmail || '').toLowerCase();
                
                const phone = String(card.dataset.employeePhone || '').toLowerCase();
                
                const visible = !normalized || name.includes(normalized) || role.includes(normalized) || email.includes(normalized) || phone.includes(normalized);
                
                card.style.display = visible ? '' : 'none';
            
            });
        
        }

        
        staffSearch?.addEventListener('input', function () {
            
            applySearch(staffSearch.value);
        
        });

        
        const storeSelect = document.querySelector('[data-store-select]');
        
        storeSelect?.addEventListener('change', function () {
            
            const id = storeSelect.value;
            
            if (!id) return;
            
            const target = "{{ route('stores.assignments', '__STORE__', false) }}".replace('__STORE__', id);
            
            window.location.href = target;
        
        });

        
        updateEmptyState();
    
    });

</script>

@endsection
