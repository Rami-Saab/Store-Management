@php
    
    
    $name = $name ?? null;
    
    
    $options = $options ?? [];
    
    
    $selectedValue = (string) ($selectedValue ?? '');
    
    
    $selectedLabel = $selectedLabel ?? '';
    
    
    $selectedLabelHtml = $selectedLabelHtml ?? null;
    
    
    $wrapperClass = $wrapperClass ?? 'custom-select';
    
    
    $triggerClass = $triggerClass ?? 'custom-select-trigger';
    
    
    $hasError = !empty($hasError);
    
    
    $disabled = !empty($disabled);
    
    
    $includeMenu = $includeMenu ?? true;
    
    
    $searchPlaceholder = $searchPlaceholder ?? 'Search...';
    
    
    $inputAttributes = $inputAttributes ?? '';
    
    
    $triggerAttributes = $triggerAttributes ?? '';

    
    
    $displayHtml = $selectedLabelHtml ?? e($selectedLabel);

@endphp

<div class="{{ $wrapperClass }}" data-custom-select>
    
    
    
    <input type="hidden" name="{{ $name }}" value="{{ $selectedValue }}" data-custom-select-input {!! $inputAttributes !!}>

    
    
    
    <button
        
        type="button"
        
        class="{{ $triggerClass }} {{ $hasError ? 'is-invalid' : '' }}"
        
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
                
                
                
                @foreach ($options as $option)
                    
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
