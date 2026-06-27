<?php // Name : Rodain Gouzlan Id:

namespace App\Services\Store;

/**
 * Service: إدارة البرشور (Store Brochure) للفرع
 *
 * ما الذي يقدمه هذا الملف:
 * - عرض بيانات البرشور داخل صفحة Blade (viewData + stores.brochure).
 * - تنزيل البرشور كملف PDF (download):
 *   1) إذا كان هناك ملف PDF مرفوع (brochure_path) يتم تنزيله مباشرة.
 *   2) إذا لم يوجد، يحاول العثور على نسخة مولدة/مخزنة سابقاً.
 *   3) إذا لم توجد نسخة، يقوم بتوليد PDF من صفحة Blade عبر Chrome Headless.
 *
 * لماذا هذا مهم للمشروع:
 * - متطلب "رفع/تحميل/عرض البرشور" جزء أساسي من الكتلة البرمجية الثالثة.
 * - هذا الملف يعزل منطق الملفات والتخزين عن Controllers لتبقى Controllers بسيطة.
 *
 * ملاحظة:
 * - يعتمد على Storage disk = public حتى يكون الوصول عبر /storage ممكناً بعد php artisan storage:link.
 */

// الاستيرادات اللازمة للتعامل مع نموذج الفرع + توليد/تحميل PDF + التعامل مع الملفات.
use App\Models\Store;
use App\Support\UserContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Process\Process;

class StoreBrochureService
{
    public function forgetGeneratedCacheByBranchCode(?string $branchCode): void
    {
        $branchCode = strtoupper(trim((string) $branchCode));
        if ($branchCode === '') {
            return;
        }

        $dir = storage_path('app/public/brochures/stores');
        $candidates = [
            $dir.DIRECTORY_SEPARATOR.'branch-'.$branchCode.'.pdf',
            $dir.DIRECTORY_SEPARATOR.'branch-'.strtolower($branchCode).'.pdf',
            $dir.DIRECTORY_SEPARATOR.'branch-'.strtoupper($branchCode).'.pdf',
        ];

        foreach (array_unique($candidates) as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * @return BinaryFileResponse|RedirectResponse
     */
    public function download(Store $store)
    {
        // تجهيز بيانات البرشور (اسم/عنوان/مدير/ساعات...) كما ستظهر في صفحة view.
        $data = $this->viewData($store);

        // تجهيز اسم ملف تحميل نظيف (بدون محارف قد تسبب مشاكل في Windows).
        $branchName = trim((string) ($data['brochure']['name'] ?? $store->name));
        $cleanName = preg_replace('/[^\p{L}\p{N}\s\-_().]/u', '', $branchName) ?: 'Branch';
        $fileName = $cleanName.' Brochure.pdf';

        // 1) إذا كان هناك ملف PDF مرفوع فعلياً داخل brochure_path نحمّله مباشرة.
        $uploadedPath = (string) ($store->brochure_path ?? '');
        if ($uploadedPath !== '' && Storage::disk('public')->exists($uploadedPath)) {
            $localPath = storage_path('app/public/'.ltrim($uploadedPath, '/\\'));
            return $this->downloadResponse($localPath, $fileName);
        }

        // 2) إن لم يوجد ملف مرفوع، نبحث عن نسخة PDF مولدة/مخزنة مسبقاً اعتماداً على كود الفرع.
        $branchCode = trim((string) ($store->branch_code ?? ''));
        if ($branchCode !== '') {
            $cachedPath = storage_path('app/public/brochures/stores/branch-'.$branchCode.'.pdf');
            if (is_file($cachedPath)) {
                return $this->downloadResponse($cachedPath, $fileName);
            }
            $cachedPathLower = storage_path('app/public/brochures/stores/branch-'.strtolower($branchCode).'.pdf');
            if (is_file($cachedPathLower)) {
                return $this->downloadResponse($cachedPathLower, $fileName);
            }
            $cachedPathUpper = storage_path('app/public/brochures/stores/branch-'.strtoupper($branchCode).'.pdf');
            if (is_file($cachedPathUpper)) {
                return $this->downloadResponse($cachedPathUpper, $fileName);
            }
        }

        // 3) بحث مرن عن ملف PDF موجود محلياً بأسماء محتملة (اسم الفرع/المحافظة/المدينة).
        $nameCandidates = array_filter([
            $branchName,
            $store->name,
            $store->province?->name,
            $store->city,
        ], static fn ($value) => is_string($value) && trim($value) !== '');

        $localFile = $this->resolveLocalBrochurePath($nameCandidates, (string) $store->branch_code);

        // تجاهل ملفات placeholder (ملفات PDF فارغة/تجريبية) حتى لا نُرسل ملف غير مفيد.
        if ($localFile && $this->isPlaceholderPdf($localFile)) {
            $localFile = null;
        }

        // إذا وجدنا ملف محلي مناسب نحمّله مباشرة.
        if ($localFile) {
            return $this->downloadResponse($localFile, $fileName);
        }

        try {
            // 4) كحل أخير: توليد PDF ديناميكياً من HTML صفحة brochure.
            $html = view('stores.brochure', $data)->render();
            // تحديد مسار Chrome/Edge headless من البيئة أو مسارات معروفة.
            $chromePath = $this->resolveChromePath();
            if ($chromePath) {
                // تحويل HTML إلى PDF عبر Chrome Headless.
                $pdfPath = $this->renderPdfWithChrome($html, $chromePath);
                if ($pdfPath && is_file($pdfPath)) {
                    // حفظ النسخة المولدة داخل storage/public لسهولة التحميل لاحقاً.
                    $persistedPath = $this->persistGeneratedBrochure($pdfPath, $store, $branchName);
                    if ($persistedPath) {
                        File::delete($pdfPath);
                        return $this->downloadResponse($persistedPath, $fileName);
                    }

                    // إذا لم نستطع حفظها، نحمّل الملف المؤقت مباشرة.
                    return $this->downloadResponse($pdfPath, $fileName, true);
                }
            }
        } catch (\Throwable $e) {
            // تسجيل الخطأ وإرجاع رسالة لطيفة للمستخدم بدل شاشة خطأ.
            report($e);
            return back()->with('warning', 'Unable to generate the brochure PDF. Please try again.');
        }

        // fallback: إن فشل كل شيء نعيد تحذير.
        return back()->with('warning', 'Unable to generate the brochure PDF. Please try again.');
    }

    public function viewData(Store $store): array
    {
        // تحميل العلاقات اللازمة لعرض البرشور (المحافظة + المدير + الموظفين + المنتجات).
        $store->loadMissing(['province', 'manager', 'employees', 'products']);

        // اسم الفرع: نعتمد القيمة المخزّنة في قاعدة البيانات (هي مصدر الحقيقة بعد التعديل/الإضافة).
        // نستخدم branchDisplayName فقط لتطبيع أسماء عربية معروفة إلى إنجليزية، بدون فرض اسم ثابت حسب الكود.
        $storeName = \App\Support\EnglishPlaceNames::branchDisplayName($store->branch_code, $store->name) ?: $store->name;
        // اسم المحافظة بشكل إنجليزي إن كان لها code معروف، وإلا نستخدم الاسم المخزن.
        $province = $store->province?->code
            ? (\App\Support\EnglishPlaceNames::provinceByCode($store->province->code) ?: $store->province->name)
            : ($store->province?->name ?? 'Not specified');

        // بيانات المدير (قد تكون غير موجودة إذا لم يتم تعيين مدير بعد).
        $manager = $store->manager;
        $managerEmail = $manager ? UserContact::email($manager->email, $manager->name, (int) $manager->id) : 'Not available';
        $managerPhone = $manager ? UserContact::phone($manager->phone) : 'Not available';

        // ساعات الدوام للعرض في البرشور.
        $hours = '-';
        if ($store->workday_starts_at && $store->workday_ends_at) {
            // تحويل وقت 24 ساعة إلى 12 ساعة (AM/PM) مع fallback إذا فشل التحويل.
            $formatTo12h = static function ($value): ?string {
                $value = $value ? \Illuminate\Support\Str::of($value)->substr(0, 5) : null;
                if (! $value) {
                    return null;
                }
                try {
                    return \Carbon\Carbon::createFromFormat('H:i', (string) $value)->format('g:i A');
                } catch (\Throwable) {
                    return (string) $value;
                }
            };

            // تجهيز النص النهائي لساعات الدوام.
            $startLabel = $formatTo12h($store->workday_starts_at) ?: substr((string) $store->workday_starts_at, 0, 5);
            $endLabel = $formatTo12h($store->workday_ends_at) ?: substr((string) $store->workday_ends_at, 0, 5);
            $hours = $startLabel.' - '.$endLabel;
        } elseif ($store->working_hours) {
            // إذا لم تتوفر حقول الوقت نستخدم النص المخزن إن وجد.
            $hours = (string) $store->working_hours;
        }

        // البيانات التي ستستخدمها صفحة stores.brochure لعرض التصميم.
        return [
            'store' => $store,
            'brochure' => [
                'name' => (string) $storeName,
                'code' => (string) $store->branch_code,
                'address' => (string) ($store->address ?? ''),
                'province' => (string) $province,
                'employees' => (int) $store->employees->count(),
                'hours' => (string) $hours,
                'description' => (string) ($store->description ?: 'This branch is part of the Store Branch Management System.'),
                'manager' => (string) ($manager?->name ?? '-'),
                'managerEmail' => (string) $managerEmail,
                'managerPhone' => (string) $managerPhone,
            ],
        ];
    }

    private function resolveChromePath(): ?string
    {
        // أولوية 1: مسار Chrome محدد عبر env (يفيد في السيرفرات).
        $envPath = trim((string) env('CHROME_PATH', ''));
        if ($envPath !== '' && is_file($envPath)) {
            return $envPath;
        }

        // أولوية 2: مسارات افتراضية معروفة على Windows/Linux/Mac.
        $candidates = [
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            '/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge',
        ];

        // اختيار أول مسار موجود فعلاً.
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        // إذا لم نجد Chrome/Edge نرجع null (وبالتالي لن نستطيع توليد PDF).
        return null;
    }

    private function resolveLocalBrochurePath(array $nameCandidates, string $branchCode): ?string
    {
        // تنظيف branchCode من المسافات.
        $branchCode = trim($branchCode);

        // تنظيف أسماء محتملة (إزالة مسافات زائدة) لاستخدامها في بناء أسماء ملفات محتملة.
        $cleanNames = [];
        foreach ($nameCandidates as $name) {
            $name = trim(preg_replace('/\s+/', ' ', (string) $name));
            if ($name !== '') {
                $cleanNames[] = $name;
            }
        }

        // بناء قائمة أسماء ملفات محتملة اعتماداً على أسماء الفرع/Slug/الكود.
        $candidates = [];
        foreach ($cleanNames as $name) {
            $candidates[] = $name.' Brochure.pdf';
            $candidates[] = $name.'.pdf';
            $candidates[] = Str::slug($name).'.pdf';
            $candidates[] = Str::slug($name).'-brochure.pdf';
        }
        if ($branchCode !== '') {
            $candidates[] = $branchCode.' Brochure.pdf';
            $candidates[] = $branchCode.'.pdf';
            $candidates[] = 'branch-'.$branchCode.'.pdf';
            $candidates[] = 'branch-'.Str::upper($branchCode).'.pdf';
            $candidates[] = 'branch-'.Str::lower($branchCode).'.pdf';
            $candidates[] = 'branch_'.$branchCode.'.pdf';
        }

        // مجلدات محتملة للبحث عن ملفات البرشور.
        $searchDirs = [
            base_path(),
            storage_path('app'),
            storage_path('app/public'),
            storage_path('app/public/brochures'),
            storage_path('app/public/brochures/stores'),
            storage_path('app/brochures'),
            storage_path('app/brochures/branches'),
            public_path('brochures'),
            public_path('storage/brochures'),
            public_path('storage/brochures/stores'),
        ];

        // بحث مباشر (exact match) داخل المجلدات المحددة.
        foreach ($searchDirs as $dir) {
            foreach ($candidates as $candidate) {
                $path = $dir.DIRECTORY_SEPARATOR.$candidate;
                if (is_file($path)) {
                    return $path;
                }
            }
        }

        // إذا لم نجد تطابق مباشر، نجمع كل ملفات PDF من مجلدات "مرنة" ونحاول مطابقة fuzzy.
        $allPdfs = [];
        $fuzzyDirs = array_filter([
            storage_path('app/public/brochures'),
            storage_path('app/public/brochures/stores'),
            storage_path('app/brochures'),
            storage_path('app/brochures/branches'),
            public_path('brochures'),
            public_path('storage/brochures'),
            public_path('storage/brochures/stores'),
        ], static fn ($dir) => is_dir($dir));

        // جمع كل ملفات PDF داخل هذه المجلدات.
        foreach ($fuzzyDirs as $dir) {
            foreach (File::allFiles($dir) as $file) {
                if ($file->getExtension() === 'pdf') {
                    $allPdfs[] = $file->getPathname();
                }
            }
        }

        if ($allPdfs === []) {
            return null;
        }

        // تجهيز مفاتيح بحث مطبّعة (بدون مسافات/رموز) لزيادة احتمال التطابق.
        $needleNames = array_filter(array_map([$this, 'normalizeFileKey'], $cleanNames));
        $needleCode = $this->normalizeFileKey($branchCode);

        // مطابقة fuzzy: نبحث إن كان اسم الملف يحتوي اسم الفرع/الكود بعد التطبيع.
        foreach ($allPdfs as $path) {
            $key = $this->normalizeFileKey(pathinfo($path, PATHINFO_FILENAME));
            foreach ($needleNames as $needleName) {
                if ($needleName !== '' && str_contains($key, $needleName)) {
                    return $path;
                }
            }
            if ($needleCode !== '' && str_contains($key, $needleCode)) {
                return $path;
            }
        }

        // لا يوجد أي تطابق.
        return null;
    }

    private function isPlaceholderPdf(string $path): bool
    {
        // ملف غير موجود -> ليس placeholder.
        if (! is_file($path)) {
            return false;
        }

        // حجم الملف: الملفات الصغيرة جداً غالباً تكون "فارغة/Placeholder".
        $size = @filesize($path);
        if ($size === false) {
            return false;
        }

        return $size > 0 && $size < 20000;
    }

    private function persistGeneratedBrochure(string $pdfPath, Store $store, string $branchName): ?string
    {
        // نتأكد أن ملف الـ PDF المؤقت موجود.
        if (! is_file($pdfPath)) {
            return null;
        }

        // مسار التخزين الدائم للبرشورات المولدة.
        $dir = storage_path('app/public/brochures/stores');
        File::ensureDirectoryExists($dir);

        // بناء اسم ملف يعتمد على branch_code إن توفر، وإلا نعتمد على slug للاسم.
        $code = trim((string) ($store->branch_code ?? ''));
        $baseName = $code !== '' ? 'branch-'.$code : (Str::slug($branchName) ?: 'branch');
        $target = $dir.DIRECTORY_SEPARATOR.$baseName.'.pdf';

        // نسخ الملف المؤقت إلى مسار دائم وإرجاع المسار عند النجاح.
        return @copy($pdfPath, $target) ? $target : null;
    }

    private function normalizeFileKey(string $value): string
    {
        // تطبيع النص للمقارنة: lowercase + إزالة أي رموز غير حروف/أرقام.
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^\p{L}\p{N}]+/u', '', $value) ?? '';

        return $value;
    }

    private function renderPdfWithChrome(string $html, string $chromePath): ?string
    {
        // مجلد مؤقت لتخزين HTML و PDF وملف بروفايل Chrome أثناء التوليد.
        $tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'store-brochure';
        File::ensureDirectoryExists($tempDir);

        // token فريد لتجنب تعارض ملفات التوليد بين أكثر من طلب.
        $token = Str::random(12);
        $htmlPath = $tempDir.DIRECTORY_SEPARATOR.'brochure-'.$token.'.html';
        $pdfPath = $tempDir.DIRECTORY_SEPARATOR.'brochure-'.$token.'.pdf';
        $profileDir = $tempDir.DIRECTORY_SEPARATOR.'chrome-profile-'.$token;

        // كتابة ملف HTML المؤقت وإنشاء مجلد بروفايل Chrome.
        File::put($htmlPath, $html);
        File::ensureDirectoryExists($profileDir);

        // Chrome headless يحتاج رابط file:// للوصول لملف HTML.
        $fileUrl = $this->toFileUrl($htmlPath);
        $profileDirArg = str_replace('\\', '/', $profileDir);
        // إعداد عملية Chrome headless لطباعة الصفحة كـ PDF.
        $process = new Process([
            $chromePath,
            '--headless',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-extensions',
            '--no-first-run',
            '--no-default-browser-check',
            '--disable-features=Crashpad',
            '--no-crash-upload',
            '--disable-crash-reporter',
            '--allow-file-access-from-files',
            '--user-data-dir='.$profileDirArg,
            '--virtual-time-budget=10000',
            '--print-to-pdf-no-header',
            '--print-to-pdf='.$pdfPath,
            $fileUrl,
        ]);
        // مهلة التنفيذ (ثواني) لتجنب تعليق الطلب في حال Chrome لم يستجب.
        $process->setTimeout(120);
        $process->run();

        // حذف HTML المؤقت (بقي PDF فقط إن نجح التوليد).
        File::delete($htmlPath);

        if (is_file($pdfPath)) {
            // نجاح: تنظيف بروفايل Chrome وإرجاع مسار PDF.
            File::deleteDirectory($profileDir);
            return $pdfPath;
        }

        // فشل: تنظيف بروفايل Chrome، تسجيل الخطأ، ثم حذف أي أثر لملف PDF.
        File::deleteDirectory($profileDir);
        report(new \RuntimeException('Chrome PDF failed: '.$process->getErrorOutput()));
        File::delete($pdfPath);
        return null;
    }

    private function toFileUrl(string $path): string
    {
        // تحويل مسار Windows إلى صيغة URL صالحة لـ Chrome (file://).
        $path = str_replace('\\', '/', $path);
        $path = str_replace(' ', '%20', $path);
        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        return 'file://'.$path;
    }

    private function downloadResponse(string $path, string $fileName, bool $deleteAfterSend = false): BinaryFileResponse
    {
        // Create a download response for the PDF file.
        $response = response()->download($path, $fileName);
        // اسم احتياطي ASCII لتفادي مشاكل أسماء الملفات غير اللاتينية في بعض المتصفحات.
        $fallbackName = Str::ascii($fileName) ?: 'brochure.pdf';
        // تهيئة Content-Disposition كـ Attachment لإجبار التحميل.
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName, $fallbackName);
        // تعيين نوع المحتوى PDF.
        $response->headers->set('Content-Type', 'application/pdf');
        // منع كاش المتصفح حتى لا يعرض نسخة قديمة عند استبدال البروشور بملف جديد.
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        if ($deleteAfterSend) {
            // عند تحميل ملف مؤقت (مولد الآن) نحذفه بعد الإرسال لتقليل تراكم الملفات.
            $response->deleteFileAfterSend(true);
        }

        return $response;
    }
}