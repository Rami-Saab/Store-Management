@php
    
    
    $currentUser = auth()->user();
    
    $isSystemAdmin = $currentUser && $currentUser->role === 'admin';
    
    $isStoreManager = $currentUser && $currentUser->role === 'store_manager';
    
    $isStoreEmployee = $currentUser && in_array($currentUser->role, ['store_employee'], true);
    
    $canEditManager = $isSystemAdmin;

    
    
    $selectedManager = old('manager_id', $store->manager?->id ?? null);
    
    $selectedEmployees = old('employee_ids', isset($store) ? $store->employees->pluck('id')->all() : []);
    
    if ($isStoreManager && isset($store) && $store->exists) {
        
        $selectedEmployees = $store->employees->pluck('id')->all();
    
    }
    
    
    $employeesById = $isStoreManager && isset($store)
        
        ? $store->employees->keyBy('id')
        
        : (isset($employees) ? $employees->keyBy('id') : collect());
    
    if ($employeesById->isNotEmpty()) {
        
        $selectedEmployees = array_values(array_intersect($selectedEmployees, $employeesById->keys()->all()));
    
    }
    
    
    $currentProvinceId = (string) old('province_id', $store->province_id ?? '');
    
    $currentProvince = isset($provinces) ? $provinces->firstWhere('id', (int) $currentProvinceId) : null;
    
    
    $currentStatus = old('status', $store->status ?: 'active');
    
    $currentManagerId = (string) $selectedManager;
    
    $currentManager = isset($managers) ? $managers->firstWhere('id', (int) $currentManagerId) : null;
    
    $statusLabels = [
        
        'active' => 'Active',
        
        'inactive' => 'Inactive',
        
        'under_maintenance' => 'Under maintenance',
    
    ];
    
    
    $workdayStarts = old(
        
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
        
        if (! preg_match('/^(\d{2}):(\d{2})$/', $value, $matches)) {
            
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

    
    
    $workBadge = function ($user): string {
        
        return '';
    
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

@endphp

<div class="row g-4">
    
    <div class="col-xl-7">
        
        
        
        <div class="form-section">
            
            <div class="form-section-title">
                
                <h5>Branch Details</h5>
            
            </div>

            
            <div class="row g-3">
                
                <div class="col-md-6">
                    
                    <label class="form-label">Store name</label>
                    
                    <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ $storeNameValue }}" required>
                    
                    @error('name')
                        
                        <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                    
                    @enderror
                
                </div>
                
                <div class="col-md-6">
                    
                    <label class="form-label">Branch code</label>
                    
                    <input type="text" name="branch_code" class="form-control {{ $errors->has('branch_code') ? 'is-invalid' : '' }}" value="{{ old('branch_code', $store->branch_code) }}" required>
                    
                    @error('branch_code')
                        
                        <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                    
                    @enderror
                
                </div>
                
                
                
                <div class="col-12">
                    
                    <label class="form-label">Business hours</label>
                    
                    <div
                        
                        class="row g-3"
                        
                        data-time-format-scope
                        
                        data-shift-min-hours="{{ (int) config('store.shift_min_hours', 8) }}"
                        
                        data-shift-max-hours="{{ (int) (config('store.shift_max_hours') ?? 0) }}"
                    
                    >
                        
                        <div class="col-md-6">
                            
                            <label class="form-label form-label-subtle">Start time</label>
                            
                            <div class="time-input-wrap" data-time-trigger>
                                
                                <input
                                    
                                    type="time"
                                    
                                    name="workday_starts_at"
                                    
                                    class="form-control {{ $errors->has('workday_starts_at') || $errors->has('workday_ends_at') ? 'is-invalid' : '' }}"
                                    
                                    value="{{ $workdayStarts }}"
                                    
                                    step="300"
                                    
                                    required
                                    
                                    data-time-input
                                
                                >
                            
                            </div>
                        
                        </div>
                        
                        <div class="col-md-6">
                            
                            <label class="form-label form-label-subtle">End time</label>
                            
                            <div class="time-input-wrap" data-time-trigger>
                                
                                <input
                                    
                                    type="time"
                                    
                                    name="workday_ends_at"
                                    
                                    class="form-control {{ $errors->has('workday_starts_at') || $errors->has('workday_ends_at') ? 'is-invalid' : '' }}"
                                    
                                    value="{{ $workdayEnds }}"
                                    
                                    step="300"
                                    
                                    required
                                    
                                    data-time-input
                                
                                >
                            
                            </div>
                        
                        </div>
                    
                    </div>
                    
                    <div
                        
                        class="field-note field-note-danger mt-2 {{ $errors->has('workday_starts_at') || $errors->has('workday_ends_at') ? '' : 'd-none' }}"
                        
                        data-time-error
                        
                        role="alert"
                    
                    >
                        
                        {{ $errors->first('workday_ends_at') ?: $errors->first('workday_starts_at') }}
                    
                    </div>
                
                </div>
                
                
                
                <div class="col-md-6">
                    
                    <label class="form-label">Province</label>
                    
                    <div class="custom-select" data-custom-select>
                        
                        <input type="hidden" name="province_id" value="{{ $currentProvinceId }}" data-custom-select-input>
                        
                        <button
                            
                            type="button"
                            
                            class="form-select custom-select-trigger {{ $errors->has('province_id') ? 'is-invalid' : '' }}"
                            
                            data-custom-select-trigger
                            
                            aria-expanded="false"
                        
                        >
                            
                            <span class="custom-select-value" data-custom-select-value>
                            
                            {{ $currentProvince?->name ?: 'Select a province' }}
                            
                            </span>
                        
                        </button>
                        
                        <div class="custom-select-menu" data-custom-select-menu>
                            
                            <div class="custom-select-search">
                                
                                <input
                                    
                                    type="text"
                                    
                                    class="custom-select-search-input"
                                    
                                    placeholder="Search..."
                                    
                                    data-custom-select-search
                                
                                >
                            
                            </div>
                            
                            <div class="custom-select-options" data-custom-select-options>
                                
                                @foreach ($provinces as $province)
                                    
                                    <button
                                        
                                        type="button"
                                        
                                        class="custom-select-option {{ $currentProvinceId === (string) $province->id ? 'is-selected' : '' }}"
                                        
                                        data-value="{{ $province->id }}"
                                        
                                        data-label="{{ $province->name }}"
                                    
                                    >
                                        
                                        <span>{{ $province->name }}</span>
                                    
                                    </button>
                                
                                @endforeach
                            
                            </div>
                            
                            <div class="custom-select-empty" data-custom-select-empty>No results found</div>
                        
                        </div>
                    
                    </div>
                    
                    @error('province_id')
                        
                        <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                    
                    @enderror
                
                </div>
                
                
                
                <div class="col-md-6">
                    
                    <label class="form-label">Status</label>
                    
                    <div class="custom-select" data-custom-select>
                        
                        <input type="hidden" name="status" value="{{ $currentStatus }}" data-custom-select-input>
                        
                        <button
                            
                            type="button"
                            
                            class="form-select custom-select-trigger {{ $errors->has('status') ? 'is-invalid' : '' }}"
                            
                            data-custom-select-trigger
                            
                            aria-expanded="false"
                        
                        >
                            
                            <span class="custom-select-value" data-custom-select-value>
                                
                                {{ $statusLabels[$currentStatus] ?? $currentStatus }}
                            
                            </span>
                        
                        </button>
                        
                        <div class="custom-select-menu" data-custom-select-menu>
                            
                            <div class="custom-select-search">
                                
                                <input
                                    
                                    type="text"
                                    
                                    class="custom-select-search-input"
                                    
                                    placeholder="Search..."
                                    
                                    data-custom-select-search
                                
                                >
                            
                            </div>
                            
                            <div class="custom-select-options" data-custom-select-options>
                                
                                @foreach ($statuses as $status)
                                    
                                    <button
                                        
                                        type="button"
                                        
                                        class="custom-select-option {{ $currentStatus === $status ? 'is-selected' : '' }}"
                                        
                                        data-value="{{ $status }}"
                                        
                                        data-label="{{ $statusLabels[$status] ?? $status }}"
                                    
                                    >
                                        
                                        <span>{{ $statusLabels[$status] ?? $status }}</span>
                                    
                                    </button>
                                
                                @endforeach
                            
                            </div>
                            
                            <div class="custom-select-empty" data-custom-select-empty>No results found</div>
                        
                        </div>
                    
                    </div>
                    
                    @error('status')
                        
                        <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                    
                    @enderror
                
                </div>
                
                
                
                <div class="col-12">
                    
                    <label class="form-label">Detailed address</label>
                    
                    <textarea name="address" class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}" rows="3" required>{{ $addressValue }}</textarea>
                    
                    @error('address')
                        
                        <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                    
                    @enderror
                
                </div>
            
            </div>
        
        </div>
    
    </div>

@php
    
    $currentUser = auth()->user();
        
        $isStoreEmployee = $currentUser && in_array($currentUser->role, ['store_employee'], true);
    
    @endphp

    
    
    
    <div class="col-xl-5">
        
        
        
        @if ($isStoreEmployee)
            
            <input type="hidden" name="manager_id" value="">
            
            <div class="form-section h-100">
                
                <div class="form-section-title">
                    
                    <h5>Contact & Description</h5>
                
                </div>
                
                <div class="row g-3">
                    
                    <div class="col-md-4">
                        
                        <label class="form-label">Phone</label>
                        
                        <input
                            
                            type="text"
                            
                            name="phone"
                            
                            class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                            
                            value="{{ $phoneValue }}"
                            
                            required
                        
                        >
                        
                        @error('phone')
                            
                            <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                        
                        @enderror
                    
                    </div>
                    
                    <div class="col-md-4">
                        
                        <label class="form-label">Email</label>
                        
                        <input
                            
                            type="email"
                            
                            name="email"
                            
                            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            
                            value="{{ old('email', $store->email) }}"
                        
                        >
                        
                        @error('email')
                            
                            <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                        
                        @enderror
                    
                    </div>
                    
                    <div class="col-md-4">
                        
                        <label class="form-label">Opening date</label>
                        
                        <input
                            
                            type="date"
                            
                            name="opening_date"
                            
                            class="form-control {{ $errors->has('opening_date') ? 'is-invalid' : '' }}"
                            
                            value="{{ old('opening_date', optional($store->opening_date)->format('Y-m-d')) }}"
                            
                            required
                        
                        >
                        
                        @error('opening_date')
                            
                            <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                        
                        @enderror
                    
                    </div>
                    
                    <div class="col-12">
                        
                        <label class="form-label">Description</label>
                        
                        <textarea name="description" class="form-control" rows="5">{{ old('description', $store->description) }}</textarea>
                    
                    </div>
                    
                    
                    
                    <div class="col-12">
                        
                        <label class="form-label">Store brochure</label>
                        
                        <div class="file-upload-shell {{ $errors->has('brochure') ? 'is-invalid' : '' }}">
                            
                            <input
                                
                                type="file"
                                
                                name="brochure"
                                
                                id="brochure"
                                
                                class="file-upload-input {{ $errors->has('brochure') ? 'is-invalid' : '' }}"
                                
                                accept="application/pdf"
                                
                                {{ $store->exists ? '' : 'required' }}
                            
                            >
                            
                            <div class="file-upload-display">
                                
                                <span class="file-upload-name" id="brochure-file-name">No file selected yet</span>
                                
                                <strong>Choose file</strong>
                            
                            </div>
                        
                        </div>
                        
                        @error('brochure')
                            
                            <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                        
                        @enderror
                    
                    </div>
                
                </div>
            
            </div>
        
        @else
            
            
            
            <div class="form-section h-100">
                
                <div class="form-section-title">
                    
                    <h5>Staff & Reference File</h5>
                
                </div>

                
                <div class="row g-3">
                    
                    
                    
                    @if ($canEditManager)
                        
                        <div class="col-12">
                            
                            <label class="form-label">Store Manager</label>
                            
                            <div class="custom-select manager-custom-select" data-custom-select>
                                
                                <input type="hidden" name="manager_id" value="{{ $currentManagerId }}" data-custom-select-input>
                                
                                <button
                                    
                                    type="button"
                                    
                                    class="form-select custom-select-trigger {{ $errors->has('manager_id') ? 'is-invalid' : '' }}"
                                    
                                    data-custom-select-trigger
                                    
                                    aria-expanded="false"
                                
                                >
                                    
                                    <span class="custom-select-value" data-custom-select-value>
                                        
                                        @if ($currentManager)
                                            
                                            <bdi dir="ltr">{{ $currentManager->name }}</bdi>
                                        
                                        @else
                                            
                                            Select a manager
                                        
                                        @endif
                                    
                                    </span>
                                
                                </button>
                                
                                <div class="custom-select-menu" data-custom-select-menu>
                                    
                                    <div class="custom-select-search">
                                        
                                        <input
                                            
                                            type="text"
                                            
                                            class="custom-select-search-input"
                                            
                                            placeholder="Search..."
                                            
                                            data-custom-select-search
                                        
                                        >
                                    
                                    </div>
                                    
                                    <div class="custom-select-options" data-custom-select-options>
                                        
                                        @foreach ($managers as $manager)
                                            
                                            <button
                                                
                                                type="button"
                                                
                                                class="custom-select-option {{ $currentManagerId === (string) $manager->id ? 'is-selected' : '' }}"
                                                
                                                data-value="{{ $manager->id }}"
                                                
                                                data-label="{{ $manager->name }}"
                                            
                                            >
                                                
                                                <bdi dir="ltr">{{ $manager->name }}</bdi>
                                            
                                            </button>
                                        
                                        @endforeach
                                    
                                    </div>
                                    
                                    <div class="custom-select-empty" data-custom-select-empty>No results found</div>
                                
                                </div>
                            
                            </div>
                            
                            @error('manager_id')
                                
                                <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                            
                            @enderror
                        
                        </div>
                    
                    @endif
                    
                    
                    
                    <div class="col-12">
                        
                        <label class="form-label">Store Employees</label>
                        
                        
                        
                        @if ($isStoreManager)
                            
                            <div class="employee-picker {{ $errors->has('employee_ids') || $errors->has('employee_ids.*') ? 'is-invalid' : '' }}">
                                
                                <div class="employee-picker-toolbar">
                                    
                                    <div>
                                        
                                        <strong>Assigned staff members</strong>
                                    
                                    </div>
                                    
                                    <div class="employee-picker-count">
                                        
                                        {{ count($selectedEmployees) }} assigned
                                    
                                    </div>
                                
                                </div>
                                
                                <div class="employee-selection-bio">
                                    
                                    @if (count($selectedEmployees))
                                        
                                        @foreach ($selectedEmployees as $employeeId)
                                            
                                            @php($employee = $employeesById->get($employeeId))
                                            
                                            @if ($employee)
                                                
                                                <span class="employee-chip" data-id="{{ $employeeId }}">
                                                    
                                                    <span class="employee-chip-label">{{ $employee->name }}</span>
                                                
                                                </span>
                                                
                                                <input type="hidden" name="employee_ids[]" value="{{ $employeeId }}" id="employee-input-{{ $employeeId }}">
                                            
                                            @endif
                                        
                                        @endforeach
                                    
                                    @else
                                        
                                        <span class="employee-chip">
                                            
                                            <span class="employee-chip-label">No employees assigned yet</span>
                                        
                                        </span>
                                    
                                    @endif
                                
                                </div>
                            
                            </div>
                        
                        @else
                            
                            
                            
                            <div class="employee-picker {{ $errors->has('employee_ids') || $errors->has('employee_ids.*') ? 'is-invalid' : '' }}">
                                
                                <div class="employee-picker-toolbar">
                                    
                                    <div>
                                        
                                        <strong>Select staff members</strong>
                                    
                                    </div>
                                    
                                    <div class="employee-picker-count" id="employee-selection-count">
                                        
                                        {{ count($selectedEmployees) }} selected
                                    
                                    </div>
                                
                                </div>

                                
                                <div class="employee-select-shell">
                                    
                                    <div class="custom-select employee-custom-select" data-employee-select>
                                        
                                        <button
                                            
                                            type="button"
                                            
                                            class="form-select custom-select-trigger"
                                            
                                            data-employee-select-trigger
                                            
                                            aria-expanded="false"
                                        
                                        >
                                            
                                            <span class="custom-select-value" data-employee-select-value>Select an employee to add</span>
                                        
                                        </button>
                                        
                                        <div class="custom-select-menu" data-employee-select-menu>
                                            
                                            <div class="custom-select-search">
                                                
                                                <input
                                                    
                                                    type="text"
                                                    
                                                    class="custom-select-search-input"
                                                    
                                                    placeholder="Search..."
                                                    
                                                    data-employee-select-search
                                                
                                                >
                                            
                                            </div>
                                            
                                            <div class="custom-select-options" data-employee-select-options>
                                                
                                                @foreach ($employees as $employee)
                                                    
                                                    <button
                                                        
                                                        type="button"
                                                        
                                                        class="custom-select-option {{ in_array($employee->id, $selectedEmployees, true) ? 'is-selected' : '' }}"
                                                        
                                                        data-employee-id="{{ $employee->id }}"
                                                        
                                                        data-employee-name="{{ $employee->name }}"
                                                    
                                                    >
                                                        
                                                        <bdi dir="ltr">{{ $employee->name }}</bdi>
                                                    
                                                    </button>
                                                
                                                @endforeach
                                            
                                            </div>
                                            
                                            <div class="custom-select-empty" data-employee-select-empty>No results found</div>
                                        
                                        </div>
                                    
                                    </div>
                                
                                </div>

                                
                                <div class="employee-selection-bio" id="employee-selection-bio">
                                    
                                    @if (count($selectedEmployees))
                                        
                                        @foreach ($selectedEmployees as $employeeId)
                                            
                                            @php($employee = $employeesById->get($employeeId))
                                            
                                            @if ($employee)
                                                
                                                <span class="employee-chip" data-id="{{ $employeeId }}">
                                                    
                                                    <span class="employee-chip-label">{{ $employee->name }}</span>
                                                    
                                                    <button type="button" class="employee-chip-remove" aria-label="Remove">×</button>
                                                
                                                </span>
                                                
                                                <input type="hidden" name="employee_ids[]" value="{{ $employeeId }}" id="employee-input-{{ $employeeId }}">
                                            
                                            @endif
                                        
                                        @endforeach
                                    
                                    @endif
                                
                                </div>
                            
                            </div>
                        
                        @endif
                        
                        @if ($errors->has('employee_ids') || $errors->has('employee_ids.*'))
                            
                            <div class="field-note field-note-danger mt-2" role="alert">
                                
                                {{ $errors->first('employee_ids') ?: $errors->first('employee_ids.*') }}
                            
                            </div>
                        
                        @endif
                    
                    </div>
                    
                    <div class="col-12">
                        
                        <label class="form-label">Store brochure</label>
                        
                        <div class="file-upload-shell {{ $errors->has('brochure') ? 'is-invalid' : '' }}">
                            
                            <input
                                
                                type="file"
                                
                                name="brochure"
                                
                                id="brochure"
                                
                                class="file-upload-input {{ $errors->has('brochure') ? 'is-invalid' : '' }}"
                                
                                accept="application/pdf"
                                
                                {{ $store->exists ? '' : 'required' }}
                            
                            >
                            
                            <div class="file-upload-display">
                                
                                <span class="file-upload-name" id="brochure-file-name">No file selected yet</span>
                                
                                <strong>Choose file</strong>
                            
                            </div>
                        
                        </div>
                        
                        @error('brochure')
                            
                            <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                        
                        @enderror
                    
                    </div>
                
                </div>
            
            </div>
        
        @endif
    
    </div>

    
    @if (! $isStoreEmployee)
        
        <div class="col-12">
            
            <div class="form-section">
                
                <div class="form-section-title">
                    
                    <h5>Contact & Description</h5>
                
                </div>

                
                <div class="row g-3">
                    
                    <div class="col-md-4">
                        
                        <label class="form-label">Phone</label>
                        
                        <input type="text" name="phone" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" value="{{ $phoneValue }}" required>
                        
                        @error('phone')
                            
                            <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                        
                        @enderror
                    
                    </div>
                    
                    <div class="col-md-4">
                        
                        <label class="form-label">Email</label>
                        
                        <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email', $store->email) }}">
                        
                        @error('email')
                            
                            <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                        
                        @enderror
                    
                    </div>
                    
                    <div class="col-md-4">
                        
                        <label class="form-label">Opening date</label>
                        
                        <input type="date" name="opening_date" class="form-control {{ $errors->has('opening_date') ? 'is-invalid' : '' }}" value="{{ old('opening_date', optional($store->opening_date)->format('Y-m-d')) }}" required>
                        
                        @error('opening_date')
                            
                            <div class="field-note field-note-danger mt-2" role="alert">{{ $message }}</div>
                        
                        @enderror
                    
                    </div>
                    
                    <div class="col-12">
                        
                        <label class="form-label">Description</label>
                        
                        <textarea name="description" class="form-control" rows="5">{{ old('description', $store->description) }}</textarea>
                    
                    </div>
                
                </div>
            
            </div>
        
        </div>
    
    @endif

</div>

<input type="hidden" name="city" value="{{ old('city', $store->city ?? '') }}">

<script>
    
    
    document.addEventListener('DOMContentLoaded', function () {
        
        
        const brochureInput = document.getElementById('brochure');
        
        const brochureFileName = document.getElementById('brochure-file-name');
        
        const employeeSelect = document.querySelector('[data-employee-select]');
        
        const employeeTrigger = employeeSelect?.querySelector('[data-employee-select-trigger]');
        
        const employeeMenu = employeeSelect?.querySelector('[data-employee-select-menu]');
        
        const employeeOptions = employeeMenu ? Array.from(employeeMenu.querySelectorAll('.custom-select-option')) : [];
        
        const employeeSearchInput = employeeSelect?.querySelector('[data-employee-select-search]');
        
        const employeeEmptyState = employeeSelect?.querySelector('[data-employee-select-empty]');
        
        const selectionBio = document.getElementById('employee-selection-bio');
        
        const employeeSelectionCount = document.getElementById('employee-selection-count');
        
        const timeScope = document.querySelector('[data-time-format-scope]');
        
        const startInput = timeScope ? timeScope.querySelector('input[name="workday_starts_at"]') : null;
        
        const endInput = timeScope ? timeScope.querySelector('input[name="workday_ends_at"]') : null;

        
        
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

        
        
        if (timeScope) {
            
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
        
        }

        
        
        if (brochureInput && brochureFileName) {
            
            brochureInput.addEventListener('change', function () {
                
                brochureFileName.textContent = brochureInput.files.length
                    
                    ? brochureInput.files[0].name
                    
                    : 'No file selected yet';
            
            });
        
        }

        
        
        function updateCount() {
            
            if (!selectionBio) {
                
                return;
            
            }

            
            const selected = selectionBio.querySelectorAll('.employee-chip').length;
            
            if (employeeSelectionCount) {
                
                employeeSelectionCount.textContent = selected + ' selected';
            
            }
            
            const emptyState = selectionBio.querySelector('.employee-selection-empty');
            
            if (emptyState) {
                
                emptyState.style.display = selected ? 'none' : 'inline';
            
            }
        
        }

        
        
        function addEmployee(id, name) {
            
            if (!selectionBio || !id || selectionBio.querySelector('.employee-chip[data-id="' + id + '"]')) {
                
                return;
            
            }

            
            const chip = document.createElement('span');
            
            chip.className = 'employee-chip';
            
            chip.dataset.id = id;
            
            const label = document.createElement('span');
            
            label.className = 'employee-chip-label';
            
            label.textContent = name;
            
            chip.appendChild(label);

            
            const removeBtn = document.createElement('button');
            
            removeBtn.type = 'button';
            
            removeBtn.className = 'employee-chip-remove';
            
            removeBtn.setAttribute('aria-label', 'Remove');
            
            removeBtn.textContent = '×';
            
            chip.appendChild(removeBtn);

            
            const hiddenInput = document.createElement('input');
            
            hiddenInput.type = 'hidden';
            
            hiddenInput.name = 'employee_ids[]';
            
            hiddenInput.value = id;
            
            hiddenInput.id = 'employee-input-' + id;

            
            selectionBio.appendChild(chip);
            
            selectionBio.appendChild(hiddenInput);
            
            if (employeeOptions.length) {
                
                const option = employeeOptions.find(function (item) {
                    
                    return item.dataset.employeeId === id;
                
                });
                
                option?.classList.add('is-selected');
            
            }
            
            updateCount();
        
        }

        
        
        function removeEmployee(id) {
            
            if (!selectionBio) {
                
                return;
            
            }
            
            const chip = selectionBio.querySelector('.employee-chip[data-id="' + id + '"]');
            
            const input = selectionBio.querySelector('#employee-input-' + id);
            
            if (chip) {
                
                chip.remove();
            
            }
            
            if (input) {
                
                input.remove();
            
            }
            
            if (employeeOptions.length) {
                
                const option = employeeOptions.find(function (item) {
                    
                    return item.dataset.employeeId === id;
                
                });
                
                option?.classList.remove('is-selected');
            
            }
            
            updateCount();
        
        }

        
        
        function closeEmployeeMenu() {
            
            employeeSelect?.classList.remove('is-open');
            
            employeeTrigger?.setAttribute('aria-expanded', 'false');
            
            if (employeeSearchInput) {
                
                employeeSearchInput.value = '';
                
                filterEmployeeOptions('');
            
            }
        
        }

        
        
        function openEmployeeMenu() {
            
            employeeSelect?.classList.add('is-open');
            
            employeeTrigger?.setAttribute('aria-expanded', 'true');
            
            if (employeeSearchInput) {
                
                employeeSearchInput.value = '';
                
                filterEmployeeOptions('');
                
                requestAnimationFrame(function () {
                    
                    employeeSearchInput.focus();
                
                });
            
            }
        
        }

        
        
        function filterEmployeeOptions(query) {
            
            if (!employeeOptions.length) {
                
                return;
            
            }
            
            const normalized = String(query || '').trim().toLowerCase();
            
            let visibleCount = 0;
            
            employeeOptions.forEach(function (option) {
                
                const label = String(option.dataset.employeeName || option.textContent || '').toLowerCase();
                
                const visible = !normalized || label.includes(normalized);
                
                option.style.display = visible ? '' : 'none';
                
                if (visible) {
                    
                    visibleCount += 1;
                
                }
            
            });
            
            if (employeeEmptyState) {
                
                employeeEmptyState.style.display = visibleCount ? 'none' : 'block';
            
            }
        
        }

        
        
        employeeTrigger?.addEventListener('click', function () {
            
            if (!employeeSelect) {
                
                return;
            
            }
            
            if (employeeSelect.classList.contains('is-open')) {
                
                closeEmployeeMenu();
            
            } else {
                
                openEmployeeMenu();
            
            }
        
        });

        
        
        employeeSearchInput?.addEventListener('input', function () {
            
            filterEmployeeOptions(employeeSearchInput.value);
        
        });

        
        
        if (employeeOptions.length) {
            
            employeeOptions.forEach(function (option) {
                
                option.addEventListener('click', function () {
                    
                    const id = option.dataset.employeeId;
                    
                    const name = option.dataset.employeeName || option.textContent.trim();
                    
                    if (id) {
                        
                        addEmployee(id, name);
                    
                    }
                    
                    closeEmployeeMenu();
                
                });
            
            });
        
        }

        
        
        if (selectionBio) {
            
            selectionBio.addEventListener('click', function (event) {
                
                const target = event.target;
                
                if (target && target.classList.contains('employee-chip-remove')) {
                    
                    const chip = target.closest('.employee-chip');
                    
                    if (chip) {
                        
                        removeEmployee(chip.dataset.id);
                    
                    }
                
                }
            
            });
        
        }

        
        updateCount();

        
        
        document.addEventListener('click', function (event) {
            
            if (employeeSelect && !employeeSelect.contains(event.target)) {
                
                closeEmployeeMenu();
            
            }
        
        });

        
        
        const firstInvalid = document.querySelector('.is-invalid');
        
        if (firstInvalid) {
            
            firstInvalid.scrollIntoView({ block: 'center', behavior: 'smooth' });
        
        }

        
        
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

        
        
        if (timeScope) {
            
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
                
                if (! validateShiftRange(true, true)) {
                    
                    event.preventDefault();
                
                }
            
            });
        
        }

        
        
        function getOrCreateInlineError(afterEl) {
            
            if (!afterEl || !afterEl.parentElement) return null;
            
            const existing = afterEl.parentElement.querySelector(':scope > .js-inline-error');
            
            if (existing) return existing;
            
            const el = document.createElement('div');
            
            el.className = 'field-note field-note-danger mt-2 js-inline-error d-none';
            
            el.setAttribute('role', 'alert');
            
            afterEl.insertAdjacentElement('afterend', el);
            
            return el;
        
        }

        
        
        function showInlineError(afterEl, message) {
            
            const el = getOrCreateInlineError(afterEl);
            
            if (!el) return;
            
            el.textContent = message || '';
            
            el.classList.toggle('d-none', !message);
        
        }

        
        
        function clearClientErrors(root) {
            
            const scope = root || document;
            
            scope.querySelectorAll('.js-inline-error').forEach(function (el) {
                
                el.remove();
            
            });
            
            scope.querySelectorAll('.is-invalid').forEach(function (el) {
                
                el.classList.remove('is-invalid');
            
            });
        
        }

        
        
        const storeForm = document.querySelector('form[data-store-form]');
        
        if (storeForm) {
            
            storeForm.addEventListener('submit', function (event) {
                
                const submitter = event.submitter;
                
                if (!submitter || !submitter.matches('[data-explicit-save]')) {
                    
                    event.preventDefault();
                    
                    return;
                
                }

                
                
                clearClientErrors(storeForm);

                
                const requiredTextFields = [
                    
                    { name: 'name', message: 'Branch name is required.' },
                    
                    { name: 'branch_code', message: 'Branch code is required.' },
                    
                    { name: 'address', message: 'Address is required.' },
                    
                    { name: 'phone', message: 'Phone number is required.' },
                    
                    { name: 'opening_date', message: 'Opening date is required.' },
                
                ];

                
                let firstProblem = null;

                
                requiredTextFields.forEach(function (item) {
                    
                    const field = storeForm.querySelector('[name="' + item.name + '"]');
                    
                    if (!field) return;
                    
                    const value = String(field.value || '').trim();
                    
                    if (!value) {
                        
                        field.classList.add('is-invalid');
                        
                        showInlineError(field, item.message);
                        
                        firstProblem = firstProblem || field;
                    
                    }
                
                });

                
                
                const phoneField = storeForm.querySelector('[name="phone"]');
                
                if (phoneField) {
                    
                    const phone = String(phoneField.value || '').trim();
                    
                    if (phone && !/^09\d{7,18}$/.test(phone)) {
                        
                        phoneField.classList.add('is-invalid');
                        
                        showInlineError(phoneField, 'Phone number must start with 09.');
                        
                        firstProblem = firstProblem || phoneField;
                    
                    }
                
                }

                
                
                const provinceHidden = storeForm.querySelector('input[name="province_id"][data-custom-select-input]');
                
                const provinceTrigger = storeForm.querySelector('input[name="province_id"][data-custom-select-input]')?.closest('.custom-select')?.querySelector('[data-custom-select-trigger]');
                
                if (provinceHidden && !String(provinceHidden.value || '').trim()) {
                    
                    if (provinceTrigger) {
                        
                        provinceTrigger.classList.add('is-invalid');
                        
                        showInlineError(provinceTrigger.closest('.custom-select') || provinceTrigger, 'Province is required.');
                        
                        firstProblem = firstProblem || provinceTrigger;
                    
                    }
                
                }

                
                const statusHidden = storeForm.querySelector('input[name="status"][data-custom-select-input]');
                
                const statusTrigger = storeForm.querySelector('input[name="status"][data-custom-select-input]')?.closest('.custom-select')?.querySelector('[data-custom-select-trigger]');
                
                if (statusHidden && !String(statusHidden.value || '').trim()) {
                    
                    if (statusTrigger) {
                        
                        statusTrigger.classList.add('is-invalid');
                        
                        showInlineError(statusTrigger.closest('.custom-select') || statusTrigger, 'Status is required.');
                        
                        firstProblem = firstProblem || statusTrigger;
                    
                    }
                
                }

                
                const managerHidden = storeForm.querySelector('input[name="manager_id"][data-custom-select-input]');
                
                const managerTrigger = storeForm.querySelector('input[name="manager_id"][data-custom-select-input]')?.closest('.custom-select')?.querySelector('[data-custom-select-trigger]');
                
                if (managerHidden && !String(managerHidden.value || '').trim()) {
                    
                    if (managerTrigger) {
                        
                        managerTrigger.classList.add('is-invalid');
                        
                        showInlineError(managerTrigger.closest('.custom-select') || managerTrigger, 'Store manager is required.');
                        
                        firstProblem = firstProblem || managerTrigger;
                    
                    }
                
                }

                
                
                const brochure = storeForm.querySelector('input[type="file"][name="brochure"]');
                
                if (brochure && brochure.required && brochure.files && brochure.files.length === 0) {
                    
                    brochure.classList.add('is-invalid');
                    
                    const shell = brochure.closest('.file-upload-shell') || brochure;
                    
                    if (shell !== brochure) shell.classList.add('is-invalid');
                    
                    showInlineError(shell, 'The brochure file is required.');
                    
                    firstProblem = firstProblem || shell;
                
                }

                
                
                if (firstProblem) {
                    
                    event.preventDefault();
                    
                    firstProblem.scrollIntoView({ block: 'center', behavior: 'smooth' });
                
                }
            
            });
        
        }
    
    });

</script>
