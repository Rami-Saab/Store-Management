@php
    
    
    $currentUser = auth()->user();
    
    
    $canEditStore = $currentUser?->hasPermission('edit_store') ?? false;
    
    $canDeleteStore = $currentUser?->hasPermission('delete_store') ?? false;
    
    $actionCount = 1 + ($canEditStore ? 1 : 0) + ($canDeleteStore ? 1 : 0);
    
    $footerRowClass = $actionCount === 1
        
        ? 'br-footer-row br-footer-row--single'
        
        : ($actionCount === 2 ? 'br-footer-row br-footer-row--double' : 'br-footer-row');

@endphp

@if ($stores->isEmpty())
    
    <div class="br-empty">
        
        No stores match the current search criteria. Try adjusting the name, status, or province.
    
    </div>

@else
    
    
    
    <div class="br-grid">
        
        
        
        @foreach ($stores as $store)
            
            @php
                
                
                $storeName = (string) ($store->name ?? '');
                
                
                $branchCode = strtoupper((string) ($store->branch_code ?? ''));
                
                
                $isDamascus = str_starts_with($branchCode, 'DAM');
                
                
                $theme = \App\Support\BranchTheme::themeForBranchCode($branchCode, (string) ($store->id ?? ''));
                
                $headerGradient = $theme['gradient'];

                
                
                $formatTo12h = static function ($value): ?string {
                    
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

                
                
                $effectiveStatus = $store->status;

                
                
                $statusLabel = match($effectiveStatus) {
                    
                    'active' => 'Active',
                    
                    'inactive' => 'Inactive',
                    
                    'under_maintenance' => 'Under maintenance',
                    
                    default => $effectiveStatus,
                
                };
                
                
                $statusVariant = match($effectiveStatus) {
                    
                    'active' => 'active',
                    
                    'inactive' => 'inactive',
                    
                    default => 'attention',
                
                };

                
                
                $provinceLabel = $store->province_name
                    
                    ? (string) $store->province_name
                    
                    : 'Not specified';
                
                $cityLabel = trim((string) ($store->city ?? ''));
                
                $locationLabel = $cityLabel !== '' ? ($provinceLabel.' - '.$cityLabel) : $provinceLabel;

                
                
                $addressLabel = trim((string) ($store->address ?? ''));
                
                $mapLabel = $addressLabel !== '' ? $addressLabel : $locationLabel;

                
                
                $workingHours = '';
                
                $start = $store->workday_starts_at ? substr((string) $store->workday_starts_at, 0, 5) : '';
                
                $end = $store->workday_ends_at ? substr((string) $store->workday_ends_at, 0, 5) : '';
                
                if ($start !== '' && $end !== '') {
                    
                    $startLabel = $formatTo12h($start) ?: $start;
                    
                    $endLabel = $formatTo12h($end) ?: $end;
                    
                    $workingHours = 'From '.$startLabel.' to '.$endLabel;
                
                } elseif ($start !== '' || $end !== '') {
                    
                    $timeLabel = $start !== '' ? $start : $end;
                    
                    $workingHours = $formatTo12h($timeLabel) ?: $timeLabel;
                
                }
                
                
                if ($workingHours === '') {
                    
                    $workingHours = trim((string) ($store->working_hours ?? ''));
                    
                    if ($workingHours !== '' && preg_match('/From\\s+(\\d{2}:\\d{2})\\s+to\\s+(\\d{2}:\\d{2})/i', $workingHours, $matches)) {
                        
                        $startLabel = $formatTo12h($matches[1]) ?: $matches[1];
                        
                        $endLabel = $formatTo12h($matches[2]) ?: $matches[2];
                        
                        $workingHours = 'From '.$startLabel.' to '.$endLabel;
                    
                    }
                
                }
                
                
                $workingHours = $workingHours !== '' ? $workingHours : 'Not specified';

                
                
                $managerName = trim((string) ($store->manager_name ?? ''));
                
                $managerName = $managerName !== '' ? $managerName : '-';
                
                $employeeCount = (int) ($store->employees_count ?? 0);
            
            @endphp

            
            
            
            <div class="br-card" style="--br-delay: {{ number_format(0.1 + ($loop->index * 0.05), 2) }}s;">
                
                <div class="br-card-main">
                    
                    <div class="br-card-head" style="background: {{ $headerGradient }};">
                    
                    <div class="br-card-head-top">
                        
                        <div>
                            
                            <h3 class="br-card-name">{{ $storeName }}</h3>
                            
                            <p class="br-card-code">{{ $store->branch_code }}</p>
                        
                        </div>
                        
                        <div class="br-status-wrap">
                            
                            <span class="br-status br-status--{{ $statusVariant }}">{{ $statusLabel }}</span>
                            
                            
                            
                            @if ($isDamascus)
                                
                                <span class="br-status-tag">
                                    
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        
                                        <path d="M12 2.5l2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 16.8 6.6 19.3l1-6.1L3.2 8.9l6.1-.9L12 2.5z"></path>
                                    
                                    </svg>
                                    
                                    Main
                                
                                </span>
                            
                            @endif
                        
                        </div>
                    
                    </div>
                        
                        <p class="br-manager">Manager: <bdi dir="ltr">{{ $managerName }}</bdi></p>
                    
                    </div>

                    
                    <div class="br-card-body">
                        
                        
                        
                        <div class="br-info">
                            
                            <span class="br-info-icon br-info-icon--pin" aria-hidden="true">
                                
                                <svg viewBox="0 0 24 24">
                                    
                                    <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 0 1 16 0Z"></path>
                                    
                                    <circle cx="12" cy="10" r="3"></circle>
                                
                                </svg>
                            
                            </span>
                            
                            <span class="br-info-text br-line"><bdi dir="auto">{{ $mapLabel }}</bdi></span>
                        
                        </div>

                        
                        
                        
                        <div class="br-info">
                            
                            <span class="br-info-icon br-info-icon--users" aria-hidden="true">
                                
                                <svg viewBox="0 0 24 24">
                                    
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    
                                    <circle cx="9" cy="7" r="4"></circle>
                                    
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                
                                </svg>
                            
                            </span>
                            
                            <span class="br-info-text"><bdi dir="ltr">{{ $employeeCount }}</bdi> employees</span>
                        
                        </div>

                        
                        
                        
                        <div class="br-info">
                            
                            <span class="br-info-icon br-info-icon--clock" aria-hidden="true">
                                
                                <svg viewBox="0 0 24 24">
                                    
                                    <circle cx="12" cy="12" r="10"></circle>
                                    
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                
                                </svg>
                            
                            </span>
                            
                            <span class="br-info-text"><bdi dir="ltr">{{ $workingHours }}</bdi></span>
                        
                        </div>

                        
                        
                        
                        <div class="br-info br-info--brochure">
                            
                            <span class="br-info-icon br-info-icon--file" aria-hidden="true">
                                
                                <svg viewBox="0 0 24 24">
                                    
                                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2Z"></path>
                                    
                                    <path d="M14 2v6h6"></path>
                                    
                                    <path d="M16 13H8"></path>
                                    
                                    <path d="M16 17H8"></path>
                                    
                                    <path d="M10 9H8"></path>
                                
                                </svg>
                            
                            </span>
                            
                            <span class="br-info-text">
                                
                                <span class="br-brochure-links">
                                    
                                    
                                    
                                    <a href="{{ route('stores.brochure.view', $store->id, false) }}" class="br-brochure-link">View Brochure</a>
                                
                                </span>
                            
                            </span>
                        
                        </div>
                    
                    </div>
                
                </div>

                
                
                
                <div class="br-footer">
                    
                    <div class="{{ $footerRowClass }}">
                        
                        
                        
                        <a href="{{ route('stores.show', $store->id, false) }}" class="br-footer-btn br-footer-btn--details" title="Details">
                            
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"></path>
                                
                                <circle cx="12" cy="12" r="3"></circle>
                            
                            </svg>
                            
                            <span style="font-size: 0.85rem;">Details</span>
                        
                        </a>

                        
                        
                        
                        @if ($canEditStore)
                            
                            <a href="{{ route('stores.edit', $store->id, false) }}" class="br-footer-btn br-footer-btn--edit" aria-label="Edit" title="Edit">
                                
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    
                                    <path d="M12 20h9"></path>
                                    
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                
                                </svg>
                                
                                <span style="font-size: 0.85rem;">Edit</span>
                            
                            </a>
                        
                        @endif

                        
                        
                        
                        @if ($canDeleteStore)
                            
                            <button
                                
                                type="button"
                                
                                class="br-footer-btn br-footer-btn--delete js-delete-open"
                                
                                data-action="{{ route('stores.destroy', $store->id, false) }}"
                                
                                data-name="{{ $storeName }}"
                                
                                aria-label="Delete"
                                
                                title="Delete"
                            
                            >
                                
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    
                                    <path d="M3 6h18"></path>
                                    
                                    <path d="M8 6V4h8v2"></path>
                                    
                                    <path d="M6 6l1 14h10l1-14"></path>
                                    
                                    <line x1="10" x2="10" y1="11" y2="17"></line>
                                    
                                    <line x1="14" x2="14" y1="11" y2="17"></line>
                                
                                </svg>
                                
                                <span style="font-size: 0.85rem;">Delete</span>
                            
                            </button>
                        
                        @endif
                    
                    </div>
                
                </div>
            
            </div>
        
        @endforeach
    
    </div>

@endif
