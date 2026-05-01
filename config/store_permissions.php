<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Store/Branch Module Roles (Programming Block 3)
    |--------------------------------------------------------------------------
    | Centralized list of roles + permissions for the store management module.
    | This keeps role/permission strings out of controllers/views.
    */
    'roles' => [
        'admin' => 'Admin',
        'department_manager' => 'Department Manager',
        'store_manager' => 'Store Manager',
        'store_employee' => 'Store Employee',
    ],

    /*
    |--------------------------------------------------------------------------
    | Store/Branch Module Permissions
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'create_store' => 'Create store',
        'edit_store' => 'Edit store',
        'view_store' => 'View stores',
        'search_store' => 'Search stores',
        'download_store_brochure' => 'Download store brochure',
        'delete_store' => 'Delete store',
        'assign_staff_to_store' => 'Assign staff to store',
        'view_store_details' => 'View store details',
        'manage_store_staff' => 'Manage store staff',
        'manage_store_products' => 'Manage store products',
        'manage_store_warehouses' => 'Manage store warehouses',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role -> Permission Mapping
    |--------------------------------------------------------------------------
    */
    'role_permissions' => [
        'admin' => ['*'],
        'department_manager' => [
            'view_store',
            'view_store_details',
            'search_store',
            'delete_store',
            'download_store_brochure',
        ],
        // Store managers can view their assigned store and manage products/staff for it.
        'store_manager' => [
            'view_store',
            'view_store_details',
            'search_store',
            'delete_store',
            'manage_store_products',
            'manage_store_warehouses',
            'download_store_brochure',
        ],
        // Store employees can create/update and view/search stores.
        'store_employee' => [
            'create_store',
            'edit_store',
            'view_store',
            'search_store',
            'view_store_details',
            'manage_store_products',
            'download_store_brochure',
        ],
    ],
];
