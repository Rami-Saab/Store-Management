<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Controllers\Auth;

// هذا الـ Controller مسؤول عن المصادقة عبر API Token (عادة باستخدام Laravel Sanctum).
// الغاية: تسجيل الدخول بدون Session وإرجاع توكن يمكن استخدامه في طلبات AJAX أو تطبيقات خارجية.
// الاستيرادات الأساسية الخاصة بالمصادقة والتعامل مع المستخدمين.
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\LockedUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Controller: مصادقة التوكن (Token Auth)
 *
 * يوفر:
 * - تسجيل الدخول وإصدار Token جديد
 * - إصدار Token من جلسة حالية
 * - إلغاء Token الحالي
 */
class TokenAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        // تأكيد وجود الحسابات الثابتة قبل أي عملية مصادقة.
        $this->ensureLockedUsers();

        // التحقق من المدخلات الأساسية (اسم المستخدم + كلمة المرور).
        $credentials = $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // تطبيع المدخلات.
        $identifier = trim((string) $credentials['name']);
        $password = (string) $credentials['password'];

        // جلب المستخدم والتحقق من كلمة المرور.
        $user = User::query()->where('name', $identifier)->first();
        if (! $user || ! Hash::check($password, (string) $user->password)) {
            return response()->json([
                'message' => __('auth.failed'),
                'errors' => ['name' => [__('auth.failed')]],
            ], 422);
        }

        // منع تسجيل الدخول للمستخدمين الموسومين كشاغرين (بدون تعيين فعلي).
        if ($user instanceof User && in_array($user->role, ['store_manager', 'store_employee'], true)) {
            $hasAssignment = (bool) $user->store_id
                || $user->managedStore()->exists()
                || $user->stores()->exists()
                || (int) ($user->department_id ?? 0) > 0
                || trim((string) $user->department) !== '';
            if (! $hasAssignment) {
                return response()->json([
                    'message' => 'Your account is currently marked as vacant. Please wait until you are assigned to a branch or department.',
                ], 423);
            }
        }

        // اسم التوكن يعتمد على معرف التبويب (إن وُجد) لتفادي تضارب الجلسات.
        $tokenName = $this->resolveTokenName($request);
        if ($tokenName !== '') {
            $user->tokens()->where('name', $tokenName)->delete();
        }

        // إنشاء توكن جديد وإرجاعه للواجهة.
        $token = $user->createToken($tokenName !== '' ? $tokenName : 'tab-session');

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'department' => $user->department,
                'role' => $user->role,
                'job_title' => $user->job_title,
            ],
        ]);
    }

    public function issueFromSession(Request $request): JsonResponse
    {
        // إصدار Token لمستخدم مسجل عبر Session ويب.
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // تنظيف أي Token سابق بنفس الاسم لضمان توكن واحد لكل تبويب.
        $tokenName = $this->resolveTokenName($request);
        if ($tokenName !== '') {
            $user->tokens()->where('name', $tokenName)->delete();
        }

        // إنشاء توكن جديد وإرجاعه للواجهة.
        $token = $user->createToken($tokenName !== '' ? $tokenName : 'tab-session');

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    public function logoutToken(Request $request): JsonResponse
    {
        // إلغاء توكن المستخدم الحالي (إذا كان موجوداً).
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // حذف التوكن الحالي من جدول personal_access_tokens.
        $current = $user->currentAccessToken();
        if ($current) {
            $current->delete();
        }

        return response()->json(['message' => 'Token revoked.']);
    }

    private function resolveTokenName(Request $request): string
    {
        // التقاط معرف التبويب من الهيدر أو من query/body.
        $tabId = $request->header('X-Tab-Id');
        if (! is_string($tabId) || $tabId === '') {
            $tabId = $request->input('tab');
        }
        if (! is_string($tabId) || $tabId === '') {
            $tabId = $request->query('tab');
        }
        $tabId = is_string($tabId) ? trim($tabId) : '';

        if ($tabId !== '' && preg_match('/^[A-Za-z0-9_-]{6,64}$/', $tabId)) {
            return 'tab-'.$tabId;
        }

        return '';
    }

    private function ensureLockedUsers(): void
    {
        // أثناء الاختبارات لا نريد تعديل بيانات فعلية.
        if (app()->environment('testing')) {
            return;
        }

        try {
            // إنشاء/تحديث المستخدمين الثابتين المطلوبة للنظام.
            LockedUsers::ensure();
        } catch (\Throwable $e) {
            // نكتفي بتسجيل الخطأ بدون تعطيل تسجيل الدخول.
            report($e);
        }
    }
}