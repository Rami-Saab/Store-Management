@extends('layouts.app')

@section('content')
    
    
    
    <div class="page-shell">
        
        <div class="page-header">
            
            <div>
                
                <h1>Link Products</h1>
                
                <p>Permission status</p>
            
            </div>
        
        </div>

        
        
        
        <div class="card">
            
            <div class="card-body empty-state">
                
                <h1 class="table-title">403</h1>
                
                <p class="table-meta">Forbidden</p>
                
                <p class="page-summary">
                    
                    {{ $message ?? 'You do not have permission to access this page.' }}
                
                </p>
            
            </div>
        
        </div>
    
    </div>

@endsection
