<?php // Name : Rodain Gouzlan Id:

namespace App\Services\Store;

/**
 * Service: بحث الأفرع + تجهيز صفوف العرض (Grid Rows)
 *
 * الهدف:
 * - إرجاع قائمة الأفرع كما تظهر في صفحة grid (stores.index) مع عدّادات (عدد الموظفين/المنتجات) واسم المدير.
 *
 * أسلوب التنفيذ:
 * - في MySQL يتم تفضيل استخدام Stored Procedure (sp_search_store_branches) لتحقيق متطلبات المشروع الأكاديمية
 *   ولتحسين الأداء عند البحث.
 * - يوجد مسار بديل باستخدام Eloquent في حال لم يتوفر MySQL/Stored Procedure (مثلاً أثناء الاختبارات).
 * - تم استخدام Cache قصيرة (30 ثانية) لتسريع التصفح وتقليل الضغط على قاعدة البيانات عند تغيير الفلاتر بسرعة.
 */

use App\Models\Store;
use App\Support\UserContact;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StoreSearchService
{
    // ترتيب أولوية لبعض أكواد الفروع لإظهارها أولاً في النتائج.
    private const PRIORITY_BRANCHES = [
        'DAM-001' => 0,
        'ALP-003' => 1,
    ];

    // تنفيذ البحث وإرجاع صفوف جاهزة للعرض في Grid View.
    public function searchStoreRows(array $filters): Collection
    {
        // إصدار البحث (Cache busting): يتغير عند أي تعديل على بيانات الأفرع.
        $version = (int) Cache::get('store_search_version', 1);
        // فلتر الهاتف نطبعّه إلى أرقام فقط لأن المستخدم قد يكتب (مسافات/رموز/كود دولة...).
        $phoneFilter = $this->normalizePhone((string) ($filters['phone'] ?? ''));
        // مفتاح الكاش يعتمد على جميع الفلاتر + نوع قاعدة البيانات (driver) حتى لا تختلط النتائج بين بيئات مختلفة.
        $cacheKey = 'store_search_v2:'.$version.':'.md5(json_encode([
            'driver' => DB::getDriverName(),
            'name' => (string) ($filters['name'] ?? ''),
            'status' => (string) ($filters['status'] ?? ''),
            'province_id' => (string) ($filters['province_id'] ?? ''),
            'phone' => (string) ($phoneFilter ?? ''),
        ]));

        // كاش 30 ثانية: يعطي إحساس "سرعة" عند تغيير الفلاتر بالواجهة دون التأثير على دقة البيانات.
        return Cache::remember($cacheKey, now()->addSeconds(30), function () use ($filters): Collection {
            // استخراج فلاتر البحث وتحويلها للقيم المتوقعة من SQL/Stored Procedure.
            $statusFilter = (string) ($filters['status'] ?? '');
            $phoneFilter = $this->normalizePhone((string) ($filters['phone'] ?? ''));
            $phoneFilter = $phoneFilter !== '' ? $phoneFilter : null;

            // في حال كانت قاعدة البيانات MySQL، نفضل استخدام Stored Procedure لاعتبارات المتطلبات والأداء.
            if (DB::getDriverName() === 'mysql') {
                try {
                    // استدعاء Stored Procedure مع ربط المعاملات (Prepared) لمنع حقن SQL.
                    $rows = collect(DB::select(
                        'CALL sp_search_store_branches(?, ?, ?, ?)',
                        [
                            $filters['name'] ?: null,
                            $statusFilter !== '' ? $statusFilter : null,
                            $filters['province_id'] ?: null,
                            $phoneFilter,
                        ]
                    ))->map(function ($row) {
                        // تأمين قيم افتراضية لتفادي أخطاء عند اختلاف أعمدة النتائج بين بيئات MySQL.
                        $defaults = $this->defaultStoredProcedureRow();

                        // دمج القيم القادمة مع الافتراضيات ثم تطبيع رقم الهاتف للعرض.
                        $merged = (object) array_merge($defaults, (array) $row);
                        $merged->phone = UserContact::phone($merged->phone, false);
                        return $merged;
                    });

                    // في بعض الحالات تكون أنواع البيانات القادمة من Stored Procedure مختلفة (مثلاً تواريخ/أوقات)،
                    // لذا نقرأ "نسخة مؤكدة" من جدول stores ونكتبها فوق القيم لضمان تطابق العرض دائماً.
                    if ($rows->isNotEmpty()) {
                        $extraById = DB::table('stores')
                            ->leftJoin('users', 'users.id', '=', 'stores.manager_id')
                            ->whereIn('stores.id', $rows->pluck('id')->all())
                            ->get([
                                'stores.id',
                                'stores.name',
                                'stores.branch_code',
                                'stores.city',
                                'stores.address',
                                'stores.phone',
                                'stores.status',
                                'stores.opening_date',
                                'stores.working_hours',
                                'stores.workday_starts_at',
                                'stores.workday_ends_at',
                                'stores.brochure_path',
                                'stores.manager_id',
                                'users.name as manager_name',
                            ])
                            ->keyBy('id');
                        $employeeCounts = DB::table('store_user')
                            ->select('store_id', DB::raw('COUNT(*) as cnt'))
                            ->whereIn('store_id', $rows->pluck('id')->all())
                            ->groupBy('store_id')
                            ->pluck('cnt', 'store_id');

                        // دمج البيانات الإضافية داخل كل Row حسب id.
                        $rows->each(function ($row) use ($extraById, $employeeCounts): void {
                            $extra = $extraById->get((int) ($row->id ?? 0));
                            if (! $extra) {
                                return;
                            }

                            $row->name = $extra->name;
                            $row->branch_code = $extra->branch_code;
                            $row->city = $extra->city;
                            $row->address = $extra->address;
                            $row->phone = UserContact::phone($extra->phone, false);
                            $row->status = $extra->status;
                            $row->opening_date = optional($extra->opening_date)->format('Y-m-d');
                            $row->working_hours = $extra->working_hours;
                            $row->workday_starts_at = $extra->workday_starts_at;
                            $row->workday_ends_at = $extra->workday_ends_at;
                            $row->brochure_path = $extra->brochure_path;
                            $row->manager_id = $extra->manager_id;
                            if ($extra->manager_name !== null && $extra->manager_name !== '') {
                                $row->manager_name = $extra->manager_name;
                            }
                            $row->employees_count = (int) ($employeeCounts[$row->id] ?? 0);
                        });
                    }

                    // ترتيب النتائج بحيث تظهر الأفرع الأساسية أولاً (قواعد UI/متطلبات المشروع).
                    return $this->sortRowsByPriority($rows);
                } catch (QueryException) {
                    // في حال فشل الإجراء المخزن (Stored Procedure) لأي سبب (غير موجود/صلاحيات/اختبارات)،
                    // ننتقل مباشرة لمسار Eloquent بالأسفل كحل احتياطي.
                }
            }

            // المسار الاحتياطي (Eloquent): يعمل في الاختبارات أو في قواعد بيانات غير MySQL.
            return $this->sortRowsByPriority(Store::query()
                ->select([
                    'id',
                    'name',
                    'description',
                    'branch_code',
                    'province_id',
                    'city',
                    'address',
                    'phone',
                    'status',
                    'opening_date',
                    'working_hours',
                    'workday_starts_at',
                    'workday_ends_at',
                    'brochure_path',
                    'manager_id',
                ])
                ->with([
                    // تحميل المحافظة والمدير بشكل eager loading لتجنب N+1.
                    'province:id,name,code',
                    'manager:id,name',
                ])
                // عدادات للعرض داخل بطاقة الفرع (عدد الموظفين/المنتجات).
                ->withCount(['employees', 'products'])
                // تطبيق فلاتر البحث عبر Scopes على نموذج Store.
                ->filterName($filters['name'] ?? null)
                ->filterProvince($filters['province_id'] ?? null)
                ->filterPhone($phoneFilter)
                ->filterStatus($statusFilter !== '' ? $statusFilter : null)
                // ترتيب من الأحدث للأقدم.
                ->latest()
                ->get()
                ->map(function (Store $store) {
                    // توحيد شكل البيانات (Row Object) ليكون مطابقاً لشكل نتائج Stored Procedure.
                    return (object) [
                        'id' => $store->id,
                        'name' => $store->name,
                        'description' => $store->description,
                        'branch_code' => $store->branch_code,
                        'province_name' => $store->province?->name,
                        'city' => $store->city,
                        'address' => $store->address,
                        'phone' => $store->phone,
                        'status' => $store->status,
                        'opening_date' => optional($store->opening_date)->format('Y-m-d'),
                        'working_hours' => $store->working_hours,
                        'workday_starts_at' => $store->workday_starts_at,
                        'workday_ends_at' => $store->workday_ends_at,
                        'manager_id' => $store->manager_id,
                        'manager_name' => $store->manager?->name,
                        'employees_count' => (int) ($store->employees_count ?? 0),
                        'products_count' => (int) ($store->products_count ?? 0),
                        'brochure_path' => $store->brochure_path,
                    ];
                })
                // ترتيب UI للأفرع الأساسية كما في المسار الأول.
                );
        });
    }

    private function normalizePhone(string $value): string
    {
        return preg_replace('/\D+/', '', $value);
    }

    private function branchPriority(string $branchCode): int
    {
        return self::PRIORITY_BRANCHES[$branchCode] ?? 2;
    }

    private function sortRowsByPriority(Collection $rows): Collection
    {
        return $rows
            ->sortBy(fn ($row) => $this->branchPriority((string) ($row->branch_code ?? '')))
            ->values();
    }

    private function defaultStoredProcedureRow(): array
    {
        return [
            'id' => 0,
            'name' => '',
            'description' => null,
            'branch_code' => '',
            'province_name' => '',
            'city' => '',
            'address' => '',
            'phone' => '',
            'status' => '',
            'opening_date' => null,
            'working_hours' => null,
            'workday_starts_at' => null,
            'workday_ends_at' => null,
            'manager_id' => null,
            'manager_name' => null,
            'employees_count' => 0,
            'products_count' => 0,
            'brochure_path' => null,
        ];
    }
}