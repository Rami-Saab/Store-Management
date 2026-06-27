<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Controllers\Api;

/**
 * Controller: واجهة API لإدارة الأفرع (REST)
 *
 * هذه الـ Endpoints مطلوبة ضمن المتطلبات الأكاديمية للكتلة البرمجية الثالثة:
 * - GET  /api/stores        : جلب قائمة الأفرع (مع فلاتر اختيارية عبر Query).
 * - GET  /api/stores/search : بحث سريع يعتمد على StoreSearchService (وقد يستخدم Stored Procedure في MySQL).
 * - POST /api/stores        : إنشاء فرع جديد (مع التحقق من الصلاحيات والتحقق من صحة المدخلات).
 * - PUT  /api/stores/{id}   : تحديث فرع.
 * - DELETE /api/stores/{id} : حذف فرع (مع منع الحذف عند وجود روابط منتجات/مستودعات).
 *
 * ملاحظات مهمة:
 * - تم استخدام Form Requests (StoreRequest/UpdateStoreRequest) لتوحيد قواعد التحقق من المدخلات.
 * - تم استخدام StoreCrudService لتنفيذ الإنشاء/التحديث داخل Transaction ولتوحيد منطق ربط المنتجات/الموظفين.
 * - الصلاحيات يتم التحقق منها عبر StorePolicy (authorize).
 */

// الاستيرادات اللازمة لهذا الـ API Controller.
use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
// Form Requests للتحقق من بيانات الإنشاء/التحديث.
use App\Http\Requests\StoreRequest;
use App\Http\Requests\UpdateStoreRequest;
// نموذج الفرع (Route Model Binding).
use App\Models\Store;
// خدمة CRUD لتنفيذ create/update/delete داخل Transaction.
use App\Services\Store\StoreCrudService;
// خدمة البحث (قد تعتمد على Stored Procedure).
use App\Services\Store\StoreSearchService;
// خدمة الفرع لمنطق الأعمال مثل قيود الحذف.
// Trait صلاحيات وحدة إدارة الأفرع.
use App\Traits\StoreAuthorization;
// استجابة JSON.
use Illuminate\Http\JsonResponse;
// Request لقراءة Query params.
use Illuminate\Http\Request;
// Auth لإحضار المستخدم الحالي.
use Illuminate\Support\Facades\Auth;

// RESTful API خاص بإدارة الفروع (Block 3).
class StoreApiController extends Controller
{
    use StoreAuthorization;

    public function __construct(
        // CRUD service: منطق الإنشاء/التحديث/الحذف.
        private StoreCrudService $storeCrudService,
        // Search service: بحث صفوف الأفرع.
        private StoreSearchService $searchService,
    )
    {
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $currentUser = Auth::user();
        $this->authorize('viewAny', Store::class);

        $query = Store::query()
            ->with(['province', 'manager', 'employees', 'products', 'warehouses'])
            ->filterName($request->input('name'))
            ->filterStatus($request->input('status'))
            ->filterProvince($request->input('province_id'))
            ->filterPhone($request->input('phone'))
            ->latest();

        if (! $this->isAdmin($currentUser)) {
            $allowedIds = $this->allowedStores($currentUser)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $query->whereIn('id', $allowedIds ?: [0]);
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min($perPage, 100));
        $paginator = $query->paginate($perPage)->appends($request->query());

        return $this->resourceResponse(
            StoreResource::collection($paginator),
            'Stores fetched successfully.'
        );
    }

    public function apiSearch(Request $request): JsonResponse
    {
        $currentUser = Auth::user();
        $this->authorize('viewAny', Store::class);

        // تجهيز فلاتر البحث بنفس شكل فلاتر الواجهة (UI) لتوحيد المنطق.
        $filters = [
            'name' => trim((string) $request->input('name', '')),
            'status' => (string) $request->input('status', ''),
            'province_id' => $request->input('province_id'),
            'phone' => (string) $request->input('phone', ''),
        ];

        // تنفيذ البحث عبر StoreSearchService (قد يستعمل Stored Procedure).
        $rows = $this->searchService->searchStoreRows($filters);
        if (! $this->isAdmin($currentUser)) {
            $allowedIds = $this->allowedStores($currentUser)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $rows = $rows->filter(fn ($row) => in_array((int) ($row->id ?? 0), $allowedIds, true))->values();
        }

        return response()->json([
            'success' => true,
            'message' => 'Search results fetched successfully.',
            'data' => $rows->values(),
        ]);
    }

    public function apiStore(StoreRequest $request): JsonResponse
    {
        $currentUser = Auth::user();
        $this->authorize('create', Store::class);

        // البيانات بعد التحقق.
        $validated = $request->validated();
        // المستخدم الحالي لتحديد الصلاحيات ومن هو المنفذ.
        // إشعارات/تدقيق التعيينات يتم تفعيلها عند Admin.
        $notifyAssignments = $this->isAdmin($currentUser);
        // معرف المنفذ ليتم تخزينه في created_by/updated_by.
        $actorId = $currentUser?->id;

        // إنشاء الفرع عبر خدمة CRUD مع مزامنة المنتجات (syncProducts = true).
        $result = $this->storeCrudService->create(
            $request,
            $validated,
            $actorId,
            $notifyAssignments,
            true
        );

        // تحميل العلاقات لإرجاع كائن كامل.
        $store = $result['store']->load(['province', 'manager', 'employees', 'products', 'warehouses']);

        return $this->resourceResponse(
            new StoreResource($store),
            'Store created successfully.',
            201
        );
    }

    public function apiShow(Store $store): JsonResponse
    {
        $this->authorize('view', $store);

        $store->load(['province', 'manager', 'employees', 'products', 'warehouses']);

        return $this->resourceResponse(
            new StoreResource($store),
            'Store fetched successfully.'
        );
    }

    public function apiUpdate(UpdateStoreRequest $request, Store $store): JsonResponse
    {
        $currentUser = Auth::user();
        $this->authorize('update', $store);

        // البيانات بعد التحقق.
        $validated = $request->validated();
        // المستخدم الحالي لتحديد الصلاحيات ومن هو المنفذ.
        // إشعارات التعيينات عند Admin.
        $notifyAssignments = $this->isAdmin($currentUser);
        // معرف المنفذ لتخزين updated_by.
        $actorId = $currentUser?->id;

        // تحديث الفرع عبر خدمة CRUD مع مزامنة المنتجات.
        $result = $this->storeCrudService->update(
            $store,
            $request,
            $validated,
            $actorId,
            $notifyAssignments,
            true
        );

        // تحميل العلاقات ثم الإرجاع.
        $store = $result['store']->load(['province', 'manager', 'employees', 'products', 'warehouses']);

        return $this->resourceResponse(
            new StoreResource($store),
            'Store updated successfully.'
        );
    }

    public function apiDestroy(Store $store): JsonResponse
    {
        $this->authorize('delete', $store);

        try {
            $this->storeCrudService->delete($store);
        } catch (\App\Exceptions\StoreDeletionBlockedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        // إرجاع رسالة نجاح.
        return response()->json([
            'success' => true,
            'message' => 'Store deleted successfully.',
        ]);
    }

    private function resourceResponse($resource, string $message, int $status = 200): JsonResponse
    {
        return $resource->additional([
            'success' => $status >= 200 && $status < 300,
            'message' => $message,
        ])->response()->setStatusCode($status);
    }
}