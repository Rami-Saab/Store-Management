@php
    
    $currentUser = auth()->user();
    
    $canCreateStore = $currentUser?->hasPermission('create_store') ?? false;
    
    $canEditStore = $currentUser?->hasPermission('edit_store') ?? false;
    
    $canDeleteStore = $currentUser?->hasPermission('delete_store') ?? false;
    
    $canViewStore = $currentUser?->hasPermission('view_store') ?? false;
    
    $canViewStoreDetails = $currentUser?->hasPermission('view_store_details') ?? false;
    
    $canSearchStore = $currentUser?->hasPermission('search_store') ?? false;
    
    $canAssignStaff = ($currentUser?->hasPermission('assign_staff_to_store') ?? false)
        
        || ($currentUser?->hasPermission('manage_store_staff') ?? false);
    
    $canLinkProducts = $currentUser?->hasPermission('manage_store_products') ?? false;

@endphp

<!DOCTYPE html>

<html lang="en" dir="ltr">

<head>
    
    <meta charset="UTF-8">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <title>Store Management Dashboard</title>

</head>

<body class="p-5 bg-light">

<div class="container text-center">
    
    <h2 class="mb-5">store mangment control pannel    </h2>

    
    <div class="row g-4">

        
        @if ($canCreateStore)
            
            <div class="col-md-4">
                
                <a href="/store/create" class="btn btn-success w-100 py-3 fw-bold">Add a new store</a>
            
            </div>
        
        @endif

        
        @if ($canViewStore)
            
            <div class="col-md-4">
                
                <a href="/store/index" class="btn btn-info w-100 py-3 fw-bold text-white">View store branches</a>
            
            </div>
        
        @endif

        
        @if ($canSearchStore)
            
            <div class="col-md-4">
                
                <a href="/store/search" class="btn btn-primary w-100 py-3 fw-bold">Find a store</a>
            
            </div>
        
        @endif

        
        @if ($canViewStoreDetails)
            
            <div class="col-md-4">
                
                <a href="/store/details" class="btn btn-secondary w-100 py-3 fw-bold">View store details</a>
            
            </div>
        
        @endif

        
        @if ($canEditStore)
            
            <div class="col-md-4">
               
               <a href="/store/edit" class="btn btn-warning w-100 py-3 fw-bold text-white">Edit store information</a>
            
            </div>
        
        @endif

        
        @if ($canDeleteStore)
            
            <div class="col-md-4">
                
                <a href="/store/delete" class="btn btn-danger w-100 py-3 fw-bold">Delete store</a>
            
            </div>
        
        @endif

        
        @if ($canAssignStaff)
            
            <div class="col-md-4">
                
                <a href="/store/assign-manager" class="btn btn-dark w-100 py-3 fw-bold">customize the store manger</a>
            
            </div>
        
        @endif

        
        @if ($canAssignStaff)
            
            <div class="col-md-4">
                
                <a href="/store/assign-employees" class="btn w-100 py-3 fw-bold text-white" style="background-color: #90EE90; border-color: #90EE90; color: white !important;">assigning staff to the store</a>
            
            </div>
        
        @endif

        
        @if ($canLinkProducts)
            
            <div class="col-md-4">
                
                <a href="/store/attach-products" class="btn w-100 py-3 fw-bold text-white" style="background-color: #8B4513; border-color: #8B4513;">Link product to the store</a>
            
            </div>
        
        @endif

        
        <!-- <div class="col-md-4">
            
            <a href="/store/upload-brochure" class="btn btn-outline-success w-100 py-3 fw-bold">Upload store brochure</a>
        
        </div> -->

        
        <!-- <div class="col-md-4">
            
            <a href="/store/download-brochure" class="btn btn-outline-info w-100 py-3 fw-bold">Download store brochure</a>
        
        </div> -->

    
    </div>

</div>

</body>

</html>
