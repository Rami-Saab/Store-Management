<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Controllers\Auth;

/**
 * Controller: تسجيل الدخول/الخروج
 *
 * النظام في هذا المشروع يستخدم تسجيل دخول بسيط:
 * - المستخدم يدخل الاسم (name) وكلمة المرور.
 * - يتم إنشاء جلسة (Session) بعد نجاح المصادقة.
 *
 * ملاحظة:
 * - يتم استخدام LockedUsers لضمان وجود حسابات ثابتة خاصة بالمشروع الجامعي
 *   (حتى لو تم تعديل البيانات بالخطأ).
 */

// الاستيرادات اللازمة لعمليات المصادقة.
use App\Http\Controllers\Controller;
// LockedUsers: آلية داخل المشروع لضمان وجود مستخدمين ثابتين أثناء تشغيل النظام.
use App\Support\LockedUsers;
// Redirect بعد تسجيل الدخول/الخروج.
use Illuminate\Http\RedirectResponse;
// Request لقراءة المدخلات وإدارة session.
use Illuminate\Http\Request;
// Auth: محرك تسجيل الدخول في Laravel.
use Illuminate\Support\Facades\Auth;
// View لعرض صفحات Blade.
use Illuminate\View\View;

class StoreAuthController extends Controller
{
    public function login(Request $request): View
    {
        // ضمان تواجد الحسابات "المقفلة/الثابتة" الخاصة بالمشروع قبل عرض صفحة الدخول.
        $this->ensureLockedUsers();

        // عرض صفحة تسجيل الدخول.
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        // ضمان وجود المستخدمين الثابتين قبل محاولة تسجيل الدخول.
        $this->ensureLockedUsers();

        // التحقق من بيانات تسجيل الدخول باستخدام bag باسم "login" حتى تظهر الأخطاء في مكانها الصحيح في الواجهة.
        $credentials = $request->validateWithBag('login', [
            'name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // تطبيع اسم المستخدم بإزالة المسافات حوله.
        $identifier = trim((string) $credentials['name']);
        // كلمة المرور كما هي.
        $password = $credentials['password'];

        // محاولة تسجيل الدخول: نستخدم name كمُعرف في هذا المشروع (بدلاً من email).
        $remember = $request->boolean('remember');
        $loggedIn = Auth::attempt(['name' => $identifier, 'password' => $password], $remember);

        // في حال فشل تسجيل الدخول نعيد المستخدم لنفس الصفحة مع رسالة خطأ قياسية.
        if (! $loggedIn) {
            return back()
                ->withErrors(['password' => __('auth.failed')], 'login')
                ->onlyInput('name', 'remember');
        }

        // تجديد session ID لتفادي Session Fixation بعد نجاح تسجيل الدخول.
        $request->session()->regenerate();

        // التحقق من حالة "الشاغر" لموظفي/مدراء قسم إدارة الفروع.
        $currentUser = Auth::user();
        if ($currentUser instanceof \App\Models\User && in_array($currentUser->role, ['store_employee', 'store_manager'], true)) {
            $hasAssignment = (bool) $currentUser->store_id
                || $currentUser->managedStore()->exists()
                || $currentUser->stores()->exists();
            if (! $hasAssignment) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->with('warning', 'Your account is currently marked as vacant. Please wait until you are assigned to a branch or department.');
            }
        }

        // تحويل المستخدم إلى لوحة المعلومات بعد نجاح تسجيل الدخول.
        return redirect()->to(route('dashboard', [], false));
    }

    public function logout(Request $request): RedirectResponse
    {
        // تسجيل الخروج من نظام Laravel.
        Auth::logout();
        // إبطال الجلسة الحالية.
        $request->session()->invalidate();
        // إعادة توليد CSRF token لضمان الأمان.
        $request->session()->regenerateToken();

        // إعادة المستخدم لصفحة تسجيل الدخول.
        return redirect()->to(route('login', [], false));
    }

    private function ensureLockedUsers(): void
    {
        // أثناء الاختبارات نترك البيانات كما هي لتفادي تأثيرات جانبية.
        if (app()->environment('testing')) {
            return;
        }

        try {
            // إنشاء/تحديث المستخدمين الثابتين (مدير النظام/مدير فرع/موظفين...) حسب ما يفرضه المشروع.
            LockedUsers::ensure();
        } catch (\Throwable $e) {
            // في حال حدوث خطأ لا نكسر صفحة الدخول، بل نسجل الخطأ في logs.
            report($e);
        }
    }
}