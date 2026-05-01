@php
    
    
    $labelClass = $labelClass ?? '';
    
    $inputClass = $inputClass ?? '';
    
    $textareaClass = $textareaClass ?? '';
    
    $errorClass = $errorClass ?? '';
    
    $gridClass = $gridClass ?? '';
    
    
    $errorRole = $errorRole ?? null;
    
    
    $descriptionRows = $descriptionRows ?? 4;

    
    
    $phoneValue = $phoneValue ?? '';
    
    $emailValue = $emailValue ?? '';
    
    $openingDateValue = $openingDateValue ?? '';
    
    $descriptionValue = $descriptionValue ?? '';

@endphp

<div class="{{ $gridClass }}">
    
    <div>
        
        <label class="{{ $labelClass }}">Phone</label>
        
        
        
        <input
            
            type="text"
            
            name="phone"
            
            class="{{ $inputClass }} {{ $errors->has('phone') ? 'is-invalid' : '' }}"
            
            value="{{ $phoneValue }}"
            
            required
        
        >
        
        
        
        @error('phone')
            
            <div class="{{ $errorClass }}" @if($errorRole) role="{{ $errorRole }}" @endif>{{ $message }}</div>
        
        @enderror
    
    </div>
    
    <div>
        
        <label class="{{ $labelClass }}">Email</label>
        
        
        
        <input
            
            type="email"
            
            name="email"
            
            class="{{ $inputClass }} {{ $errors->has('email') ? 'is-invalid' : '' }}"
            
            value="{{ $emailValue }}"
        
        >
        
        
        
        @error('email')
            
            <div class="{{ $errorClass }}" @if($errorRole) role="{{ $errorRole }}" @endif>{{ $message }}</div>
        
        @enderror
    
    </div>
    
    <div>
        
        <label class="{{ $labelClass }}">Opening date</label>
        
        
        
        <input
            
            type="date"
            
            name="opening_date"
            
            class="{{ $inputClass }} {{ $errors->has('opening_date') ? 'is-invalid' : '' }}"
            
            value="{{ $openingDateValue }}"
            
            required
        
        >
        
        
        
        @error('opening_date')
            
            <div class="{{ $errorClass }}" @if($errorRole) role="{{ $errorRole }}" @endif>{{ $message }}</div>
        
        @enderror
    
    </div>

</div>

<div>
    
    <label class="{{ $labelClass }}">Description</label>
    
    <textarea
        
        name="description"
        
        rows="{{ (int) $descriptionRows }}"
        
        class="{{ $textareaClass }} {{ $errors->has('description') ? 'is-invalid' : '' }}"
    
    >{{ $descriptionValue }}</textarea>
    
    
    
    @error('description')
        
        <div class="{{ $errorClass }}" @if($errorRole) role="{{ $errorRole }}" @endif>{{ $message }}</div>
    
    @enderror

</div>
