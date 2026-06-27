@php
    
    
    $name = $name ?? '';
    
    
    $label = $label ?? '';
    
    
    $value = $value ?? '';
    
    
    $hasError = !empty($hasError);
    
    
    $inputClass = $inputClass ?? 'ab-input';

@endphp

<div>
    
    
    
    @if ($label !== '')
        
        <label class="form-label form-label-subtle">{{ $label }}</label>
    
    @endif
    
    <div class="time-input-wrap" data-time-trigger>
        
        
        
        <input
            
            type="time"
            
            name="{{ $name }}"
            
            value="{{ $value }}"
            
            class="{{ $inputClass }} {{ $hasError ? 'is-invalid' : '' }}"
            
            step="300"
            
            required
            
            data-time-input
        
        >
    
    </div>

</div>
