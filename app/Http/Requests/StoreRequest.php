<?php // Name : Rodain Gouzlan Id:

// هذا الملف هو Form Request مركزي لعمليات إدارة الفروع.
// الهدف: توحيد منطق الـ validation والـ authorization لعدة مسارات في مكان واحد.

namespace App\Http\Requests;

use App\Models\Store;
use App\Support\EnglishInputNormalizer;
use App\Services\Store\StoreService;
use App\Traits\StoreAuthorization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    use StoreAuthorization;

    // ثوابت لتحديد سيناريو الطلب (scenario) اعتمادًا على اسم المسار.
    private const SCENARIO_STORE = 'store';
    private const SCENARIO_ASSIGNMENTS = 'assignments';
    private const SCENARIO_BROCHURE_CHUNK = 'brochure_chunk';

    // رسالة تفصيلية نستخدمها عند فشل التفويض في سيناريو التعيينات.
    private ?string $authorizationFailureMessage = null;

    public function authorize(): bool
    {
        // نحدد السيناريو الحالي أولًا حتى نطبّق قواعد التفويض المناسبة فقط.
        $scenario = $this->requestScenario();

        // تفويض مخصص لصفحة Assign Staff: يسمح فقط للـ admin.
        if ($scenario === self::SCENARIO_ASSIGNMENTS) {
            $currentUser = $this->user();
            if (! $this->isAdmin($currentUser)) {
                $this->authorizationFailureMessage = 'Only system administrators can manage staff assignments.';
                return false;
            }

            return true;
        }

        // تفويض خاص برفع ملف brochure على شكل chunks.
        // هنا نكتفي بالتأكد أن المستخدم يملك صلاحية الدخول لوحدة الفروع.
        if ($scenario === self::SCENARIO_BROCHURE_CHUNK) {
            $this->abortUnlessStoreModuleUser($this->user());
            return true;
        }
        // التحقق من أن المستخدم مسموح له بدخول وحدة إدارة الأفرع عند استدعاء هذا الطلب
        // (نفعّل ذلك فقط لمسارات stores.* و api.stores.* حتى لا نتداخل مع طلبات أخرى).
        $routeName = (string) ($this->route()?->getName() ?? '');
        if ($routeName !== '' && (str_starts_with($routeName, 'stores.') || str_starts_with($routeName, 'api.stores.'))) {
            $this->abortUnlessStoreModuleUser($this->user());
        }

        // إذا لم يتم عمل abort نسمح بمتابعة الطلب.
        return true;
    }

    // عند فشل التفويض نعيد رسالة مناسبة حسب نوع الطلب (JSON أو Redirect).
    protected function failedAuthorization(): void
    {
        if ($this->requestScenario() !== self::SCENARIO_ASSIGNMENTS) {
            parent::failedAuthorization();
        }

        $message = $this->authorizationFailureMessage ?? 'Unauthorized.';
        $store = $this->route('store');

        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'ok' => false,
                'message' => $message,
            ], 403));
        }

        throw new HttpResponseException(
            redirect()
                ->to(route('stores.assignments', $store, false))
                ->with('warning', $message)
        );
    }

    // نختار قواعد التحقق بناءً على السيناريو.
    public function rules(): array
    {
        return match ($this->requestScenario()) {
            self::SCENARIO_ASSIGNMENTS => $this->assignmentRules(),
            self::SCENARIO_BROCHURE_CHUNK => $this->brochureChunkRules(),
            default => $this->storeRules(),
        };
    }

    // قواعد التحقق الخاصة بإنشاء/تعديل الفرع (CRUD).
    private function storeRules(): array
    {
        $managerRule = [
            'nullable',
            Rule::exists('users', 'id')->where('role', 'store_manager'),
        ];

        // قيود "الإنجليزية فقط" مبنية على ASCII printable لضمان أن البيانات المخزنة تكون بالإنجليزية.
        $englishText = 'regex:/^[\\x20-\\x7E]+$/';
        $englishOptional = 'regex:/^[\\x20-\\x7E]*$/';
        $englishOptionalWithNewlines = 'regex:/^[\\x20-\\x7E\\r\\n]*$/';

        return [
            // اسم الفرع مطلوب وبالإنجليزية فقط.
            'name' => ['required', 'string', 'max:255', $englishText],
            // كود الفرع مطلوب، يسمح بحروف/أرقام/شرطة، ويجب أن يكون unique (مع ignore عند التحديث).
            'branch_code' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\\-]+$/', Rule::unique('stores', 'branch_code')->ignore($this->storeId())],
            // المحافظة مطلوبة ويجب أن تكون موجودة.
            'province_id' => ['required', 'exists:provinces,id'],
            // المدينة اختيارية لكن إن كُتبت يجب أن تكون بالإنجليزية.
            'city' => ['nullable', 'string', 'max:120', $englishOptional],
            // العنوان مطلوب وبالإنجليزية.
            'address' => ['required', 'string', 'max:500', $englishText],
            // الهاتف مطلوب ويجب أن يبدأ بـ 09 حسب متطلبات المشروع.
            'phone' => ['required', 'regex:/^09\\d{7,18}$/'],
            // الوصف اختياري ويسمح بأسطر جديدة.
            'description' => ['nullable', 'string', 'max:2000', $englishOptionalWithNewlines],
            // البريد الإلكتروني اختياري لكن إن وجد يجب أن يكون صيغة صحيحة.
            'email' => ['nullable', 'email', 'max:255'],
            // الحالة يجب أن تكون ضمن القيم المحددة في StoreService.
            'status' => ['required', Rule::in(StoreService::STORE_STATUSES)],
            // أوقات الدوام مطلوبة بصيغة 24 ساعة HH:MM.
            'workday_starts_at' => ['required', 'date_format:H:i'],
            'workday_ends_at' => ['required', 'date_format:H:i'],
            // تاريخ الافتتاح مطلوب ويجب ألا يكون في المستقبل.
            'opening_date' => ['required', 'date', 'before_or_equal:today'],
            // ملف البرشور اختياري، يجب أن يكون PDF.
            'brochure' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            // مسار البرشور (في حال تم رفعه على شكل chunks عبر AJAX ثم تمرير المسار أثناء الحفظ).
            'brochure_path' => ['nullable', 'string', 'max:255', 'regex:/^brochures\\/stores\\/.+\\.pdf$/i'],
            // مدير الفرع (اختياري؛ يتم تعيينه لاحقًا بواسطة مدير النظام).
            'manager_id' => $managerRule,
            // قائمة الموظفين اختيارية.
            'employee_ids' => ['nullable', 'array'],
            // كل عنصر يجب أن يكون user id صحيح.
            'employee_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->whereIn('role', ['store_employee']),
            ],
            // قائمة المنتجات اختيارية.
            'product_ids' => ['nullable', 'array'],
            // كل عنصر يجب أن يكون product id صحيح.
            'product_ids.*' => ['integer', 'exists:products,id'],
        ];
    }

    // قواعد التحقق الخاصة بتعيين المدير والموظفين للفرع.
    private function assignmentRules(): array
    {
        return [
            'manager_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'store_manager'),
            ],
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->whereIn('role', ['store_employee']),
            ],
            'removed_employee_ids' => ['nullable', 'array'],
            'removed_employee_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->whereIn('role', ['store_employee']),
            ],
        ];
    }

    // قواعد التحقق الخاصة برفع brochure على شكل chunks.
    private function brochureChunkRules(): array
    {
        return [
            'upload_id' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9-]{16,64}$/'],
            'chunk_index' => ['required', 'integer', 'min:0'],
            'total_chunks' => ['required', 'integer', 'min:1', 'max:200000'],
            'file_name' => ['required', 'string', 'max:255'],
            'chunk' => ['required', 'file'],
        ];
    }

    // رسائل أخطاء مخصصة تظهر للمستخدم في واجهة الفرع.
    public function messages(): array
    {
        if ($this->requestScenario() !== self::SCENARIO_STORE) {
            return [];
        }
        // رسائل مخصصة لجعل التحذيرات مفهومة للمستخدم النهائي (UI).
        return [
            'name.required' => 'Branch name is required.',
            'name.regex' => 'Use English letters, numbers, and basic punctuation only.',
            'branch_code.regex' => 'Use English letters, numbers, and dashes only (example: DAM-001).',
            'city.regex' => 'Use English letters only (example: Damascus).',
            'address.regex' => 'Use English letters, numbers, and standard punctuation only.',
            'description.regex' => 'Use English letters, numbers, and punctuation only.',
            'phone.regex' => 'Phone number must start with 09 and contain digits only.',
            'brochure.mimes' => 'The brochure file must be a PDF.',
            'brochure.max' => 'The brochure file must not exceed 10 MB.',
            'workday_starts_at.required' => 'Start time is required.',
            'workday_ends_at.required' => 'End time is required.',
            'workday_starts_at.date_format' => 'Start time format is invalid.',
            'workday_ends_at.date_format' => 'End time format is invalid.',
            'manager_id.required' => 'Store manager is required.',
        ];
    }

    // تجهيز البيانات قبل التحقق: تنظيف الحقول وتطبيق قواعد العمل.
    protected function prepareForValidation(): void
    {
        if ($this->requestScenario() !== self::SCENARIO_STORE) {
            return;
        }
        // قبل التحقق: نطبع المدخلات (مثل إزالة مسافات زائدة/تحويل بعض الرموز) لتقليل رفض البيانات الصحيحة.
        $payload = EnglishInputNormalizer::normalizeStorePayload($this->all());

        $currentUser = $this->user();
        $store = $this->route('store');
        $isCreate = ! ($store instanceof Store);

        // عند إنشاء الفرع: يجب أن يُحفظ بدون مدير وبدون موظفين.
        if ($isCreate) {
            $payload['manager_id'] = null;
            $payload['employee_ids'] = [];
        } elseif (! $currentUser || ! ($currentUser->role === 'admin')) {
            // عند التعديل، غير المدير النظام لا يُسمح له بتغيير المدير/الموظفين.
            $payload['manager_id'] = $store->manager_id ? (int) $store->manager_id : null;
            $payload['employee_ids'] = [];
        }

        $this->merge($payload);
    }

    // تحقق إضافي يعتمد على أكثر من حقل (cross-field validation).
    public function withValidator($validator): void
    {
        $scenario = $this->requestScenario();

        // تحقق مخصص لرفع الـ brochure بالقطع.
        if ($scenario === self::SCENARIO_BROCHURE_CHUNK) {
            $validator->after(function ($validator) {
                $index = $this->input('chunk_index');
                $total = $this->input('total_chunks');

                if (! is_numeric($index) || ! is_numeric($total)) {
                    return;
                }

                if ((int) $total > 0 && (int) $index >= (int) $total) {
                    $validator->errors()->add('chunk_index', 'Chunk index is out of range.');
                }
            });

            return;
        }

        // تحقق مخصص لصفحة التعيينات (manager + employees).
        if ($scenario === self::SCENARIO_ASSIGNMENTS) {
            $validator->after(function ($validator) {
                $managerId = (int) $this->input('manager_id');
                $employeeIds = collect($this->input('employee_ids', []))
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->all();

                if ($managerId && in_array($managerId, $employeeIds, true)) {
                    $validator->errors()->add('employee_ids', 'The store manager cannot be assigned as an employee in the same store.');
                }

                $store = $this->route('store');
                $storeDepartmentId = $store instanceof Store ? (int) ($store->department_id ?? 0) : 0;
                if ($storeDepartmentId && $managerId) {
                    $managerDepartmentId = (int) (DB::table('users')->where('id', $managerId)->value('department_id') ?? 0);
                    if ($managerDepartmentId && $managerDepartmentId !== $storeDepartmentId) {
                        $validator->errors()->add('manager_id', 'The selected manager must belong to the same department as the store.');
                    }
                }

                if ($storeDepartmentId && $employeeIds !== []) {
                    $employeeDepartmentIds = DB::table('users')
                        ->whereIn('id', $employeeIds)
                        ->pluck('department_id')
                        ->filter()
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values()
                        ->all();
                    if ($employeeDepartmentIds !== [] && count($employeeDepartmentIds) > 1 || ($employeeDepartmentIds !== [] && $employeeDepartmentIds[0] !== $storeDepartmentId)) {
                        $validator->errors()->add('employee_ids', 'Employees must belong to the same department as the store.');
                    }
                }
            });

            return;
        }
        // تحقق إضافي يعتمد على أكثر من حقل: مدة الدوام بين min و max (بالساعات).
        $validator->after(function ($validator) {
            $managerId = (int) $this->input('manager_id');
            $employeeIds = collect($this->input('employee_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->all();

            if ($managerId && in_array($managerId, $employeeIds, true)) {
                $validator->errors()->add('employee_ids', 'The store manager cannot be assigned as an employee in the same store.');
            }

            $store = $this->route('store');
            $storeDepartmentId = $store instanceof Store ? (int) ($store->department_id ?? 0) : 0;
            $managerDepartmentId = $managerId ? (int) (DB::table('users')->where('id', $managerId)->value('department_id') ?? 0) : 0;
            if ($storeDepartmentId && $managerId && $managerDepartmentId && $managerDepartmentId !== $storeDepartmentId) {
                $validator->errors()->add('manager_id', 'The selected manager must belong to the same department as the store.');
            }

            if ($storeDepartmentId && $employeeIds !== []) {
                $employeeDepartmentIds = DB::table('users')
                    ->whereIn('id', $employeeIds)
                    ->pluck('department_id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();
                if ($employeeDepartmentIds !== [] && count($employeeDepartmentIds) > 1 || ($employeeDepartmentIds !== [] && $employeeDepartmentIds[0] !== $storeDepartmentId)) {
                    $validator->errors()->add('employee_ids', 'Employees must belong to the same department as the store.');
                }
            }

            $start = $this->input('workday_starts_at');
            $end = $this->input('workday_ends_at');

            // لا نكمل إذا كانت القيم غير نصية.
            if (! is_string($start) || ! is_string($end)) {
                return;
            }

            // تأكد من صيغة HH:MM.
            if (! preg_match('/^\\d{2}:\\d{2}$/', $start) || ! preg_match('/^\\d{2}:\\d{2}$/', $end)) {
                return;
            }

            try {
                // تحويل HH:MM إلى أرقام.
                [$sh, $sm] = array_map('intval', explode(':', $start, 2));
                [$eh, $em] = array_map('intval', explode(':', $end, 2));
            } catch (\Throwable) {
                return;
            }

            // حماية من قيم خارج نطاق الوقت الطبيعي.
            if ($sh < 0 || $sh > 23 || $sm < 0 || $sm > 59 || $eh < 0 || $eh > 23 || $em < 0 || $em > 59) {
                return;
            }

            // حساب المدة بالدقائق.
            $startMinutes = ($sh * 60) + $sm;
            $endMinutes = ($eh * 60) + $em;
            $duration = $endMinutes - $startMinutes;
            if ($duration <= 0) {
                $duration += 1440; // في حال كان الدوام يعبر منتصف الليل (مثال: 22:00 إلى 06:00)
            }

            // الحدود الدنيا/العليا (بالساعات) تأتي من config/store.php.
            $minHours = (int) config('store.shift_min_hours', 8);
            $maxHours = config('store.shift_max_hours', null);
            $maxHours = is_numeric($maxHours) ? (int) $maxHours : 0;
            $minMinutes = max(0, $minHours) * 60;
            $maxMinutes = max(0, $maxHours) * 60;

            // إضافة خطأ إلى workday_ends_at حتى يظهر التحذير أسفل حقل النهاية في الواجهة.
            if ($duration < $minMinutes) {
                $validator->errors()->add('workday_ends_at', "The working time is too short. Business hours must be at least {$minHours} hours.");
            } elseif ($maxMinutes > 0 && $duration > $maxMinutes) {
                $validator->errors()->add('workday_ends_at', "The working time is too long. Business hours must not exceed {$maxHours} hours.");
            }
        });
    }

    // تحديد السيناريو من اسم المسار (route name).
    private function requestScenario(): string
    {
        $routeName = (string) ($this->route()?->getName() ?? '');

        return match ($routeName) {
            'stores.assignments.update' => self::SCENARIO_ASSIGNMENTS,
            'stores.brochure.uploadChunk' => self::SCENARIO_BROCHURE_CHUNK,
            default => self::SCENARIO_STORE,
        };
    }

    // استخراج معرف الفرع من المسار لتطبيق قواعد unique بشكل صحيح.
    protected function storeId(): ?int
    {
        // دعم Route Model Binding: قد يكون store كائن Store أو رقم id حسب المسار.
        $store = $this->route('store');
        if ($store instanceof Store) {
            return $store->getKey();
        }

        if (is_numeric($store)) {
            return (int) $store;
        }

        return null;
    }
}