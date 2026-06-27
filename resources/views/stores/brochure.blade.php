- يتم فتحه عند View Brochure.

- يتم استخدامه أيضاً من StoreBrochureService لتوليد ملف PDF (عند التحميل).

- يحتوي على تصميم كامل للبرشور (ألوان/أقسام/محتوى) وقد يتضمن بيانات تجريبية/هيكلية للعرض.

@extends('layouts.app')

@php
    
    
    $patternLight = 'data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E'
        
        . '%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E';
    
    
    $patternStrong = 'data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E'
        
        . '%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E';

    
    $branches = [
        
        [
            
            'id' => 'damascus',
            
            'name' => 'Damascus Branch',
            
            'city' => 'Damascus',
            
            'isMain' => true,
            
            'address' => 'Mezzeh Autostrad, Damascus, Syria',
            
            'coordinates' => [
                
                'lat' => 33.5138,
                
                'lng' => 36.2765,
            
            ],
            
            'phone' => '+963 0987 111 001',
            
            'email' => 'damascus@healthcare.sy',
            
            'departments' => [
                
                'Emergency Medicine',
                
                'Cardiology',
                
                'Neurology',
                
                'Orthopedics',
                
                'Pediatrics',
                
                'Obstetrics & Gynecology',
                
                'Internal Medicine',
                
                'General Surgery',
                
                'ICU & Critical Care',
                
                'Radiology & Imaging',
                
                'Laboratory Services',
                
                'Pharmacy',
            
            ],
            
            'facilities' => [
                
                '24/7 Emergency Department',
                
                'Advanced Operating Rooms (12 Rooms)',
                
                'Cardiac Catheterization Lab',
                
                'MRI & CT Scan Center',
                
                'Neonatal Intensive Care Unit',
                
                'Hemodialysis Center',
                
                'Blood Bank',
                
                'Fully Equipped Laboratory',
                
                'On-site Pharmacy',
                
                'Patient Cafeteria',
                
                'Prayer Room',
                
                'Free WiFi Throughout',
            
            ],
            
            'specialServices' => [
                
                'Heart Surgery & Interventional Cardiology',
                
                'Neurosurgery & Spine Center',
                
                'Comprehensive Cancer Care',
                
                'Advanced Maternity & Delivery Suites',
                
                'Pediatric Emergency Care',
                
                '24/7 Stroke & Heart Attack Response',
            
            ],
            
            'operatingHours' => [
                
                'weekdays' => '24 Hours',
                
                'saturday' => '24 Hours',
                
                'sunday' => '24 Hours',
                
                'emergency' => 'Always Open - 24/7/365',
            
            ],
            
            'totalBeds' => 350,
            
            'icuBeds' => 45,
            
            'parkingSpaces' => 200,
            
            'yearEstablished' => 1995,
            
            'certifications' => [
                
                'Apple',
                
                'Samsung',
                
                'Sony',
                
                'Lenovo',
            
            ],
            
            'highlights' => [
                
                'Damascus Branch is the flagship with the widest specialty coverage',
                
                'Largest emergency and ICU capacity in the network',
                
                'Advanced cardiac and neuro programs under one roof',
                
                'Multilingual clinical teams for international patient support',
            
            ],
            
            'color' => 'from-[#3b82f6] to-[#9333ea]',
        
        ],
        
        [
            
            'id' => 'aleppo',
            
            'name' => 'Aleppo Branch',
            
            'city' => 'Aleppo',
            
            'isMain' => false,
            
            'address' => 'Furqan District, Aleppo, Syria',
            
            'coordinates' => [
                
                'lat' => 36.2021,
                
                'lng' => 37.1343,
            
            ],
            
            'phone' => '+963 0987 222 003',
            
            'email' => 'aleppo@healthcare.sy',
            
            'departments' => [
                
                'Emergency Medicine',
                
                'General Surgery',
                
                'Orthopedics',
                
                'Pediatrics',
                
                'Internal Medicine',
                
                'Obstetrics & Gynecology',
                
                'ENT',
                
                'Ophthalmology',
                
                'Dermatology',
                
                'Radiology',
                
                'Laboratory',
            
            ],
            
            'facilities' => [
                
                '24/7 Emergency Services',
                
                'Modern Operating Rooms (6 Rooms)',
                
                'Digital X-Ray & Ultrasound',
                
                'Maternity Ward',
                
                'Pediatric Ward',
                
                'Intensive Care Unit',
                
                'Laboratory Services',
                
                'Pharmacy',
                
                'Ambulance Services',
                
                'Patient Waiting Lounges',
                
                'Accessible Parking',
            
            ],
            
            'specialServices' => [
                
                'Trauma & Emergency Care',
                
                'Maternal & Child Health Services',
                
                'Orthopedic Surgery & Rehabilitation',
                
                'Chronic Disease Management',
                
                'Preventive Health Screenings',
            
            ],
            
            'operatingHours' => [
                
                'weekdays' => '24 Hours',
                
                'saturday' => '24 Hours',
                
                'sunday' => '24 Hours',
                
                'emergency' => 'Always Open - 24/7/365',
            
            ],
            
            'totalBeds' => 180,
            
            'icuBeds' => 20,
            
            'parkingSpaces' => 100,
            
            'yearEstablished' => 2003,
            
            'certifications' => [
                
                'Huawei',
                
                'Xiaomi',
                
                'Oppo',
                
                'OnePlus',
            
            ],
            
            'highlights' => [
                
                'Aleppo Branch is the northern hub for trauma and emergency response',
                
                'Strong maternal and pediatric care with rapid triage',
                
                'On-site orthopedic surgery and rehabilitation programs',
                
                'Community screening and outreach across northern districts',
            
            ],
            
            'color' => 'from-[#6366f1] to-[#2563eb]',
        
        ],
        
        [
            
            'id' => 'homs',
            
            'name' => 'Homs Branch',
            
            'city' => 'Homs',
            
            'isMain' => false,
            
            'address' => 'Al-Hamra District, Homs, Syria',
            
            'coordinates' => [
                
                'lat' => 34.7298,
                
                'lng' => 36.7156,
            
            ],
            
            'phone' => '+963 0987 333 002',
            
            'email' => 'homs@healthcare.sy',
            
            'departments' => [
                
                'Emergency Medicine',
                
                'Internal Medicine',
                
                'General Surgery',
                
                'Orthopedics',
                
                'Pediatrics',
                
                'Obstetrics & Gynecology',
                
                'Cardiology',
                
                'Neurology',
                
                'Radiology',
                
                'Laboratory',
            
            ],
            
            'facilities' => [
                
                '24/7 Emergency Department',
                
                'Surgical Units (5 Rooms)',
                
                'Cardiology Unit',
                
                'Dialysis Center',
                
                'CT Scan & Ultrasound',
                
                'Maternity Services',
                
                'ICU',
                
                'Central Laboratory',
                
                'Pharmacy',
                
                'Ambulance Fleet',
                
                'Comfortable Patient Rooms',
            
            ],
            
            'specialServices' => [
                
                'Cardiovascular Care Center',
                
                'Kidney Disease & Dialysis Services',
                
                "Women's Health Programs",
                
                'Diabetes Management Clinic',
                
                'Sports Medicine & Rehabilitation',
            
            ],
            
            'operatingHours' => [
                
                'weekdays' => '24 Hours',
                
                'saturday' => '24 Hours',
                
                'sunday' => '24 Hours',
                
                'emergency' => 'Always Open - 24/7/365',
            
            ],
            
            'totalBeds' => 150,
            
            'icuBeds' => 18,
            
            'parkingSpaces' => 80,
            
            'yearEstablished' => 2006,
            
            'certifications' => [
                
                'Dell',
                
                'HP',
                
                'ASUS',
                
                'MSI',
            
            ],
            
            'highlights' => [
                
                'Homs Branch serves as the central hub for middle Syria',
                
                'Free and fast delivery throughout the governorate',
                
                'Reasonable prices and wonderful and distinctive products',
                
                "A group dedicated to ordering personal and instant products",
            
            ],
            
            'color' => 'from-[#a855f7] to-[#db2777]',
        
        ],
        
        [
            
            'id' => 'suwayda',
            
            'name' => 'As-Suwayda Branch',
            
            'city' => 'As-Suwayda',
            
            'isMain' => false,
            
            'address' => 'Main Street, As-Suwayda, Syria',
            
            'coordinates' => [
                
                'lat' => 32.7089,
                
                'lng' => 36.5646,
            
            ],
            
            'phone' => '+963 0987 555 005',
            
            'email' => 'suwayda@healthcare.sy',
            
            'departments' => [
                
                'Emergency Medicine',
                
                'Internal Medicine',
                
                'General Surgery',
                
                'Pediatrics',
                
                'Obstetrics & Gynecology',
                
                'Orthopedics',
                
                'ENT',
                
                'Dentistry',
                
                'Radiology',
                
                'Laboratory',
            
            ],
            
            'facilities' => [
                
                'Emergency Services 24/7',
                
                'Operating Rooms (4 Rooms)',
                
                'Maternity Ward',
                
                'Pediatric Care Unit',
                
                'Digital Radiology',
                
                'Ultrasound Services',
                
                'Clinical Laboratory',
                
                'Dental Clinic',
                
                'Pharmacy',
                
                'Patient Recovery Rooms',
                
                'Family Waiting Areas',
            
            ],
            
            'specialServices' => [
                
                'Mother & Baby Care',
                
                'Pediatric Vaccination Programs',
                
                'Dental Care Center',
                
                'Health Education & Wellness',
                
                'Chronic Pain Management',
            
            ],
            
            'operatingHours' => [
                
                'weekdays' => '7:00 AM - 10:00 PM',
                
                'saturday' => '8:00 AM - 8:00 PM',
                
                'sunday' => '9:00 AM - 5:00 PM',
                
                'emergency' => '24/7 Emergency Services',
            
            ],
            
            'totalBeds' => 90,
            
            'icuBeds' => 10,
            
            'parkingSpaces' => 50,
            
            'yearEstablished' => 2010,
            
            'certifications' => [
                
                'JBL',
                
                'Anker',
                
                'Beats',
                
                'Soundcore',
            
            ],
            
            'highlights' => [
                
                'As‑Suwayda Branch focuses on family medicine and preventive care',
                
                'Comprehensive dental and pediatric services for the community',
                
                'Wellness education and chronic care follow-ups',
                
                'Friendly outpatient experience with fast-access clinics',
            
            ],
            
            'color' => 'from-[#14b8a6] to-[#059669]',
        
        ],
        
        [
            
            'id' => 'latakia',
            
            'name' => 'Latakia Branch',
            
            'city' => 'Latakia',
            
            'isMain' => false,
            
            'address' => 'Corniche, Latakia, Syria',
            
            'coordinates' => [
                
                'lat' => 35.5308,
                
                'lng' => 35.7819,
            
            ],
            
            'phone' => '+963 0987 444 004',
            
            'email' => 'latakia@healthcare.sy',
            
            'departments' => [
                
                'Emergency Medicine',
                
                'Internal Medicine',
                
                'Cardiology',
                
                'General Surgery',
                
                'Orthopedics',
                
                'Pediatrics',
                
                'Obstetrics & Gynecology',
                
                'Dermatology',
                
                'Ophthalmology',
                
                'Radiology',
                
                'Laboratory',
            
            ],
            
            'facilities' => [
                
                '24/7 Emergency Care',
                
                'Advanced Surgery Suites (7 Rooms)',
                
                'Cardiac Care Unit',
                
                'MRI & CT Scanner',
                
                "Women's Health Center",
                
                'Pediatric Department',
                
                'ICU & HDU',
                
                'Full Laboratory Services',
                
                '24-Hour Pharmacy',
                
                'Seaside Patient Rooms',
                
                'Ample Parking',
            
            ],
            
            'specialServices' => [
                
                'Advanced Cardiac Imaging',
                
                'Minimally Invasive Surgery',
                
                'Comprehensive Eye Care',
                
                'Skin & Cosmetic Dermatology',
                
                'Marine & Diving Medicine',
            
            ],
            
            'operatingHours' => [
                
                'weekdays' => '24 Hours',
                
                'saturday' => '24 Hours',
                
                'sunday' => '24 Hours',
                
                'emergency' => 'Always Open - 24/7/365',
            
            ],
            
            'totalBeds' => 140,
            
            'icuBeds' => 16,
            
            'parkingSpaces' => 90,
            
            'yearEstablished' => 2008,
            
            'certifications' => [
                
                'Canon',
                
                'GoPro',
                
                'Nintendo',
                
                'Microsoft Xbox',
            
            ],
            
            'highlights' => [
                
                'Latakia Branch is the coastal flagship for advanced cardiac care',
                
                'Seaside recovery suites with expanded imaging services',
                
                'Comprehensive eye care and dermatology clinics',
                
                'Unique marine and diving medicine expertise',
            
            ],
            
            'color' => 'from-[#06b6d4] to-[#2563eb]',
        
        ],
        
        [
            
            'id' => 'hama',
            
            'name' => 'Hama Branch',
            
            'city' => 'Hama',
            
            'isMain' => false,
            
            'address' => 'Al-Quwatli Street, Hama, Syria',
            
            'coordinates' => [
                
                'lat' => 35.1324,
                
                'lng' => 36.7505,
            
            ],
            
            'phone' => '+963 0987 666 006',
            
            'email' => 'hama@healthcare.sy',
            
            'departments' => [
                
                'Emergency Medicine',
                
                'Internal Medicine',
                
                'General Surgery',
                
                'Orthopedics',
                
                'Pediatrics',
                
                'Obstetrics & Gynecology',
                
                'Neurology',
                
                'ENT',
                
                'Urology',
                
                'Radiology',
                
                'Laboratory',
            
            ],
            
            'facilities' => [
                
                '24/7 Emergency Services',
                
                'Modern Operating Theaters (5 Rooms)',
                
                'Neurology Department',
                
                'Maternity & Delivery Rooms',
                
                'Pediatric Ward',
                
                'Intensive Care',
                
                'Digital Imaging Center',
                
                'Advanced Laboratory',
                
                'Pharmacy Services',
                
                'Recovery Suites',
                
                'Visitor Lounges',
            
            ],
            
            'specialServices' => [
                
                'Neurological Care & Diagnostics',
                
                'Urological Treatments',
                
                'Maternity Care Excellence',
                
                'Pediatric Urgent Care',
                
                'Rehabilitation Services',
            
            ],
            
            'operatingHours' => [
                
                'weekdays' => '24 Hours',
                
                'saturday' => '24 Hours',
                
                'sunday' => '24 Hours',
                
                'emergency' => 'Always Open - 24/7/365',
            
            ],
            
            'totalBeds' => 120,
            
            'icuBeds' => 14,
            
            'parkingSpaces' => 70,
            
            'yearEstablished' => 2009,
            
            'certifications' => [
                
                'LG',
                
                'TCL',
                
                'PlayStation',
                
                'Xiaomi TV',
            
            ],
            
            'highlights' => [
                
                "Hama Branch anchors central Syria with multi-specialty care",
                
                'Expert neurological diagnostics and treatment programs',
                
                'Dedicated maternity and pediatric urgent care',
                
                'Rehabilitation services with patient-centered support',
            
            ],
            
            'color' => 'from-[#f97316] to-[#dc2626]',
        
        ],
    
    ];

    
    $branchId = null;
    
    if (isset($store) && $store) {
        
        $code = strtoupper((string) ($store->branch_code ?? ''));
        
        $branchId = match (true) {
            
            str_starts_with($code, 'DAM') => 'damascus',
            
            str_starts_with($code, 'ALP') => 'aleppo',
            
            str_starts_with($code, 'HMS') => 'homs',
            
            str_starts_with($code, 'SWD') => 'suwayda',
            
            str_starts_with($code, 'LAT') => 'latakia',
            
            str_starts_with($code, 'HMA') => 'hama',
            
            default => null,
        
        };
    
    }
    
    $branchId = $branchId ?: 'damascus';
    
    $branch = null;
    
    foreach ($branches as $candidate) {
        
        if ($candidate['id'] === $branchId) {
            
            $branch = $candidate;
            
            break;
        
        }
    
    }
    
    $emailPrefix = $branch ? (explode('@', $branch['email'])[0] ?? $branch['email']) : '';
    
    $branchTitle = $branch ? $branch['name'] : 'Branch Brochure';
    
    $pageTitle = $branchTitle.' Brochure';
    
    $linkedProducts = isset($store) && $store
        
        ? $store->products->pluck('name')->filter()->values()
        
        : collect();
    
    $productCount = $linkedProducts->count();
    
    $employeeCount = isset($store) && $store ? $store->employees->count() : 0;
    
    $heroName = $branch['name'] ?? 'Branch';
    
    if (isset($store) && $store) {
        
        $heroName = \App\Support\EnglishPlaceNames::branchDisplayName($store->branch_code, $store->name);
    
    }
    
    $heroTitle = $heroName;
    
    $heroDescription = 'A key store branch delivering curated products, reliable staffing, and fast customer service.';
    
    $openedYear = '-';
    
    if (isset($store) && $store && $store->opening_date) {
        
        $openedYear = \Carbon\Carbon::parse($store->opening_date)->format('Y');
    
    } elseif (isset($store) && $store && $store->created_at) {
        
        $openedYear = $store->created_at->format('Y');
    
    } elseif (isset($branch['yearEstablished'])) {
        
        $openedYear = (string) $branch['yearEstablished'];
    
    }
    
    $branchStatusLabel = isset($store) && $store
        
        ? match ($store->status) {
            
            'active' => 'Active',
            
            'inactive' => 'Inactive',
            
            'under_maintenance' => 'Under maintenance',
            
            default => (string) $store->status,
        
        }
        
        : 'Active';
    
    $displayBranchCode = isset($store) && $store && $store->branch_code
        
        ? strtoupper((string) $store->branch_code)
        
        : match ($branchId) {
            
            'damascus' => 'DAM-001',
            
            'aleppo' => 'ALP-003',
            
            'homs' => 'HMS-002',
            
            'suwayda' => 'SWD-005',
            
            'latakia' => 'LAT-004',
            
            'hama' => 'HMA-006',
            
            default => '—',
        
        };
    
    $theme = \App\Support\BranchTheme::themeForBranchCode($displayBranchCode, $branchId);
    
    $heroGradient = $theme['gradient'];
    
    $branchColor = $theme['tailwind'];

    
    $categoryDefinitions = [
        
        [
            
            'label' => 'Phones & Tablets',
            
            'keywords' => ['iphone', 'galaxy', 'pixel', 'oneplus', 'huawei', 'oppo', 'xiaomi', 'phone', 'ipad', 'tab'],
        
        ],
        
        [
            
            'label' => 'Laptops & Computers',
            
            'keywords' => ['laptop', 'macbook', 'thinkpad', 'legion', 'victus', 'rog', 'xps', 'g14', 'g15', 'katana', 'notebook'],
        
        ],
        
        [
            
            'label' => 'Gaming & Consoles',
            
            'keywords' => ['playstation', 'ps5', 'ps4', 'xbox', 'nintendo', 'switch', 'console', 'controller'],
        
        ],
        
        [
            
            'label' => 'Audio & Headphones',
            
            'keywords' => ['headphones', 'earbuds', 'buds', 'airpods', 'soundcore', 'sony wf', 'wh-', 'jbl', 'beats'],
        
        ],
        
        [
            
            'label' => 'Cameras & Action Cams',
            
            'keywords' => ['camera', 'gopro', 'eos', 'alpha'],
        
        ],
        
        [
            
            'label' => 'TVs & Displays',
            
            'keywords' => ['tv', 'bravia', 'oled', 'qled'],
        
        ],
        
        [
            
            'label' => 'Wearables & Smartwatches',
            
            'keywords' => ['watch', 'smartwatch', 'galaxy watch', 'apple watch'],
        
        ],
        
        [
            
            'label' => 'Accessories & Chargers',
            
            'keywords' => ['charger', 'cable', 'power bank', 'powercore'],
        
        ],
    
    ];

    
    $productCategories = collect();
    
    foreach ($linkedProducts as $productName) {
        
        $name = strtolower((string) $productName);
        
        foreach ($categoryDefinitions as $definition) {
            
            foreach ($definition['keywords'] as $keyword) {
                
                if ($keyword !== '' && str_contains($name, $keyword)) {
                    
                    $productCategories->push($definition['label']);
                    
                    break 2;
                
                }
            
            }
        
        }
    
    }
    
    $productCategories = $productCategories->unique()->values();
    
    if ($productCategories->isEmpty()) {
        
        $productCategories = collect([
            
            'Electronics & Devices',
            
            'Phones & Tablets',
            
            'Laptops & Computers',
            
            'Accessories & Chargers',
        
        ]);
    
    }

    
    $productSpecs = collect([
        
        'Original products with verified authenticity',
        
        'Latest models with multiple storage options',
        
        'High-performance hardware and smooth multitasking',
        
        'Long-lasting battery life with fast charging support',
        
        'Warranty coverage and after-sales service',
    
    ]);

    
    $brandDefinitions = [
        
        'Apple' => ['apple', 'iphone', 'ipad', 'macbook', 'airpods', 'apple watch'],
        
        'Samsung' => ['samsung', 'galaxy'],
        
        'Sony' => ['sony', 'bravia'],
        
        'PlayStation' => ['playstation', 'ps5', 'ps4', 'dualsense'],
        
        'Microsoft Xbox' => ['xbox'],
        
        'Nintendo' => ['nintendo', 'switch'],
        
        'ASUS' => ['asus', 'rog'],
        
        'Lenovo' => ['lenovo', 'thinkpad', 'legion'],
        
        'Dell' => ['dell', 'xps'],
        
        'HP' => ['hp', 'victus'],
        
        'MSI' => ['msi', 'katana'],
        
        'Acer' => ['acer'],
        
        'Canon' => ['canon'],
        
        'GoPro' => ['gopro'],
        
        'LG' => ['lg', 'oled'],
        
        'TCL' => ['tcl', 'qled'],
        
        'Xiaomi' => ['xiaomi'],
        
        'Google' => ['google', 'pixel'],
        
        'OnePlus' => ['oneplus'],
        
        'Oppo' => ['oppo'],
        
        'Huawei' => ['huawei'],
        
        'JBL' => ['jbl'],
        
        'Beats' => ['beats'],
        
        'Anker' => ['anker', 'soundcore', 'powercore'],
    
    ];

    
    $brandNames = collect();
    
    foreach ($linkedProducts as $productName) {
        
        $name = strtolower((string) $productName);
        
        foreach ($brandDefinitions as $brand => $keywords) {
            
            foreach ($keywords as $keyword) {
                
                if ($keyword !== '' && str_contains($name, $keyword)) {
                    
                    $brandNames->push($brand);
                    
                    break 2;
                
                }
            
            }
        
        }
    
    }
    
    $brandNames = $brandNames->unique()->values();
    
    if ($brandNames->isEmpty()) {
        
        $brandNames = collect(['No linked brands yet']);
    
    }

    
    $contactAddress = '';
    
    $contactPhone = '';
    
    $contactEmail = '';

    
    if (isset($store) && $store) {
        
        $addressOverride = \App\Support\EnglishPlaceNames::branchAddressByCode($store->branch_code ?? '');
        
        $storeAddress = trim((string) ($store->address ?? ''));
        
        $storeCity = trim((string) ($store->city ?? ''));
        
        $storeProvinceName = trim((string) ($store->province?->name ?? ''));
        
        $storeProvinceCode = trim((string) ($store->province?->code ?? ''));
        
        $storeProvinceEnglish = $storeProvinceCode !== ''
            
            ? (\App\Support\EnglishPlaceNames::provinceByCode($storeProvinceCode) ?: $storeProvinceName)
            
            : $storeProvinceName;

        
        if ($storeAddress !== '') {
            
            $contactAddress = $storeAddress;
        
        } elseif ($addressOverride !== '') {
            
            $contactAddress = $addressOverride;
        
        } elseif ($storeCity !== '' && $storeProvinceEnglish !== '') {
            
            $contactAddress = $storeProvinceEnglish.', '.$storeCity;
        
        } elseif ($storeCity !== '') {
            
            $contactAddress = $storeCity;
        
        } elseif ($storeProvinceEnglish !== '') {
            
            $contactAddress = $storeProvinceEnglish;
        
        }

        
        $contactPhone = trim((string) ($store->phone ?? ''));
        
        $contactEmail = trim((string) ($store->email ?? ''));
    
    }

    
    if ($contactAddress === '') {
        
        $contactAddress = $branch['address'] ?? '';
    
    }
    
    if ($contactPhone === '') {
        
        $contactPhone = $branch['phone'] ?? '';
    
    }
    
    if ($contactEmail === '') {
        
        $contactEmail = $branch['email'] ?? '';
    
    }

    
    $phoneOverrides = [
        
        'DAM-001' => '0987111001',
        
        'HMS-002' => '0987333002',
        
        'ALP-003' => '0987222003',
        
        'LAT-004' => '0987444004',
        
        'SWD-005' => '0987555005',
        
        'HMA-006' => '0987666006',
    
    ];
    
    if (isset($store) && $store) {
        
        $branchCode = strtoupper((string) ($store->branch_code ?? ''));
        
        if ($branchCode !== '' && isset($phoneOverrides[$branchCode])) {
            
            $contactPhone = $phoneOverrides[$branchCode];
        
        }
    
    }
    
    $contactPhone = \App\Support\UserContact::phone($contactPhone, false);

    
    $mapQuery = trim((string) $contactAddress);
    
    $mapUrl = $mapQuery !== '' ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($mapQuery) : 'https://www.google.com/maps';

    
    $urgentPhoneOverrides = [
        
        'DAM-001' => '0911100001',
        
        'HMS-002' => '0933200002',
        
        'ALP-003' => '0922300003',
        
        'LAT-004' => '0944400004',
        
        'SWD-005' => '0955500005',
        
        'HMA-006' => '0966600006',
    
    ];
    
    $urgentHotline = $contactPhone;
    
    if (isset($store) && $store) {
        
        $branchCode = strtoupper((string) ($store->branch_code ?? ''));
        
        if ($branchCode !== '' && isset($urgentPhoneOverrides[$branchCode])) {
            
            $urgentHotline = $urgentPhoneOverrides[$branchCode];
        
        }
    
    }
    
    $urgentHotline = \App\Support\UserContact::phone($urgentHotline, false);

    
    $workingHoursText = '';
    
    $workdayStart = null;
    
    $workdayEnd = null;
    
    if (isset($store) && $store) {
        
        $workingHoursText = trim((string) ($store->working_hours ?? ''));
        
        $workdayStart = $store->workday_starts_at ? substr((string) $store->workday_starts_at, 0, 5) : null;
        
        $workdayEnd = $store->workday_ends_at ? substr((string) $store->workday_ends_at, 0, 5) : null;
    
    }

    
    $durationMinutes = null;
    
    if ($workdayStart && $workdayEnd) {
        
        try {
            
            $start = \Carbon\Carbon::createFromFormat('H:i', $workdayStart);
            
            $end = \Carbon\Carbon::createFromFormat('H:i', $workdayEnd);
            
            if ($end->lessThanOrEqualTo($start)) {
                
                $end->addDay();
            
            }
            
            $durationMinutes = $start->diffInMinutes($end);
        
        } catch (\Throwable $e) {
            
            $durationMinutes = null;
        
        }
    
    }

    
    if ($durationMinutes === null) {
        
        $fallbackText = $workingHoursText !== '' ? $workingHoursText : '9:00 AM - 10:00 PM';
        
        if (preg_match_all('/(\d{1,2}:\d{2}\s*[AP]M)/i', $fallbackText, $matches) && count($matches[1]) >= 2) {
            
            try {
                
                $start = \Carbon\Carbon::createFromFormat('g:i A', strtoupper($matches[1][0]));
                
                $end = \Carbon\Carbon::createFromFormat('g:i A', strtoupper($matches[1][1]));
                
                if ($end->lessThanOrEqualTo($start)) {
                    
                    $end->addDay();
                
                }
                
                $durationMinutes = $start->diffInMinutes($end);
            
            } catch (\Throwable $e) {
                
                $durationMinutes = null;
            
            }
        
        } elseif (preg_match_all('/(\d{1,2}:\d{2})/', $fallbackText, $matches) && count($matches[1]) >= 2) {
            
            try {
                
                $start = \Carbon\Carbon::createFromFormat('H:i', $matches[1][0]);
                
                $end = \Carbon\Carbon::createFromFormat('H:i', $matches[1][1]);
                
                if ($end->lessThanOrEqualTo($start)) {
                    
                    $end->addDay();
                
                }
                
                $durationMinutes = $start->diffInMinutes($end);
            
            } catch (\Throwable $e) {
                
                $durationMinutes = null;
            
            }
        
        } elseif (stripos($fallbackText, '24') !== false) {
            
            $durationMinutes = 24 * 60;
        
        }
    
    }

    
    if ($durationMinutes === null) {
        
        $durationMinutes = 13 * 60;
    
    }

    
    $hours = $durationMinutes / 60;
    
    $hoursFormatted = fmod($hours, 1.0) === 0.0 ? number_format($hours, 0) : rtrim(rtrim(number_format($hours, 2), '0'), '.');
    
    $workingHoursDuration = $hoursFormatted.' Hours';

@endphp

@section('title', $pageTitle)

@section('page_subtitle', '')

@push('head')
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        
        .brochure-page { margin: -1.35rem -1.5rem -1.5rem; }
        
        .brochure-hero { min-height: calc(100vh - 90px); }
        
        .brochure-content { margin-top: 0 !important; }
        
        @media (max-width: 900px) {
            
            .brochure-page { margin: -1.35rem -1rem -1.5rem; }
            
            .brochure-hero { min-height: calc(100vh - 80px); }
        
        }
        
        @media print {
            
            .main-sidebar,
            
            .content-topbar {
                
                display: none !important;
            
            }
            
            .app-main {
                
                padding: 0 !important;
                
                background: #fff !important;
            
            }
        
        }
    
    </style>

@endpush

@section('content')

<div class="brochure-page">

@if (! $branch)
    
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        
        <div class="text-center">
            
            <h1 class="text-4xl mb-4">Branch Not Found</h1>
            
            <a
                
                href="{{ route('stores.index', [], false) }}"
                
                class="text-blue-600 hover:underline flex items-center gap-2 justify-center"
            
            >
                
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                
                Return to All Branches
            
            </a>
        
        </div>
    
    </div>

@else
    
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 brochure-shell">
        
        <nav class="bg-white/80 backdrop-blur-lg shadow-md sticky top-0 z-50 print:hidden border-b border-gray-200" data-brochure-nav>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                
                <div class="flex items-center justify-between">
                    
                    <a
                        
                        href="{{ route('stores.index', [], false) }}"
                        
                        class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors group"
                    
                    >
                        
                        <i data-lucide="arrow-left" class="w-5 h-5 group-hover:-translate-x-1 transition-transform"></i>
                        
                        <span>Back to All Branches</span>
                    
                    </a>

                    
                    <button
                        
                        type="button"
                        
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all hover:scale-105"
                        
                        data-brochure-download
                    
                    >
                        
                        <span class="brochure-download-label flex items-center gap-2">
                            
                            <i data-lucide="download" class="w-5 h-5"></i>
                            
                            <span>Download Brochure</span>
                        
                        </span>
                    
                    </button>
                
                </div>
            
            </div>
        
        </nav>
        
        <div id="brochure-content" class="print-content">
        
        <section class="relative overflow-hidden print:h-auto brochure-hero" style="background: {{ $heroGradient }};">
            
            <div class="absolute inset-0" style="background: {{ $heroGradient }};">
                
                <div class="absolute -top-24 -right-24 w-80 h-80 bg-white/10 rounded-full"></div>
                
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-black/10 rounded-full"></div>
            
            </div>

            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32">
                
                <div class="max-w-4xl">
                    
                    <div class="flex flex-wrap items-center gap-3 mb-8 brochure-hero-badges">
                        
                        @if ($branch['isMain'])
                            
                            <div class="inline-flex items-center gap-2 bg-yellow-400/90 backdrop-blur-sm px-5 py-2.5 rounded-full shadow-lg">
                                
                                <i data-lucide="star" class="w-5 h-5 text-yellow-900 fill-yellow-900"></i>
                                
                                <span class="text-yellow-900">Main Branch</span>
                            
                            </div>
                        
                        @endif
                        
                        <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-5 py-2.5 rounded-full border border-white/30">
                            
                            <i data-lucide="calendar" class="w-5 h-5 text-white"></i>
                            
                            <span class="text-white">Serving Since {{ $openedYear }}</span>
                        
                        </div>
                        
                        <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-5 py-2.5 rounded-full border border-white/30">
                            
                            <i data-lucide="check-circle-2" class="w-5 h-5 text-white"></i>
                            
                            <span class="text-white">Status: {{ $branchStatusLabel }}</span>
                        
                        </div>
                    
                    </div>

                    
                    <h1 class="text-6xl md:text-7xl lg:text-8xl text-white mb-6 leading-tight tracking-tight">
                        
                        {{ $heroTitle }}
                    
                    </h1>
                    
                    <p class="text-xl text-white/85 mb-12 max-w-2xl leading-relaxed">
                        
                        {{ $heroDescription }}
                    
                    </p>

                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12 brochure-hero-stats">
                        
                        <div class="bg-white/15 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/20">
                            
                            <div class="flex flex-col items-center text-center">
                                
                                <i data-lucide="users" class="w-8 h-8 text-white mb-2"></i>
                                
                                <p class="text-3xl text-white mb-1">{{ $employeeCount }}</p>
                                
                                <p class="text-sm text-white/80">Staff Members</p>
                            
                            </div>
                        
                        </div>

                        
                        <div class="bg-white/15 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/20">
                            
                            <div class="flex flex-col items-center text-center">
                                
                                <i data-lucide="package" class="w-8 h-8 text-white mb-2"></i>
                                
                                <p class="text-3xl text-white mb-1">{{ $productCount }}</p>
                                
                                <p class="text-sm text-white/80">Linked Products</p>
                            
                            </div>
                        
                        </div>

                        
                        <div class="bg-white/15 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/20">
                            
                            <div class="flex flex-col items-center text-center">
                                
                                <i data-lucide="hash" class="w-8 h-8 text-white mb-2"></i>
                                
                                <p class="text-3xl text-white mb-1">{{ $displayBranchCode }}</p>
                                
                                <p class="text-sm text-white/80">Branch Code</p>
                            
                            </div>
                        
                        </div>

                        
                        <div class="bg-white/15 backdrop-blur-md px-6 py-4 rounded-2xl border border-white/20">
                            
                            <div class="flex flex-col items-center text-center">
                                
                                <i data-lucide="clock" class="w-8 h-8 text-white mb-2"></i>
                                
                                <p class="text-3xl text-white mb-1">{{ $workingHoursDuration }}</p>
                                
                                <p class="text-sm text-white/80">Working Hours</p>
                            
                            </div>
                        
                        </div>
                    
                    </div>

                
                </div>
            
            </div>

            
            <div class="relative"></div>
        
        </section>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 pb-20 brochure-content">
            
            <div class="brochure-page-2">
                
                <section class="mb-16 brochure-section brochure-why">
                    
                    <div class="bg-white rounded-3xl p-10 shadow-2xl border border-gray-100 brochure-card">
                        
                        <div class="flex items-center gap-4 mb-8">
                            
                            <div class="w-14 h-14 bg-gradient-to-br {{ $branchColor }} rounded-2xl flex items-center justify-center shadow-lg">
                                
                                <i data-lucide="sparkles" class="w-7 h-7 text-white"></i>
                            
                            </div>
                            
                            <div>
                                
                                <h2 class="text-4xl text-gray-900">Why Choose Us</h2>
                                
                                <p class="text-gray-600">Excellence in every aspect of care</p>
                            
                            </div>
                        
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            @foreach ($branch['highlights'] as $highlight)
                                
                                <div class="group flex items-start gap-4 p-6 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 rounded-2xl hover:shadow-lg transition-all border border-blue-100">
                                    
                                    <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md group-hover:scale-110 transition-transform">
                                        
                                        <i data-lucide="check-circle-2" class="w-6 h-6 text-white"></i>
                                    
                                    </div>
                                    
                                    <p class="text-gray-800 leading-relaxed">{{ $highlight }}</p>
                                
                                </div>
                            
                            @endforeach
                        
                        </div>
                    
                    </div>
                
                </section>

                
                <section class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16 brochure-section brochure-duo items-stretch">
                    
                    <div class="bg-white rounded-3xl p-10 shadow-2xl border border-gray-100 brochure-card h-full flex flex-col">
                        
                        <div class="flex items-center gap-4 mb-8">
                            
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                                
                                <i data-lucide="phone" class="w-7 h-7 text-white"></i>
                            
                            </div>
                            
                            <div>
                                
                                <h2 class="text-3xl text-gray-900">Get In Touch</h2>
                                
                                <p class="text-gray-600">We're here to help</p>
                            
                            </div>
                        
                        </div>

                        
                        <div class="space-y-6">
                            
                            <div class="group flex items-start gap-5 p-5 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl hover:shadow-md transition-all border border-blue-100">
                                
                                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md group-hover:scale-110 transition-transform">
                                    
                                    <i data-lucide="map-pin" class="w-7 h-7 text-white"></i>
                                
                                </div>
                                
                                <div class="flex-1">
                                    
                                    <p class="text-sm text-gray-500 mb-2">Address</p>
                                    
                                    <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="text-gray-900 text-lg hover:text-blue-600 transition-colors">
                                        
                                        {{ $contactAddress }}
                                    
                                    </a>
                                
                                </div>
                            
                            </div>

                            
                            <div class="group flex items-start gap-5 p-5 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl hover:shadow-md transition-all border border-green-100">
                                
                                <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md group-hover:scale-110 transition-transform">
                                    
                                    <i data-lucide="phone" class="w-7 h-7 text-white"></i>
                                
                                </div>
                                
                                <div class="flex-1">
                                    
                                    <p class="text-sm text-gray-500 mb-2">Phone Number</p>
                                    
                                    <a href="tel:{{ $contactPhone }}" class="text-gray-900 text-lg hover:text-green-600 transition-colors">
                                        
                                        {{ $contactPhone }}
                                    
                                    </a>
                                
                                </div>
                            
                            </div>

                            
                            <div class="group flex items-start gap-5 p-5 bg-gradient-to-br from-purple-50 to-fuchsia-50 rounded-2xl hover:shadow-md transition-all border border-purple-100">
                                
                                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-fuchsia-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md group-hover:scale-110 transition-transform">
                                    
                                    <i data-lucide="mail" class="w-7 h-7 text-white"></i>
                                
                                </div>
                                
                                <div class="flex-1">
                                    
                                    <p class="text-sm text-gray-500 mb-2">Email Address</p>
                                    
                                    <a href="mailto:{{ $contactEmail }}" class="text-gray-900 text-lg hover:text-purple-600 transition-colors">
                                        
                                        {{ $contactEmail }}
                                    
                                    </a>
                                
                                </div>
                            
                            </div>
                        
                        </div>
                    
                    </div>

                    
                    <div class="bg-white rounded-3xl p-10 shadow-2xl border border-gray-100 brochure-card h-full flex flex-col">
                        
                        <div class="flex items-center gap-4 mb-8">
                            
                            <div class="w-14 h-14 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center shadow-lg">
                                
                                <i data-lucide="clock" class="w-7 h-7 text-white"></i>
                            
                            </div>
                            
                            <div>
                                
                                <h2 class="text-3xl text-gray-900">Opening Hours</h2>
                                
                                <p class="text-gray-600">Visit us anytime</p>
                            
                            </div>
                        
                        </div>

                        
                        <div class="space-y-4">
                            
                            <div class="flex items-center justify-between p-5 bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl border border-gray-200">
                                
                                <div class="flex items-center gap-3">
                                    
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    
                                    <span class="text-gray-700">Saturday - Thursday</span>
                                
                                </div>
                                
                                <span class="text-gray-900">{{ $workingHoursDuration }}</span>
                            
                            </div>

                            
                            <div class="flex items-center justify-between p-5 bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl border border-gray-200">
                                
                                <div class="flex items-center gap-3">
                                    
                                    <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                    
                                    <span class="text-gray-700">Friday</span>
                                
                                </div>
                                
                                <span class="text-gray-900">Closed</span>
                            
                            </div>

                            
                            <div class="p-6 bg-gradient-to-br from-red-50 via-rose-50 to-pink-50 rounded-2xl border-2 border-red-300 shadow-lg">
                                
                                <div class="flex items-center gap-4">
                                    
                                    <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 animate-pulse">
                                        
                                        <i data-lucide="alert-circle" class="w-7 h-7 text-white"></i>
                                    
                                    </div>
                                    
                                    <div>
                                        
                                        <p class="text-xs text-red-700 mb-1">Urgent services</p>
                                        
                                        <p class="text-red-900">{{ $urgentHotline }}</p>
                                    
                                    </div>
                                
                                </div>
                            
                            </div>
                        
                        </div>
                    
                    </div>
                
                </section>

                
                <section class="mt-16 mb-16 brochure-section brochure-categories">
                    
                    <div class="bg-white rounded-3xl p-10 shadow-2xl border border-gray-100 brochure-card">
                        
                        <div class="flex items-center gap-4 mb-8">
                            
                            <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-lg">
                                
                                <i data-lucide="layers" class="w-7 h-7 text-white"></i>
                            
                            </div>
                            
                            <div>
                                
                                <h2 class="text-4xl text-gray-900">Product Categories</h2>
                                
                                <p class="text-gray-600">Electronics, phones, laptops, and more</p>
                            
                            </div>
                        
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            
                            @foreach ($productCategories as $category)
                                
                                <div class="flex items-start gap-4 p-5 bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl hover:shadow-md transition-all border border-gray-200">
                                    
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                                        
                                        <i data-lucide="check-circle-2" class="w-5 h-5 text-white"></i>
                                    
                                    </div>
                                    
                                    <span class="text-gray-800 leading-relaxed">{{ $category }}</span>
                                
                                </div>
                            
                            @endforeach
                        
                        </div>
                    
                    </div>
                
                </section>

            
            </div>

            
            <section class="mt-10 mb-16 brochure-section brochure-brands">
                
                <div class="bg-white rounded-3xl p-10 shadow-2xl border border-gray-100 brochure-card">
                    
                    <div class="flex items-center gap-4 mb-8">
                        
                        <div class="w-14 h-14 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-2xl flex items-center justify-center shadow-lg">
                            
                            <i data-lucide="badge-check" class="w-7 h-7 text-white"></i>
                        
                        </div>
                        
                        <div>
                            
                            <h2 class="text-4xl text-gray-900">Brands Available</h2>
                            
                            <p class="text-gray-600">Brands currently sold in this branch</p>
                        
                        </div>
                    
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        
                        @foreach ($brandNames as $brand)
                            
                            <div class="group flex items-center gap-4 p-6 bg-gradient-to-r from-yellow-50 via-amber-50 to-orange-50 rounded-2xl border-2 border-yellow-300 hover:shadow-lg transition-all hover:scale-105">
                                
                                <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                    
                                    <i data-lucide="shield" class="w-6 h-6 text-white"></i>
                                
                                </div>
                                
                                <span class="text-gray-900 text-lg">{{ $brand }}</span>
                            
                            </div>
                        
                        @endforeach
                    
                    </div>
                
                </div>
            
            </section>

            
            <section class="mt-12 mb-16 brochure-section brochure-specs">
                
                <div class="bg-gradient-to-br from-purple-50 via-fuchsia-50 to-pink-50 rounded-3xl p-10 shadow-2xl border border-purple-100 brochure-card brochure-card--soft">
                    
                    <div class="flex items-center gap-4 mb-8">
                        
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-fuchsia-600 rounded-2xl flex items-center justify-center shadow-lg">
                            
                            <i data-lucide="settings" class="w-7 h-7 text-white"></i>
                        
                        </div>
                        
                        <div>
                            
                            <h2 class="text-4xl text-gray-900">Product Specifications</h2>
                            
                            <p class="text-gray-600">Key specifications you can expect</p>
                        
                        </div>
                    
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        @foreach ($productSpecs as $spec)
                            
                            <div class="group bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all border-l-4 border-purple-500 hover:scale-105">
                                
                                <div class="flex items-start gap-4">
                                    
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-fuchsia-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                        
                                        <i data-lucide="sparkles" class="w-5 h-5 text-white"></i>
                                    
                                    </div>
                                    
                                    <p class="text-gray-800 text-lg leading-relaxed">{{ $spec }}</p>
                                
                                </div>
                            
                            </div>
                        
                        @endforeach
                    
                    </div>
                
                </div>
            
            </section>

            
            <div class="brochure-page-4">
                
                <section class="mb-16 brochure-section brochure-linked-products">
                    
                    <div class="bg-white rounded-3xl p-10 shadow-2xl border border-gray-100 brochure-card">
                        
                        <div class="flex items-center gap-4 mb-8">
                            
                            <div class="w-14 h-14 bg-gradient-to-br {{ $branchColor }} rounded-2xl flex items-center justify-center shadow-lg">
                                
                                <i data-lucide="package" class="w-7 h-7 text-white"></i>
                            
                            </div>
                            
                            <div>
                                
                                <h2 class="text-4xl text-gray-900">Linked Products</h2>
                                
                                <p class="text-gray-600">Products linked to this branch</p>
                            
                            </div>
                        
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 brochure-grid-2">
                            
                            @forelse ($linkedProducts as $productName)
                                
                                <div class="group flex items-center gap-4 p-5 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 rounded-2xl hover:shadow-xl transition-all border border-blue-100 hover:scale-105">
                                    
                                    <div class="w-12 h-12 bg-gradient-to-br {{ $branchColor }} rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                        
                                        <i data-lucide="package" class="w-6 h-6 text-white"></i>
                                    
                                    </div>
                                    
                                    <span class="text-gray-800">{{ $productName }}</span>
                                
                                </div>
                            
                            @empty
                                
                                <div class="group flex items-center gap-4 p-5 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 rounded-2xl border border-blue-100">
                                    
                                    <div class="w-12 h-12 bg-gradient-to-br {{ $branchColor }} rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                        
                                        <i data-lucide="package" class="w-6 h-6 text-white"></i>
                                    
                                    </div>
                                    
                                    <span class="text-gray-800">No linked products yet</span>
                                
                                </div>
                            
                            @endforelse
                        
                        </div>
                    
                    </div>
                
                </section>

                
                <section class="brochure-export-footer">
                    
                    <div class="brochure-export-footer-card shadow-2xl">
                        
                        <div class="flex flex-col items-center text-center gap-4">
                            
                            <div class="w-14 h-14 bg-gradient-to-br {{ $branchColor }} rounded-2xl flex items-center justify-center shadow-lg">
                                
                                <i data-lucide="building-2" class="w-7 h-7 text-white"></i>
                            
                            </div>
                            
                            <div>
                                
                                <h3 class="text-3xl text-white">{{ $branch['name'] }}</h3>
                                
                                <p class="text-base">{{ $branch['city'] }}, Syria</p>
                            
                            </div>
                        
                        </div>

                        
                        <div class="mt-6 flex items-center justify-center gap-3">
                            
                            <i data-lucide="map-pin" class="w-5 h-5 text-slate-300"></i>
                            
                            <p class="text-base">{{ $contactAddress }}</p>
                        
                        </div>

                        
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-8 text-sm">
                            
                            <a href="tel:{{ $contactPhone }}" class="flex items-center gap-2">
                                
                                <i data-lucide="phone" class="w-4 h-4"></i>
                                
                                <span>{{ $contactPhone }}</span>
                            
                            </a>
                            
                            <span class="text-slate-500">&bull;</span>
                            
                            <a href="mailto:{{ $contactEmail }}" class="flex items-center gap-2">
                                
                                <i data-lucide="mail" class="w-4 h-4"></i>
                                
                                <span>{{ $contactEmail }}</span>
                            
                            </a>
                        
                        </div>
                    
                    </div>
                
                </section>
            
            </div>

        
        </div>
        
        </div>
        
        <style>
            
            .brochure-hidden {
                
                display: none !important;
            
            }
            
            .brochure-export .brochure-section {
                
                margin-bottom: 2.25rem;
            
            }
            
            .brochure-export .brochure-content {
                
                margin-top: 2rem;
            
            }
            
            .brochure-export .brochure-card {
                
                box-shadow: none;
            
            }
            
            .brochure-export .brochure-brands,
            
            .brochure-export .brochure-linked-products,
            
            .brochure-export .brochure-specs,
            
            .brochure-export .brochure-categories {
                
                break-inside: avoid;
                
                page-break-inside: avoid;
            
            }
            
            .brochure-export .brochure-brands .brochure-card {
                
                padding: 1.25rem;
            
            }
            
            .brochure-export .brochure-brands .mb-8 {
                
                margin-bottom: 0.75rem;
            
            }
            
            .brochure-export .brochure-brands .grid {
                
                grid-template-columns: repeat(3, minmax(0, 1fr));
                
                gap: 0.5rem;
            
            }
            
            .brochure-export .brochure-brands .p-6 {
                
                padding: 0.6rem !important;
            
            }
            
            .brochure-export .brochure-brands .w-12.h-12 {
                
                width: 2.25rem !important;
                
                height: 2.25rem !important;
            
            }
            
            .brochure-export .brochure-brands .text-lg {
                
                font-size: 0.9rem !important;
            
            }
            
            .brochure-export .brochure-categories h2,
            
            .brochure-export .brochure-specs h2 {
                
                font-size: 1.5rem !important;
            
            }
            
            .brochure-export .brochure-categories p,
            
            .brochure-export .brochure-specs p {
                
                font-size: 0.9rem !important;
            
            }
            
            .brochure-export-tight .brochure-categories .brochure-card,
            
            .brochure-export-tight .brochure-specs .brochure-card {
                
                padding: 1rem !important;
            
            }
            
            .brochure-export-tight .brochure-categories .grid,
            
            .brochure-export-tight .brochure-specs .grid {
                
                gap: 0.4rem !important;
            
            }
            
            .brochure-export-tight .brochure-categories .p-5,
            
            .brochure-export-tight .brochure-specs .p-8 {
                
                padding: 0.6rem !important;
            
            }
            
            .brochure-export-tight .brochure-categories h2,
            
            .brochure-export-tight .brochure-specs h2 {
                
                font-size: 1.35rem !important;
            
            }
            
            .brochure-export-tight .brochure-categories p,
            
            .brochure-export-tight .brochure-specs p {
                
                font-size: 0.85rem !important;
            
            }
            
            .brochure-export-tight-2 .brochure-linked-products .brochure-card,
            
            .brochure-export-tight-2 .brochure-categories .brochure-card,
            
            .brochure-export-tight-2 .brochure-specs .brochure-card {
                
                padding: 0.85rem !important;
            
            }
            
            .brochure-export-tight-2 .brochure-linked-products .grid,
            
            .brochure-export-tight-2 .brochure-categories .grid,
            
            .brochure-export-tight-2 .brochure-specs .grid {
                
                gap: 0.35rem !important;
            
            }
            
            .brochure-export-tight-2 .brochure-linked-products .p-5,
            
            .brochure-export-tight-2 .brochure-categories .p-5,
            
            .brochure-export-tight-2 .brochure-specs .p-8 {
                
                padding: 0.5rem !important;
            
            }
            
            .brochure-export-tight-2 .brochure-categories h2,
            
            .brochure-export-tight-2 .brochure-specs h2 {
                
                font-size: 1.2rem !important;
            
            }
            
            .brochure-export-tight-2 .brochure-categories p,
            
            .brochure-export-tight-2 .brochure-specs p {
                
                font-size: 0.8rem !important;
            
            }
            
            .brochure-export-tight-2 .brochure-categories .grid {
                
                grid-template-columns: repeat(3, minmax(0, 1fr));
            
            }
            
            .brochure-export-tight-3 .brochure-content {
                
                padding-top: 0.4rem !important;
                
                padding-bottom: 0.4rem !important;
            
            }
            
            .brochure-export-tight-3 .brochure-section {
                
                margin-bottom: 0.5rem !important;
            
            }
            
            .brochure-export-tight-3 .brochure-card {
                
                padding: 1rem !important;
            
            }
            
            .brochure-export-tight-3 .brochure-section .text-4xl,
            
            .brochure-export-tight-3 .brochure-section .text-3xl {
                
                font-size: 1.15rem !important;
            
            }
            
            .brochure-export-tight-3 .brochure-section .text-2xl {
                
                font-size: 1rem !important;
            
            }
            
            .brochure-export-tight-3 .brochure-section .text-lg,
            
            .brochure-export-tight-3 .brochure-section .text-base {
                
                font-size: 0.82rem !important;
            
            }
            
            .brochure-export-tight-3 .brochure-linked-products .grid,
            
            .brochure-export-tight-3 .brochure-brands .grid,
            
            .brochure-export-tight-3 .brochure-categories .grid,
            
            .brochure-export-tight-3 .brochure-specs .grid {
                
                gap: 0.5rem !important;
            
            }
            
            .brochure-export-tight-3 .brochure-linked-products .grid,
            
            .brochure-export-tight-3 .brochure-brands .grid {
                
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            
            }
            
            .brochure-export-tight-3 .brochure-categories .grid {
                
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            
            }
            
            .brochure-export-tight-3 .brochure-linked-products .p-6,
            
            .brochure-export-tight-3 .brochure-brands .p-6,
            
            .brochure-export-tight-3 .brochure-categories .p-5,
            
            .brochure-export-tight-3 .brochure-specs .p-8 {
                
                padding: 0.5rem !important;
            
            }
            
            .brochure-export-tight-3 .brochure-categories h2,
            
            .brochure-export-tight-3 .brochure-specs h2 {
                
                font-size: 1.05rem !important;
            
            }
            
            .brochure-export-tight-3 .brochure-categories p,
            
            .brochure-export-tight-3 .brochure-specs p {
                
                font-size: 0.75rem !important;
            
            }
            
            .brochure-export-footer {
                
                margin-top: 1rem;
            
            }
            
            .brochure-export-footer .brochure-export-footer-card {
                
                background: #0f172a;
                
                color: #ffffff;
                
                border-radius: 22px;
                
                padding: 1.6rem 1.8rem;
            
            }
            
            .brochure-export-footer .brochure-export-footer-card p {
                
                color: #cbd5f5;
            
            }
            
            .brochure-export-footer .brochure-export-footer-card a {
                
                color: #e2e8f0;
            
            }
            
            
            
            .brochure-export .brochure-brands .brochure-card,
            
            .brochure-export .brochure-linked-products .brochure-card,
            
            .brochure-export .brochure-specs .brochure-card,
            
            .brochure-export .brochure-categories .brochure-card {
                
                padding: 1.5rem;
            
            }
            
            .brochure-export .brochure-brands .grid,
            
            .brochure-export .brochure-linked-products .grid,
            
            .brochure-export .brochure-specs .grid,
            
            .brochure-export .brochure-categories .grid {
                
                gap: 0.65rem;
            
            }
            
            .brochure-export .brochure-brands .p-6,
            
            .brochure-export .brochure-linked-products .p-6,
            
            .brochure-export .brochure-specs .p-8,
            
            .brochure-export .brochure-categories .p-5 {
                
                padding: 0.85rem !important;
            
            }
        
        @media print {
            
            html {
                
                font-size: 14px;
            
            }
            
            html,
            
            body {
                
                width: 210mm;
                
                height: 297mm;
                
                margin: 0;
                
                padding: 0;
            
            }
            
            body {
                
                print-color-adjust: exact;
                
                -webkit-print-color-adjust: exact;
                
                background: #f8fafc !important;
                
                margin: 0 !important;
            
            }
            
            .brochure-page {
                
                margin: 0 !important;
            
            }
            
            .print\:hidden {
                
                display: none !important;
            
            }
            
            .print\:break-before-page {
                
                page-break-before: always;
            
            }
            
            .brochure-shell {
                
                background: #f8fafc !important;
            
            }
            
            .min-h-screen {
                
                min-height: auto !important;
            
            }
            
            .max-w-7xl {
                
                max-width: 100% !important;
            
            }
            
            .brochure-hero {
                
                padding-top: 1.2cm !important;
                
                padding-bottom: 1.2cm !important;
                
                break-inside: avoid;
                
                page-break-inside: avoid;
                
                height: 297mm !important;
                
                min-height: 297mm !important;
                
                width: 210mm !important;
                
                min-width: 210mm !important;
                
                margin: 0 !important;
                
                box-sizing: border-box;
                
                background: inherit !important;
            
            }
                
                .brochure-hero .absolute.inset-0 {
                    
                    top: 0;
                    
                    right: 0;
                    
                    bottom: 0;
                    
                    left: 0;
                
                }
                
                .brochure-hero .max-w-7xl {
                    
                    max-width: 100% !important;
                
                }
                
                .brochure-hero .px-4,
                
                .brochure-hero .sm\:px-6,
                
                .brochure-hero .lg\:px-8 {
                    
                    padding-left: 12mm !important;
                    
                    padding-right: 12mm !important;
                
                }
                
                .brochure-hero .py-20,
                
                .brochure-hero .md\:py-32 {
                    
                    padding-top: 0 !important;
                    
                    padding-bottom: 0 !important;
                
                }
                
                .brochure-hero .max-w-4xl {
                    
                    min-height: 297mm !important;
                    
                    display: flex !important;
                    
                    flex-direction: column !important;
                    
                    justify-content: center !important;
                
                }
                
                .brochure-hero .text-6xl,
                
                .brochure-hero .text-7xl,
                
                .brochure-hero .text-8xl {
                    
                    font-size: 2.4rem !important;
                    
                    line-height: 1.1 !important;
                
                }
                
                .brochure-hero .text-3xl,
                
                .brochure-hero .text-4xl {
                    
                    font-size: 1.6rem !important;
                    
                    line-height: 1.2 !important;
                
                }
                
                .brochure-hero .text-xl {
                    
                    font-size: 1rem !important;
                
                }
                
                .brochure-hero-badges {
                    
                    display: flex !important;
                    
                    flex-wrap: wrap !important;
                    
                    gap: 0.4rem !important;
                    
                    margin-bottom: 0.7cm !important;
                
                }
                
                .brochure-hero-badges > div {
                    
                    background: rgba(255, 255, 255, 0.18) !important;
                    
                    border-color: rgba(255, 255, 255, 0.35) !important;
                
                }
                
                .brochure-hero-stats {
                    
                    display: grid !important;
                    
                    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                    
                    gap: 0.45rem !important;
                    
                    margin-bottom: 0.9cm !important;
                
                }
                
                .brochure-hero-stats > div {
                    
                    background: rgba(255, 255, 255, 0.22) !important;
                    
                    border-color: rgba(255, 255, 255, 0.3) !important;
                    
                    break-inside: avoid;
                    
                    page-break-inside: avoid;
                
                }
                
                .brochure-hero-stats i,
                
                .brochure-hero-stats p {
                    
                    color: #fff !important;
                
                }
                
                .brochure-hero-urgent {
                    
                    margin-top: 0.2cm !important;
                
                }
                
                .brochure-content {
                    
                    margin-top: 0.6cm !important;
                    
                    padding-bottom: 0 !important;
                    
                    padding-left: 12mm !important;
                    
                    padding-right: 12mm !important;
                    
                    padding-top: 6mm !important;
                
                }
                
                .brochure-card {
                    
                    box-shadow: none !important;
                    
                    break-inside: avoid;
                    
                    page-break-inside: avoid;
                    
                    border-color: #e5e7eb !important;
                    
                    box-decoration-break: clone;
                
                }
                
                .brochure-section {
                    
                    break-inside: avoid;
                    
                    page-break-inside: avoid;
                    
                    margin-bottom: 0.6cm !important;
                
                }
                
                .brochure-why {
                    
                    margin-bottom: 0.35cm !important;
                
                }
                
                .brochure-page-2 {
                    
                    page-break-after: always;
                    
                    break-inside: avoid;
                
                }
                
                .brochure-page-2 .brochure-section + .brochure-section {
                    
                    page-break-before: avoid !important;
                    
                    break-before: avoid-page !important;
                
                }
                
                .brochure-page-2 .brochure-section {
                    
                    margin-bottom: 0.8cm !important;
                
                }
                
                .brochure-page-2 .mb-8 {
                    
                    margin-bottom: 0.8rem !important;
                
                }
                
                .brochure-page-2 .space-y-6 > :not([hidden]) ~ :not([hidden]) {
                    
                    margin-top: 0.8rem !important;
                
                }
                
                .brochure-page-2 .space-y-4 > :not([hidden]) ~ :not([hidden]) {
                    
                    margin-top: 0.7rem !important;
                
                }
                
                .brochure-page-2 .brochure-section .grid {
                    
                    gap: 0.75rem !important;
                
                }
                
                .brochure-linked-products {
                    
                    page-break-before: avoid;
                    
                    page-break-after: avoid;
                
                }
                
                .brochure-brands {
                    
                    page-break-before: avoid;
                
                }
                
                .brochure-brands,
                
                .brochure-specs {
                    
                    margin-bottom: 0.3cm !important;
                    
                    break-inside: avoid;
                    
                    page-break-inside: avoid;
                
                }
                
                .brochure-categories {
                    
                    margin-top: 0.75cm !important;
                
                }
                
                .brochure-brands {
                    
                    margin-top: 0.45cm !important;
                    
                    margin-bottom: 0.7cm !important;
                
                }
                
                .brochure-specs {
                    
                    margin-top: 0.55cm !important;
                
                }
                
                .brochure-brands .brochure-card,
                
                .brochure-specs .brochure-card {
                    
                    padding: 0.8rem !important;
                
                }
                
                .brochure-brands .grid {
                    
                    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                    
                    gap: 0.35rem !important;
                
                }
                
                .brochure-specs .grid {
                    
                    gap: 0.4rem !important;
                
                }
                
                .brochure-brands .p-6,
                
                .brochure-specs .p-8 {
                    
                    padding: 0.55rem !important;
                
                }
                
                .brochure-brands h2,
                
                .brochure-specs h2 {
                    
                    font-size: 1.2rem !important;
                
                }
                
                .brochure-brands p,
                
                .brochure-specs p {
                    
                    font-size: 0.8rem !important;
                
                }
                
                .brochure-specs .text-lg {
                    
                    font-size: 0.85rem !important;
                
                }
                
                .brochure-page-4 {
                    
                    page-break-before: always;
                    
                    break-before: page;
                    
                    min-height: 297mm;
                    
                    height: 297mm;
                    
                    display: flex;
                    
                    flex-direction: column;
                    
                    padding-bottom: 0.6cm !important;
                
                }
                
                .brochure-page-4 .brochure-export-footer {
                    
                    margin-top: auto !important;
                    
                    margin-bottom: 0.35cm !important;
                
                }
                
                .brochure-page-4 .brochure-specs .brochure-card {
                    
                    padding: 0.7rem !important;
                
                }
                
                .brochure-page-4 .brochure-specs .grid {
                    
                    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                    
                    gap: 0.35rem !important;
                
                }
                
                .brochure-page-4 .brochure-specs .p-8 {
                    
                    padding: 0.5rem !important;
                
                }
                
                .brochure-page-4 .brochure-specs .text-lg {
                    
                    font-size: 0.78rem !important;
                
                }
                
                .brochure-page-4 .brochure-linked-products {
                    
                    margin-top: 0.55cm !important;
                    
                    margin-bottom: 0.35cm !important;
                
                }
                
                .brochure-page-4 .brochure-linked-products .brochure-card {
                    
                    padding: 0.6rem !important;
                
                }
                
                .brochure-page-4 .brochure-linked-products .grid {
                    
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                    
                    gap: 0.45rem !important;
                
                }
                
                .brochure-page-4 .brochure-linked-products .p-5 {
                    
                    padding: 0.7rem !important;
                
                }
                
                .brochure-page-4 .brochure-linked-products .text-lg,
                
                .brochure-page-4 .brochure-linked-products span {
                    
                    font-size: 0.9rem !important;
                    
                    line-height: 1.2 !important;
                
                }
                
                .brochure-page-4 .brochure-linked-products .w-12.h-12 {
                    
                    width: 2.25rem !important;
                    
                    height: 2.25rem !important;
                
                }
                
                .brochure-page-4 .brochure-export-footer .brochure-export-footer-card {
                    
                    padding: 0.85rem 1rem;
                    
                    border-radius: 14px;
                
                }
                
                .brochure-page-4 .brochure-export-footer h3 {
                    
                    font-size: 1.05rem;
                
                }
                
                .brochure-export-footer {
                    
                    break-inside: avoid;
                    
                    page-break-inside: avoid;
                
                }
                
                .brochure-export-footer .brochure-export-footer-card {
                    
                    padding: 1.1rem 1.4rem;
                    
                    border-radius: 18px;
                
                }
                
                .brochure-export-footer h3 {
                    
                    font-size: 1.35rem;
                
                }
                
                .brochure-export-footer p,
                
                .brochure-export-footer a {
                    
                    font-size: 0.82rem;
                
                }
                
                
                .brochure-section > .grid,
                
                .brochure-section .grid {
                    
                    break-inside: avoid;
                    
                    page-break-inside: avoid;
                    
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                
                }
                
                .brochure-grid-2 {
                    
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                
                }
                
                .brochure-section .grid > * {
                    
                    break-inside: avoid;
                    
                    page-break-inside: avoid;
                
                }
                
                .brochure-card .grid > * {
                    
                    break-inside: avoid;
                    
                    page-break-inside: avoid;
                
                }
                
                .brochure-section .p-10 {
                    
                    padding: 0.95rem !important;
                
                }
                
                .brochure-section .p-8 {
                    
                    padding: 0.8rem !important;
                
                }
                
                .brochure-section .p-6 {
                    
                    padding: 0.65rem !important;
                
                }
                
                .brochure-section .p-5 {
                    
                    padding: 0.6rem !important;
                
                }
                
                .brochure-section .p-4 {
                    
                    padding: 0.55rem !important;
                
                }
                
                .brochure-section .text-4xl,
                
                .brochure-section .text-3xl {
                    
                    font-size: 1.3rem !important;
                    
                    line-height: 1.2 !important;
                
                }
                
                .brochure-section .text-2xl {
                    
                    font-size: 1.1rem !important;
                
                }
                
                .brochure-section .text-xl {
                    
                    font-size: 1rem !important;
                
                }
                
                .brochure-section .text-lg {
                    
                    font-size: 0.9rem !important;
                
                }
                
                .brochure-section .text-base {
                    
                    font-size: 0.85rem !important;
                
                }
                
                .brochure-section .gap-8 {
                    
                    gap: 0.6rem !important;
                
                }
                
                .brochure-section .gap-6 {
                    
                    gap: 0.5rem !important;
                
                }
                
                .brochure-section .gap-5 {
                    
                    gap: 0.45rem !important;
                
                }
                
                .brochure-section .gap-4 {
                    
                    gap: 0.4rem !important;
                
                }
                
                .brochure-duo {
                    
                    display: grid !important;
                    
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                    
                    gap: 0.6cm !important;
                
                }
                
                .brochure-duo > .brochure-card {
                    
                    break-inside: avoid;
                    
                    page-break-inside: avoid;
                
                }
                
                .brochure-card--soft {
                    
                    background: #fdf4ff !important;
                
                }
                
                .shadow-2xl,
                
                .shadow-lg,
                
                .shadow-md {
                    
                    box-shadow: none !important;
                
                }
            
            @page {
                
                margin: 0;
                
                size: A4;
            
            }
        
        }
        
        </style>
    
    </div>

@endif

</div>

@endsection

@push('scripts')

<script src="https://unpkg.com/lucide@latest"></script>

<script>
    
    if (window.lucide) {
        
        window.lucide.createIcons();
    
    }

    
    (function () {
        
        const downloadLink = document.querySelector('[data-brochure-download]');
        
        if (!downloadLink) return;

        
        downloadLink.addEventListener('click', function (event) {
            
            event.preventDefault();
            
            window.print();
        
        });
    
    })();

</script>

@endpush
