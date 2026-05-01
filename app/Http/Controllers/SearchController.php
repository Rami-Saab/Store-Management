<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Controllers;

/**
 * Controller: بحث عام داخل النظام (Smart Search)
 *
 * هذه الصفحة توفر مربع بحث عام (Global Search) للبحث عن:
 * - الأفرع (Stores/Branches)
 * - المستخدمين/الموظفين (Users/Employees/Managers)
 * - المنتجات (Products) و SKU
 * - المحافظات (Provinces)
 *
 * آلية العمل:
 * - يدعم Prefix مثل: store: / employee: / product: لتحديد نوع البحث بسرعة.
 * - يحاول "تخمين" نية المستخدم عبر نظام نقاط (scoring) ثم يعرض الأقسام بترتيب منطقي.
 * - لا يغيّر البيانات، فقط قراءة وعرض نتائج مصغرة لكل قسم (limit 6).
 */

// الاستيرادات اللازمة للنماذج وطلب HTTP وواجهة العرض.
use App\Models\Product;
use App\Models\Province;
use App\Models\Store;
use App\Models\User;
use App\Services\Access\StoreScopeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        if (! $currentUser || ! $currentUser->hasPermission('search_store')) {
            abort(403, 'You do not have permission to search branches.');
        }
        $scopeService = app(StoreScopeService::class);
        $departmentId = $currentUser ? $scopeService->resolveDepartmentId($currentUser) : null;
        $isStoreScoped = $currentUser && ($currentUser->hasRole('store_manager') || $currentUser->hasRole('store_employee'));
        $storeIds = [];
        if ($isStoreScoped) {
            $storeIds = $scopeService
                ->allowedStores($currentUser)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }
        // قراءة نص البحث من شريط البحث العلوي (q).
        $query = trim((string) $request->input('q', ''));
        // term هو الجزء الأساسي الذي سنبحث عنه (قد يتغير بعد إزالة prefix).
        $term = $query;
        // prefix يساعد على تحديد نوع البحث (مثل: store: ، product: ...).
        $prefix = null;

        // دعم صيغة: "type: كلمة" لتوجيه البحث لقسم معين.
        if (preg_match('/^(branch|store|employee|manager|staff|user|product|sku|province)\s*:\s*(.+)$/i', $query, $matches)) {
            $prefix = strtolower($matches[1]);
            $term = trim((string) $matches[2]);
        }

        // نسخة موحدة للحروف الصغيرة لتحسين matching مع كلمات مثل employee/manager...
        $normalized = mb_strtolower($term);
        // استخراج الأرقام فقط (مفيد للبحث عن الهاتف/الأكواد الرقمية).
        $digitTerm = preg_replace('/\D+/', '', (string) $term);

        // نظام نقاط بسيط لتخمين "نية" المستخدم (أي قسم هو الأقرب).
        $scores = [
            'stores' => 0,
            'users' => 0,
            'products' => 0,
            'provinces' => 0,
        ];

        // تحويل prefix إلى المفتاح الداخلي للقسم.
        $prefixMap = [
            'branch' => 'stores',
            'store' => 'stores',
            'employee' => 'users',
            'manager' => 'users',
            'staff' => 'users',
            'user' => 'users',
            'product' => 'products',
            'sku' => 'products',
            'province' => 'provinces',
        ];

        // إذا استخدم المستخدم prefix صريح نرفع نقاط القسم المقصود ليظهر أولاً.
        if ($prefix && isset($prefixMap[$prefix])) {
            $scores[$prefixMap[$prefix]] += 5;
        }

        // قواعد تخمين إضافية تعتمد على شكل النص المدخل.
        if ($normalized !== '') {
            // كود فرع مثل DAM-001 يعطي نقاط لقسم الأفرع.
            if (preg_match('/^[a-z]{2,4}-?\d{2,4}$/i', $term)) {
                $scores['stores'] += 4;
            }
            // كلمات مفتاحية تشير للأفرع.
            if (str_contains($normalized, 'branch') || str_contains($normalized, 'store')) {
                $scores['stores'] += 2;
            }
            // كلمات مفتاحية تشير للمحافظات.
            if (str_contains($normalized, 'province')) {
                $scores['provinces'] += 2;
            }
            // كلمات مفتاحية تشير للمنتجات/SKU.
            if (str_contains($normalized, 'product') || str_contains($normalized, 'sku')) {
                $scores['products'] += 2;
            }
            // كلمات مفتاحية تشير للمستخدمين.
            if (str_contains($normalized, 'employee') || str_contains($normalized, 'manager') || str_contains($normalized, 'staff') || str_contains($normalized, 'user')) {
                $scores['users'] += 2;
            }
            // وجود @ عادة يشير إلى بريد إلكتروني -> مستخدمين.
            if (str_contains($term, '@')) {
                $scores['users'] += 4;
            }
            // أرقام طويلة قد تشير إلى هاتف -> مستخدمين.
            if ($digitTerm !== '' && strlen($digitTerm) >= 6) {
                $scores['users'] += 2;
            }
        }

        // تحديد القسم الأعلى نقاطاً ليظهر كبداية "intent".
        $intentKey = collect($scores)->sortDesc()->keys()->first();
        // تسمية intent لعرضها في UI (مثل: Employees / Products ...).
        $intentLabel = $scores[$intentKey] > 0 ? match ($intentKey) {
            'stores' => 'Branches',
            'users' => 'Employees',
            'products' => 'Products',
            'provinces' => 'Provinces',
            default => 'All results',
        } : 'All results';

        // إزالة الكلمات المفتاحية العامة من المصطلح حتى لا تفسد نتائج البحث الفعلية.
        $keywordless = trim((string) preg_replace('/\\b(branch|store|employee|manager|staff|user|product|sku|province)\\b/i', '', $term));
        // إن تبقى مصطلح بعد الإزالة نستخدمه، وإلا نستخدم term الأصلي.
        $genericTerm = $keywordless !== '' ? $keywordless : $term;

        // تلميح لدور المستخدم: إذا كتب manager/employee نقيّد نتائج المستخدمين إلى هذا الدور.
        $userRoleHint = null;
        if (str_contains($normalized, 'manager')) {
            $userRoleHint = ['store_manager'];
        } elseif (str_contains($normalized, 'employee') || str_contains($normalized, 'staff')) {
            $userRoleHint = ['store_employee'];
        }

        // Collections لنتائج كل قسم (تبقى فارغة إن لم يوجد بحث).
        $stores = collect();
        $users = collect();
        $products = collect();
        $provinces = collect();

        // نجري الاستعلامات فقط إذا كان هناك term فعلي أو prefix (حتى لا نعرض كل البيانات عند فتح الصفحة).
        if ($term !== '' || $prefix) {
            // البحث عن الأفرع: نبحث في الاسم/الكود/المدينة/العنوان/المحافظة.
            $stores = Store::query()
                ->select(['id', 'name', 'branch_code', 'province_id', 'address', 'city', 'status'])
                ->with(['province:id,name,code'])
                ->when($genericTerm !== '', function ($builder) use ($genericTerm) {
                    $like = '%'.$genericTerm.'%';
                    $builder->where(function ($query) use ($like) {
                        $query->where('name', 'like', $like)
                            ->orWhere('branch_code', 'like', $like)
                            ->orWhere('city', 'like', $like)
                            ->orWhere('address', 'like', $like)
                            ->orWhereHas('province', function ($provinceQuery) use ($like) {
                                $provinceQuery->where('name', 'like', $like)
                                    ->orWhere('code', 'like', $like);
                            });
                    });
                })
                ->when($isStoreScoped, function ($builder) use ($storeIds) {
                    $builder->whereIn('id', $storeIds !== [] ? $storeIds : [0]);
                })
                ->when($currentUser && ! $currentUser->hasRole('admin') && ! $isStoreScoped, function ($builder) use ($departmentId) {
                    if ($departmentId) {
                        $builder->where('department_id', $departmentId);
                    } else {
                        $builder->whereRaw('1 = 0');
                    }
                })
                ->orderBy('name')
                ->limit(6)
                ->get();

            // البحث عن المستخدمين: name/email/phone مع إمكانية تقييد الدور (manager/employee).
            $users = User::query()
                ->select(['id', 'name', 'email', 'phone', 'role', 'status'])
                ->with(['stores:id,branch_code,name'])
                ->when($userRoleHint, function ($builder) use ($userRoleHint) {
                    return is_array($userRoleHint)
                        ? $builder->whereIn('role', $userRoleHint)
                        : $builder->where('role', $userRoleHint);
                })
                ->when($isStoreScoped, function ($builder) use ($storeIds) {
                    $storeIds = $storeIds !== [] ? $storeIds : [0];
                    $employeeIds = DB::table('store_user')
                        ->whereIn('store_id', $storeIds)
                        ->pluck('user_id')
                        ->all();
                    $managerIds = Store::query()
                        ->whereIn('id', $storeIds)
                        ->pluck('manager_id')
                        ->filter()
                        ->all();
                    $ids = array_values(array_unique(array_filter(array_merge($employeeIds, $managerIds))));
                    $builder->whereIn('id', $ids !== [] ? $ids : [0]);
                })
                ->when($currentUser && ! $currentUser->hasRole('admin') && ! $isStoreScoped, function ($builder) use ($departmentId) {
                    if ($departmentId) {
                        $builder->where('department_id', $departmentId);
                    } else {
                        $builder->whereRaw('1 = 0');
                    }
                })
                ->when($keywordless !== '', function ($builder) use ($keywordless, $digitTerm) {
                    $like = '%'.$keywordless.'%';
                    $builder->where(function ($query) use ($like, $digitTerm) {
                        $query->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                        if ($digitTerm !== '') {
                            $query->orWhere('phone', 'like', '%'.$digitTerm.'%');
                        }
                    });
                })
                ->when($keywordless === '' && $digitTerm !== '', function ($builder) use ($digitTerm) {
                    $builder->where('phone', 'like', '%'.$digitTerm.'%');
                })
                ->orderBy('name')
                ->limit(6)
                ->get();

            // البحث عن المنتجات: name أو sku.
            $products = Product::query()
                ->select(['id', 'name', 'sku', 'status', 'price'])
                ->when($genericTerm !== '', function ($builder) use ($genericTerm) {
                    $search = $genericTerm;
                    $like = '%'.$search.'%';
                    $builder->where(function ($query) use ($like) {
                        $query->where('name', 'like', $like)
                            ->orWhere('sku', 'like', $like);
                    });
                })
                ->orderBy('name')
                ->limit(6)
                ->get();

            // البحث عن المحافظات: name أو code.
            $provinces = Province::query()
                ->select(['id', 'name', 'code'])
                ->when($genericTerm !== '', function ($builder) use ($genericTerm) {
                    $like = '%'.$genericTerm.'%';
                    $builder->where('name', 'like', $like)
                        ->orWhere('code', 'like', $like);
                })
                ->orderBy('name')
                ->limit(6)
                ->get();
        }

        // تجميع النتائج في أقسام مع ترتيب مبني على intent score (الأقرب أولاً).
        $sections = collect([
            [
                'key' => 'stores',
                'title' => 'Branches',
                'count' => $stores->count(),
                'items' => $stores,
                'order' => 100 - ($scores['stores'] ?? 0),
            ],
            [
                'key' => 'users',
                'title' => 'Employees',
                'count' => $users->count(),
                'items' => $users,
                'order' => 100 - ($scores['users'] ?? 0),
            ],
            [
                'key' => 'products',
                'title' => 'Products',
                'count' => $products->count(),
                'items' => $products,
                'order' => 100 - ($scores['products'] ?? 0),
            ],
            [
                'key' => 'provinces',
                'title' => 'Provinces',
                'count' => $provinces->count(),
                'items' => $provinces,
                'order' => 100 - ($scores['provinces'] ?? 0),
            ],
        ])->sortBy('order')->values();

        // عرض صفحة النتائج وتمرير البيانات الأساسية.
        return view('search.index', [
            'query' => $query,
            'term' => $term,
            'intentLabel' => $intentLabel,
            'sections' => $sections,
        ]);
    }
}