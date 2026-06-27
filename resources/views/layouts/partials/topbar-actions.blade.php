@php
    
    $showLogout = $showLogout ?? true;
    
    $showUser = $showUser ?? true;

@endphp

@if ($currentUser)
    
    @if ($showUser)
        
        <div class="topbar-user">
            
            <div class="topbar-user-copy">
                
                <span class="topbar-user-name">{{ $currentUser->name }}</span>
                
                @if (!empty($currentUserRole))
                    
                    <span class="topbar-user-role">{{ $currentUserRole }}</span>
                
                @endif
            
            </div>
            
            <span class="topbar-avatar" aria-hidden="true">
                
                <svg viewBox="0 0 24 24" fill="none">
                    
                    <circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.8"></circle>
                    
                    <path d="M5.5 19.5C6.7 16.9 9 15.5 12 15.5C15 15.5 17.3 16.9 18.5 19.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                
                </svg>
            
            </span>
        
        </div>
    
    @endif

@endif

@if ($showLogout)
    
    <form action="{{ route('logout', [], false) }}" method="POST" class="topbar-logout-form">
        
        @csrf
        
        <button type="submit" class="topbar-logout-btn">Log out</button>
    
    </form>

@endif
