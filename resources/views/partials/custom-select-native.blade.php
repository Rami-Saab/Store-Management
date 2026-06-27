@php
    
    
    $name = $name ?? null;
    
    
    $options = $options ?? [];
    
    
    $selectedValue = (string) ($selectedValue ?? '');
    
    
    $selectedLabel = $selectedLabel ?? '';
    
    
    $selectedLabelHtml = $selectedLabelHtml ?? null;
    
    
    $wrapperClass = $wrapperClass ?? 'custom-select';
    
    
    $selectClass = $selectClass ?? 'custom-select-native';
    
    
    $triggerClass = $triggerClass ?? 'custom-select-trigger';
    
    
    $disabled = !empty($disabled);
    
    
    $includeMenu = $includeMenu ?? true;
    
    
    $selectAttributes = $selectAttributes ?? '';
    
    
    $triggerAttributes = $triggerAttributes ?? '';
    
    
    $searchPlaceholder = $searchPlaceholder ?? 'Search...';
    
    
    $menuOptions = $menuOptions ?? $options;

    
    
    $displayHtml = $selectedLabelHtml ?? e($selectedLabel);

@endphp

<div class="{{ $wrapperClass }}" data-custom-select>
    
    
    
    <select
        
        class="{{ $selectClass }}"
        
        data-custom-select-input
        
        {{ $disabled ? 'disabled' : '' }}
        
        {!! $selectAttributes !!}
        
        @if ($name)
            
            
            
            name="{{ $name }}"
        
        @endif
    
    >
        
        
        
        @foreach ($options as $option)
            
            @php
                
                
                $optionValue = (string) ($option['value'] ?? '');
                
                $optionLabel = (string) ($option['label'] ?? $optionValue);
            
            @endphp
            
            <option value="{{ $optionValue }}" {{ $optionValue === $selectedValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
        
        @endforeach
    
    </select>

    
    
    
    <button
        
        type="button"
        
        class="{{ $triggerClass }}"
        
        data-custom-select-trigger
        
        aria-expanded="false"
        
        {{ $disabled ? 'disabled' : '' }}
        
        {!! $triggerAttributes !!}
    
    >
        
        
        
        <span class="custom-select-value" data-custom-select-value>{!! $displayHtml !!}</span>
    
    </button>

    
    
    
    @if ($includeMenu)
        
        <div class="custom-select-menu" data-custom-select-menu>
            
            <div class="custom-select-search">
                
                
                
                <input
                    
                    type="text"
                    
                    class="custom-select-search-input"
                    
                    placeholder="{{ $searchPlaceholder }}"
                    
                    data-custom-select-search
                
                >
            
            </div>
            
            <div class="custom-select-options" data-custom-select-options>
                
                
                
                @foreach ($menuOptions as $option)
                    
                    @php
                        
                        
                        $optionValue = (string) ($option['value'] ?? '');
                        
                        $optionLabel = (string) ($option['label'] ?? $optionValue);
                        
                        $optionLabelHtml = $option['label_html'] ?? null;
                    
                    @endphp
                    
                    <button
                        
                        type="button"
                        
                        class="custom-select-option {{ $optionValue === $selectedValue ? 'is-selected' : '' }}"
                        
                        data-value="{{ $optionValue }}"
                        
                        data-label="{{ $optionLabel }}"
                    
                    >
                        
                        
                        
                        @if ($optionLabelHtml)
                            
                            {!! $optionLabelHtml !!}
                        
                        @else
                            
                            <span>{{ $optionLabel }}</span>
                        
                        @endif
                    
                    </button>
                
                @endforeach
            
            </div>
            
            
            
            <div class="custom-select-empty" data-custom-select-empty>No results found</div>
        
        </div>
    
    @endif

</div>
