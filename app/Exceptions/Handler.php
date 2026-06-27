<?php // Name : Rodain Gouzlan Id:

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * معالج الاستثناءات (Exception Handler)
 *
 * في هذا المشروع تم إضافة معالجة خاصة لحالة 419 (انتهاء الجلسة/CSRF):
 * - عند حدوث TokenMismatchException يتم تسجيل معلومات مفيدة في log
 * - ثم إعادة المستخدم برسالة واضحة بدلاً من صفحة خطأ غامضة.
 */
class Handler extends ExceptionHandler
{
    /**
     * قائمة أنواع الاستثناءات التي لا يتم تسجيلها في التقارير (Report).
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * قائمة الحقول التي لا يتم إرجاع قيمها في جلسة الأخطاء عند فشل التحقق (لأسباب أمنية).
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * تسجيل/تهيئة معالجات الاستثناءات (Exception Handlers).
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (PostTooLargeException $e, Request $request) {
            // This exception happens before Laravel validation when the incoming request body
            // exceeds PHP/server limits (post_max_size, upload_max_filesize, nginx client_max_body_size, ...).
            //
            // We return a friendly response instead of the default error page.

            $uploadMax = (string) (ini_get('upload_max_filesize') ?: '');
            $postMax = (string) (ini_get('post_max_size') ?: '');

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Upload failed: the submitted data is too large. Please upload a smaller file.',
                    'limits' => [
                        'upload_max_filesize' => $uploadMax,
                        'post_max_size' => $postMax,
                    ],
                ], 413);
            }

            return response()
                ->view('errors.413', [
                    'uploadMax' => $uploadMax,
                    'postMax' => $postMax,
                ], 413);
        });

        $this->renderable(function (TokenMismatchException $e, Request $request) {
            Log::warning('CSRF token mismatch (419).', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'host' => $request->getHost(),
                'referer' => $request->headers->get('referer'),
                'user_agent' => $request->userAgent(),
                'session_driver' => config('session.driver'),
                'session_cookie' => config('session.cookie'),
                'has_session_cookie' => $request->cookies->has((string) config('session.cookie')),
                'has_xsrf_cookie' => $request->cookies->has('XSRF-TOKEN'),
                'expects_json' => $request->expectsJson(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session expired. Please refresh and try again.',
                ], 419);
            }

            return redirect()
                ->back()
                ->with('warning', 'Your session has expired (CSRF). Refresh the page and try again.');
        });

        $this->renderable(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            Log::warning('HTTP 419 Page Expired.', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'host' => $request->getHost(),
                'referer' => $request->headers->get('referer'),
                'user_agent' => $request->userAgent(),
                'session_driver' => config('session.driver'),
                'session_cookie' => config('session.cookie'),
                'has_session_cookie' => $request->cookies->has((string) config('session.cookie')),
                'has_xsrf_cookie' => $request->cookies->has('XSRF-TOKEN'),
                'expects_json' => $request->expectsJson(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session expired. Please refresh and try again.',
                ], 419);
            }

            return redirect()
                ->back()
                ->with('warning', 'Your session has expired. Refresh the page and try again.');
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}