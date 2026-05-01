@extends('layouts.app')

@section('title', 'Store Details')

@section('page_subtitle', 'A unified reference page for branch data, status, assigned staff, products, and operational files.')

@section('content')

@php
    
    
    $currentUser = auth()->user();
    
    $isSystemAdmin = $currentUser && $currentUser->role === 'admin';
    
    $canDeleteStore = $currentUser?->hasPermission('delete_store') ?? false;
    
    $canManageProducts = $currentUser?->hasPermission('manage_store_products') ?? false;
    
    $canAssignStaff = ($currentUser?->hasPermission('assign_staff_to_store') ?? false)
        
        || ($currentUser?->hasPermission('manage_store_staff') ?? false);
    
    $hasQuickActions = $canAssignStaff || $canManageProducts || $isSystemAdmin;

    
    
    $storeDisplayName = (string) ($store->name ?? '');
    
    
    $branchCode = strtoupper((string) ($store->branch_code ?? ''));
    
    
    
    $storedAddress = trim((string) ($store->address ?? ''));
    
    
    $addressLabel = $storedAddress !== '' ? $storedAddress : 'Not specified';
    
    
    $provinceLabel = $store->province?->name ?: 'Not specified';

    
    
    $manager = $store->manager;
    
    $managerEmail = $manager ? \App\Support\UserContact::email($manager->email, $manager->name, (int) $manager->id) : 'Not available';
    
    $managerPhone = $manager ? \App\Support\UserContact::phone($manager->phone) : 'Not available';
    
    
    $storePhone = \App\Support\UserContact::phone($store->phone, false);
    
    $storePhoneLabel = $storePhone !== '' ? $storePhone : 'Not available';
    
    
    $employeeCount = (int) $store->employees->count();
    
    
    $staffCount = $employeeCount;
    
    $productCount = (int) $store->products->count();

    
    
    $statusLabel = match($store->status) {
        
        'active' => 'Active',
        
        'inactive' => 'Inactive',
        
        'under_maintenance' => 'Under maintenance',
        
        default => $store->status,
    
    };

    
    
    $formatTo12h = function ($value) {
        
        $value = $value ? \Illuminate\Support\Str::of($value)->substr(0, 5) : null;
        
        if (! $value) {
            
            return null;
        
        }
        
        try {
            
            return \Carbon\Carbon::createFromFormat('H:i', (string) $value)->format('g:i A');
        
        } catch (\Throwable $e) {
            
            return (string) $value;
        
        }
    
    };

    
    $formatWorkingHours = function (?string $startsAt, ?string $endsAt) use ($formatTo12h): ?string {
        
        if (! $startsAt && ! $endsAt) {
            
            return null;
        
        }
        
        $startLabel = $formatTo12h($startsAt);
        
        $endLabel = $formatTo12h($endsAt);
        
        if ($startLabel && $endLabel) {
            
            return 'From '.$startLabel.' to '.$endLabel;
        
        }
        
        return $startLabel ?: $endLabel;
    
    };

    
    $workingHoursLabel = $formatWorkingHours($store->workday_starts_at, $store->workday_ends_at);
    
    if (! $workingHoursLabel && $store->working_hours) {
        
        $workingHoursLabel = $store->working_hours;
    
    }
    
    $workingHoursLabel = $workingHoursLabel ?: 'Not specified';

    
    $workingHoursDuration = 'Not specified';
    
    if ($store->workday_starts_at && $store->workday_ends_at) {
        
        try {
            
            $start = \Carbon\Carbon::createFromFormat('H:i', (string) \Illuminate\Support\Str::of($store->workday_starts_at)->substr(0, 5));
            
            $end = \Carbon\Carbon::createFromFormat('H:i', (string) \Illuminate\Support\Str::of($store->workday_ends_at)->substr(0, 5));
            
            if ($end->lessThanOrEqualTo($start)) {
                
                $end->addDay();
            
            }
            
            $hours = $start->diffInMinutes($end) / 60;
            
            $workingHoursDuration = (fmod($hours, 1.0) === 0.0 ? number_format($hours, 0) : rtrim(rtrim(number_format($hours, 2), '0'), '.')).' Hours';
        
        } catch (\Throwable $e) {
            
            $workingHoursDuration = 'Not specified';
        
        }
    
    }

    
    $openingDateLabel = $store->opening_date?->format('Y-m-d') ?: 'Not specified';

@endphp

<style>
    
    .bd-shell { display: grid; gap: 1.5rem; }
    
    .bd-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
    
    .bd-header-left { display: flex; align-items: center; gap: 1rem; }
    
    @keyframes bdFadeDown { from { opacity: 0; transform: translateY(-12px); } to { opacity: 1; transform: translateY(0); } }
    
    @keyframes bdFadeLeft { from { opacity: 0; transform: translateX(-12px); } to { opacity: 1; transform: translateX(0); } }
    
    @keyframes bdFadeRight { from { opacity: 0; transform: translateX(12px); } to { opacity: 1; transform: translateX(0); } }
    
    @keyframes bdFadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    
    .bd-animate-down { animation: bdFadeDown 0.55s ease both; }
    
    .bd-animate-left { animation: bdFadeLeft 0.55s ease both; }
    
    .bd-animate-right { animation: bdFadeRight 0.55s ease both; }
    
    .bd-animate-up { animation: bdFadeUp 0.55s ease both; }
    
    .bd-back {
        
        width: 42px;
        
        height: 42px;
        
        border-radius: 12px;
        
        border: none;
        
        background: var(--surface-soft);
        
        display: inline-flex;
        
        align-items: center;
        
        justify-content: center;
        
        color: var(--placeholder);
        
        transition: all .2s ease;
    
    }
    
    .bd-back:hover { background: rgba(var(--primary-rgb), 0.12); color: var(--ink); }
    
    .bd-title { margin: 0; font-size: 1.85rem; font-weight: 800; color: var(--ink); }
    
    .bd-subtitle { margin: 0.2rem 0 0; color: var(--muted); font-size: 0.95rem; }
    
    .bd-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }
    
    .bd-btn {
        
        border-radius: 12px;
        
        padding: 0.75rem 1.25rem;
        
        font-weight: 700;
        
        display: inline-flex;
        
        align-items: center;
        
        gap: 0.5rem;
        
        border: 2px solid transparent;
        
        text-decoration: none;
        
        transition: all .2s ease;
    
    }
    
    .bd-btn-primary { background: var(--primary-deep, #2563eb); color: #fff; box-shadow: 0 12px 24px rgba(37, 99, 235, 0.25); }
    
    .bd-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 18px 32px rgba(37, 99, 235, 0.35); }
    
    .bd-btn-danger { background: var(--glass-surface-strong); color: var(--danger); border-color: rgba(239, 68, 68, 0.45); }
    
    .bd-btn-danger:hover { background: rgba(239, 68, 68, 0.12); }

    
    .bd-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; }
    
    .bd-stat {
        
        border-radius: 18px;
        
        padding: 1.1rem;
        
        color: #fff;
        
        box-shadow: 0 16px 24px rgba(15, 23, 42, 0.18);
        
        transition: transform .2s ease, box-shadow .2s ease;
    
    }
    
    .bd-stat:hover { transform: translateY(-2px); box-shadow: 0 20px 30px rgba(15, 23, 42, 0.22); }
    
    .bd-stat h4 { margin: 0; font-size: 1.6rem; font-weight: 800; }
    
    .bd-stat p { margin: 0.2rem 0 0; opacity: 0.85; font-size: 0.85rem; }
    
    .bd-stat-icon {
        
        width: 44px;
        
        height: 44px;
        
        border-radius: 12px;
        
        background: rgba(255, 255, 255, 0.2);
        
        display: inline-flex;
        
        align-items: center;
        
        justify-content: center;
        
        margin-bottom: 0.8rem;
    
    }
    
    .bd-stat-icon svg { width: 22px; height: 22px; stroke: #fff; fill: none; stroke-width: 2; }
    
    .bd-stat-blue { background: var(--primary-deep, #2563eb); }
    
    .bd-stat-emerald { background: #10b981; }
    
    .bd-stat-purple { background: #a855f7; }
    
    .bd-stat-orange { background: #f97316; }

    
    .bd-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 1.5rem; }
    
    .bd-card {
        
        background: var(--glass-surface);
        
        border-radius: 18px;
        
        border: 1px solid var(--glass-border);
        
        box-shadow: var(--glass-shadow);
        
        overflow: hidden;
        
        transition: transform .2s ease, box-shadow .2s ease;
    
    }
    
    .bd-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
    
    .bd-card-header {
        
        padding: 1rem 1.5rem;
        
        border-bottom: 1px solid var(--line);
        
        display: flex;
        
        align-items: center;
        
        gap: 0.75rem;
        
        background: var(--surface-soft);
    
    }
    
    .bd-card-header h2 {
        
        margin: 0;
        
        font-size: 1.25rem;
        
        font-weight: 700;
        
        color: var(--ink);
    
    }
    
    .bd-card-header-icon {
        
        width: 40px;
        
        height: 40px;
        
        border-radius: 8px;
        
        background: var(--primary-deep, #2563eb);
        
        display: inline-flex;
        
        align-items: center;
        
        justify-content: center;
    
    }
    
    .bd-card-header-icon svg { width: 20px; height: 20px; stroke: #fff; fill: none; stroke-width: 2; }
    
    .bd-card-body { padding: 1.25rem; }
    
    .bd-info-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    
    .bd-info-item label { display: block; font-size: 0.75rem; font-weight: 700; color: var(--placeholder); margin-bottom: 0.35rem; }
    
    .bd-info-item span { font-weight: 600; color: var(--ink); }
    
    .bd-info-row { display: flex; gap: 0.75rem; align-items: flex-start; }
    
    .bd-info-row svg { width: 18px; height: 18px; stroke: #2563eb; fill: none; stroke-width: 2; margin-top: 0.2rem; }
    
    .bd-pill {
        
        display: inline-flex;
        
        align-items: center;
        
        padding: 0.35rem 0.75rem;
        
        border-radius: 999px;
        
        font-size: 0.8rem;
        
        font-weight: 700;
        
        background: rgba(34, 197, 94, 0.18);
        
        color: var(--success);
    
    }
    
    .bd-brochure-links { display: inline-flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; }
    
    .bd-link { color: var(--primary); font-weight: 600; text-decoration: none; }
    
    .bd-link:hover { color: var(--primary-deep); }
    
    .bd-link--download { color: #0f766e; }
    
    .bd-link--download:hover { color: #0f766e; }
    
    .bd-link-sep { color: var(--line); font-weight: 700; }

    
    .bd-people-list { display: grid; gap: 0.75rem; }
    
    .bd-people-item {
        
        display: flex;
        
        align-items: center;
        
        gap: 0.75rem;
        
        padding: 0.85rem;
        
        border-radius: 14px;
        
        background: var(--surface-soft);
        
        border: 1px solid var(--line);
        
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    
    }
    
    .bd-people-item:hover { transform: translateY(-2px); background: rgba(var(--primary-rgb), 0.08); box-shadow: var(--shadow-md); }
    
    .bd-people-avatar {
        
        width: 44px;
        
        height: 44px;
        
        border-radius: 999px;
        
        background: #10b981;
        
        color: #fff;
        
        display: inline-flex;
        
        align-items: center;
        
        justify-content: center;
        
        font-weight: 700;
        
        font-size: 0.9rem;
    
    }
    
    .bd-people-role { font-size: 0.75rem; color: var(--placeholder); }
    
    .bd-people-meta { font-size: 0.8rem; color: var(--muted); margin-top: 0.2rem; }

    
    .bd-sidebar { display: grid; gap: 1rem; }
    
    .bd-sidebar-card { background: var(--glass-surface); border-radius: 18px; border: 1px solid var(--glass-border); box-shadow: var(--glass-shadow); overflow: hidden; }
    
    .bd-sidebar-card { transition: transform .2s ease, box-shadow .2s ease; }
    
    .bd-sidebar-card:hover { transform: translateY(-2px); box-shadow: 0 20px 32px rgba(15, 23, 42, 0.12); }
    
    .bd-sidebar-card .bd-card-header { background: var(--surface-soft); }
    
    .bd-sidebar-card .bd-card-header-icon { background: #a855f7; }
    
    .bd-sidebar-card .bd-card-header h2 { font-size: 1.125rem; }
    
    .bd-manager-avatar { width: 70px; height: 70px; border-radius: 999px; background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.4rem; margin: 0 auto 0.75rem; }
    
    .bd-manager-meta { text-align: center; }
    
    .bd-manager-meta h3 { margin: 0 0 0.2rem; font-size: 1.1rem; }
    
    .bd-manager-meta p { margin: 0; color: var(--muted); font-size: 0.85rem; }
    
    .bd-manager-contact { margin-top: 1rem; display: grid; gap: 0.5rem; }
    
    .bd-manager-contact div { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--ink); background: var(--surface-soft); padding: 0.6rem 0.75rem; border-radius: 12px; }
    
    .bd-manager-contact svg {
        
        width: 16px;
        
        height: 16px;
        
        stroke: #10b981;
        
        fill: none;
        
        stroke-width: 2;
        
        stroke-linecap: round;
        
        stroke-linejoin: round;
    
    }

    
    .bd-quick-actions { display: grid; gap: 0.5rem; }
    
    .bd-quick-actions a {
        
        display: block;
        
        text-decoration: none;
        
        padding: 0.7rem 0.9rem;
        
        border-radius: 12px;
        
        font-weight: 600;
        
        text-align: center;
        
        background: rgba(var(--primary-rgb), 0.12);
        
        color: var(--primary);
        
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    
    }
    
    .bd-quick-actions a:hover {
        
        background: rgba(var(--primary-rgb), 0.18);
        
        box-shadow: 0 10px 16px -14px rgba(37, 99, 235, 0.4);
        
        transform: translateY(-1px);
    
    }

    
    @media (max-width: 1100px) {
        
        .bd-grid { grid-template-columns: 1fr; }
        
        .bd-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    
    }

    
    @media (max-width: 720px) {
        
        .bd-stats { grid-template-columns: 1fr; }
    
    }

    
    @media (prefers-reduced-motion: reduce) {
        
        .bd-animate-down,
        
        .bd-animate-left,
        
        .bd-animate-right,
        
        .bd-animate-up {
            
            animation: none !important;
        
        }
        
        .bd-card,
        
        .bd-people-item,
        
        .bd-sidebar-card,
        
        .bd-btn {
            
            transition: none !important;
        
        }
    
    }

</style>

<div class="bd-shell">
    
    <div class="bd-header bd-animate-down">
        
        <div class="bd-header-left">
            
            <a href="{{ route('stores.index', [], false) }}" class="bd-back" aria-label="Back">
                
                <svg viewBox="0 0 24 24">
                    
                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>
                
                </svg>
            
            </a>
            
            <div>
                
                <h1 class="bd-title">{{ $storeDisplayName }}</h1>
                
                <p class="bd-subtitle">{{ $branchCode }}</p>
            
            </div>
        
        </div>
        
        <div class="bd-actions"></div>
    
    </div>

    
    <div class="bd-stats bd-animate-up" style="animation-delay: 0.05s;">
        
        <div class="bd-stat bd-stat-blue">
            
            <div class="bd-stat-icon">
                
                <svg viewBox="0 0 24 24">
                    
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    
                    <circle cx="9" cy="7" r="4"></circle>
                    
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                
                </svg>
            
            </div>
            
            <h4>{{ $staffCount }}</h4>
            
            <p>Employees</p>
        
        </div>
        
        <div class="bd-stat bd-stat-emerald">
            
            <div class="bd-stat-icon">
                
                <svg viewBox="0 0 24 24">
                    
                    <path d="m7.5 4.27 9 5.15"></path>
                    
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                    
                    <path d="m3.3 7 8.7 5 8.7-5"></path>
                    
                    <path d="M12 22V12"></path>
                
                </svg>
            
            </div>
            
            <h4>{{ $productCount }}</h4>
            
            <p>Linked Products</p>
        
        </div>
        
        <div class="bd-stat bd-stat-purple">
            
            <div class="bd-stat-icon">
                
                <svg viewBox="0 0 24 24">
                    
                    <path d="M12 5v14"></path>
                    
                    <path d="M5 12h14"></path>
                
                </svg>
            
            </div>
            
            <h4>{{ $branchCode }}</h4>
            
            <p>Branch Code</p>
        
        </div>
        
        <div class="bd-stat bd-stat-orange">
            
            <div class="bd-stat-icon">
                
                <svg viewBox="0 0 24 24">
                    
                    <circle cx="12" cy="12" r="10"></circle>
                    
                    <polyline points="12 6 12 12 16 14"></polyline>
                
                </svg>
            
            </div>
            
            <h4>{{ $workingHoursDuration }}</h4>
            
            <p>Working Hours</p>
        
        </div>
    
    </div>

    
    <div class="bd-grid">
        
        <div class="bd-card bd-animate-left" style="animation-delay: 0.1s;">
            
            <div class="bd-card-header">
                
                <div class="bd-card-header-icon">
                    
                    <svg viewBox="0 0 24 24">
                        
                        <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path>
                        
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                        
                        <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path>
                        
                        <path d="M2 7h20"></path>
                        
                        <path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"></path>
                    
                    </svg>
                
                </div>
                
                <h2>Branch Information</h2>
            
            </div>
            
            <div class="bd-card-body">
                
                <div class="bd-info-grid">
                    
                    <div class="bd-info-item">
                        
                        <label>Store name:</label>
                        
                        <span>{{ $storeDisplayName }}</span>
                    
                    </div>
                    
                    <div class="bd-info-item">
                        
                        <label>Branch code:</label>
                        
                        <span>{{ $branchCode }}</span>
                    
                    </div>
                    
                    <div class="bd-info-item">
                        
                        <label>Province:</label>
                        
                        <span>{{ $provinceLabel }}</span>
                    
                    </div>
                    
                    <div class="bd-info-item">
                        
                        <label>Status:</label>
                        
                        <span class="bd-pill">{{ $statusLabel }}</span>
                    
                    </div>
                
                </div>

                
                <div class="bd-info-item" style="margin-top: 1rem;">
                    
                    <label>Description:</label>
                    
                    <span>{{ $store->description ?: 'Not specified' }}</span>
                
                </div>

                
                <div style="margin-top: 1rem; display: grid; gap: 0.75rem;">
                    
                    <div class="bd-info-row">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 0 1 16 0Z"></path>
                            
                            <circle cx="12" cy="10" r="3"></circle>
                        
                        </svg>
                        
                        <div>
                            
                            <label>Address:</label>
                            
                            <span>{{ $addressLabel }}</span>
                        
                        </div>
                    
                    </div>
                    
                    <div class="bd-info-row">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <path d="M22 16.92V21a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.86 19.86 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h4.18a2 2 0 0 1 2 1.72l.7 4.2a2 2 0 0 1-.45 1.82L8.09 11a16 16 0 0 0 6 6l1.26-2.45a2 2 0 0 1 1.82-.45l4.2.7a2 2 0 0 1 1.63 2.12Z"></path>
                        
                        </svg>
                        
                        <div>
                            
                            <label>Phone:</label>
                            
                            <span><bdi dir="ltr">{{ $storePhoneLabel }}</bdi></span>
                        
                        </div>
                    
                    </div>
                    
                    <div class="bd-info-row">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <path d="M22 6 12 13 2 6"></path>
                            
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        
                        </svg>
                        
                        <div>
                            
                            <label>Email:</label>
                            
                            <span>{{ $store->email ?: 'Not available' }}</span>
                        
                        </div>
                    
                    </div>
                    
                    <div class="bd-info-row">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2Z"></path>
                            
                            <path d="M14 2v6h6"></path>
                            
                            <path d="M16 13H8"></path>
                            
                            <path d="M16 17H8"></path>
                            
                            <path d="M10 9H8"></path>
                        
                        </svg>
                        
                        <div>
                            
                            <label>Brochure:</label>
                            
                            <span class="bd-brochure-links">
                                
                                <a href="{{ route('stores.brochure.view', $store->id, false) }}?v={{ $store->updated_at?->timestamp ?? time() }}" class="bd-link">View</a>
                            
                            </span>
                        
                        </div>
                    
                    </div>
                    
                    <div class="bd-info-row">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <circle cx="12" cy="12" r="10"></circle>
                            
                            <polyline points="12 6 12 12 16 14"></polyline>
                        
                        </svg>
                        
                        <div>
                            
                            <label>Working Hours:</label>
                            
                            <span>{{ $workingHoursLabel }}</span>
                        
                        </div>
                    
                    </div>
                    
                    <div class="bd-info-row">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                            
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        
                        </svg>
                        
                        <div>
                            
                            <label>Opening Date:</label>
                            
                            <span>{{ $openingDateLabel }}</span>
                        
                        </div>
                    
                    </div>
                
                </div>
            
            </div>
        
        </div>

        
        <div class="bd-sidebar bd-animate-right" style="animation-delay: 0.12s;">
            
            <div class="bd-sidebar-card">
                
                <div class="bd-card-header">
                    
                    <div class="bd-card-header-icon">
                        
                        <svg viewBox="0 0 24 24">
                            
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            
                            <circle cx="12" cy="7" r="4"></circle>
                        
                        </svg>
                    
                    </div>
                    
                    <h2>Store Manager</h2>
                
                </div>
                
                <div class="bd-card-body">
                    
                    <div class="bd-manager-avatar">
                        
                        {{ $manager ? collect(explode(' ', $manager->name))->map(fn($n) => mb_substr($n, 0, 1))->implode('') : 'NA' }}
                    
                    </div>
                    
                    <div class="bd-manager-meta">
                        
                        <h3>{{ $manager?->name ?? '-' }}</h3>
                        
                        <p>Store Manager</p>
                    
                    </div>
                    
                    <div class="bd-manager-contact">
                        
                        <div>
                            
                            <svg viewBox="0 0 24 24"><path d="M22 16.92V21a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.86 19.86 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h4.18a2 2 0 0 1 2 1.72l.7 4.2a2 2 0 0 1-.45 1.82L8.09 11a16 16 0 0 0 6 6l1.26-2.45a2 2 0 0 1 1.82-.45l4.2.7a2 2 0 0 1 1.63 2.12Z"></path></svg>
                            
                            {{ $managerPhone }}
                        
                        </div>
                        
                        <div>
                            
                            <svg viewBox="0 0 24 24"><path d="M22 6 12 13 2 6"></path><rect x="2" y="4" width="20" height="16" rx="2"></rect></svg>
                            
                            {{ $managerEmail }}
                        
                        </div>
                    
                    </div>
                
                </div>
            
            </div>

            
            @if ($hasQuickActions)
                
                <div class="bd-sidebar-card" style="margin-top: 1rem;">
                    
                    <div class="bd-card-header">
                        
                        <div class="bd-card-header-icon" style="background: #10b981;">
                            
                            <svg viewBox="0 0 24 24">
                                
                                <path d="M12 5v14"></path>
                                
                                <path d="M5 12h14"></path>
                            
                            </svg>
                        
                        </div>
                        
                        <h2>Quick Actions</h2>
                    
                    </div>
                    
                    <div class="bd-card-body">
                        
                        <div class="bd-quick-actions">
                            
                            @if ($canAssignStaff)
                                
                                <a href="{{ route('stores.assignments', $store, false) }}">Assign Staff</a>
                            
                            @endif
                            
                            @if ($canManageProducts)
                                
                                <a href="{{ route('stores.products', $store, false) }}">Link Products</a>
                            
                            @endif
                        
                        </div>
                    
                    </div>
                
                </div>
            
            @endif
        
        </div>
    
    </div>

    
    <div class="bd-card bd-animate-up" style="animation-delay: 0.15s;">
        
        <div class="bd-card-header" style="background: rgba(34, 197, 94, 0.10);">
            
            <div class="bd-card-header-icon" style="background: #10b981;">
                
                <svg viewBox="0 0 24 24">
                    
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    
                    <circle cx="9" cy="7" r="4"></circle>
                    
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                
                </svg>
            
            </div>
            
            <h2>Branch Employees</h2>
        
        </div>
        
        <div class="bd-card-body">
            
            <div class="bd-people-list">
                
                @foreach ($store->employees as $employee)
                    
                    <div class="bd-people-item">
                        
                        <div class="bd-people-avatar">{{ collect(explode(' ', $employee->name))->map(fn($n) => mb_substr($n, 0, 1))->implode('') }}</div>
                        
                        <div>
                            
                            <div class="bd-people-role">Store Employee</div>
                            
                            <strong>{{ $employee->name }}</strong>
                            
                            <div class="bd-people-meta">Phone: {{ $employee->phone ?: '—' }}</div>
                            
                            <div class="bd-people-meta">Email: {{ $employee->email ?: '—' }}</div>
                        
                        </div>
                    
                    </div>
                
                @endforeach
            
            </div>
        
        </div>
    
    </div>

</div>

@endsection
