<?php // Name : Rodain Gouzlan Id:

namespace App\Services\Store;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * خدمة رفع بروشور الفرع بطريقة مجزأة (Chunked Upload).
 *
 * لماذا نحتاجها:
 * - الرفع التقليدي قد يفشل بسبب حدود `upload_max_filesize` و`post_max_size` (شائع في Windows/XAMPP).
 * - تقسيم الملف إلى أجزاء صغيرة يجعل كل طلب ضمن الحدود.
 * - بعد استلام آخر جزء، نجمع الملف ونحفظه تحت `storage/app/public/...`
 *   ثم نُرجع `brochure_path` ليُخزَّن في جدول `stores`.
 */
class StoreBrochureUploadService
{
    // التأكد أن تنظيف الملفات المؤقتة يتم مرة واحدة في دورة التنفيذ.
    private static bool $cleanupRan = false;

    /**
     * @return array{complete: bool, received: int, total: int, brochure_path?: string}
     */
    public function uploadChunk(
        string $uploadId,
        int $chunkIndex,
        int $totalChunks,
        string $originalFileName,
        UploadedFile $chunk
    ): array {
        $uploadId = trim($uploadId);
        $originalFileName = trim($originalFileName);

        $baseDir = storage_path('app/tmp/brochure_uploads');
        File::ensureDirectoryExists($baseDir);
        if (! self::$cleanupRan) {
            $this->cleanupStaleUploads($baseDir);
            self::$cleanupRan = true;
        }

        $partPath = $baseDir.DIRECTORY_SEPARATOR.$uploadId.'.part';
        $metaPath = $baseDir.DIRECTORY_SEPARATOR.$uploadId.'.json';

        $meta = $this->loadMeta($metaPath);
        if ($meta === null || (int) ($meta['total'] ?? 0) !== $totalChunks) {
            // بدء رفع جديد (أو إعادة ضبط إذا أعاد العميل نفس upload_id).
            $meta = [
                'received' => 0,
                'total' => $totalChunks,
                'file_name' => $originalFileName,
                'started_at' => time(),
            ];

            // عند إعادة الضبط نحذف الملف الجزئي السابق.
            if (is_file($partPath)) {
                @unlink($partPath);
            }
        }

        $received = (int) ($meta['received'] ?? 0);

        // نفرض الترتيب التسلسلي للقطع حتى يبقى المنطق بسيطاً وآمناً على الذاكرة.
        // يجب أن يرسل العميل القطعة 0 ثم 1 ثم 2 ...
        if ($chunkIndex < $received) {
            // قطعة مكررة: نعيد التقدم الحالي بدون خطأ.
            return [
                'complete' => false,
                'received' => $received,
                'total' => $totalChunks,
            ];
        }

        if ($chunkIndex !== $received) {
            abort(409, 'Chunk out of order. Please retry the upload.');
        }

        $writeMode = $chunkIndex === 0 ? 'wb' : 'ab';
        $dest = @fopen($partPath, $writeMode);
        if (! is_resource($dest)) {
            abort(500, 'Unable to write upload data.');
        }

        $src = @fopen($chunk->getRealPath(), 'rb');
        if (! is_resource($src)) {
            @fclose($dest);
            abort(500, 'Unable to read uploaded chunk.');
        }

        stream_copy_to_stream($src, $dest);
        fclose($src);
        fclose($dest);

        $received++;
        $meta['received'] = $received;
        $this->saveMeta($metaPath, $meta);

        // لم يكتمل التجميع بعد.
        if ($received < $totalChunks) {
            return [
                'complete' => false,
                'received' => $received,
                'total' => $totalChunks,
            ];
        }

        // اكتمل التجميع: ننقل ملف الـ PDF النهائي إلى قرص public.
        $safeName = $this->sanitizePdfFileName($meta['file_name'] ?? $originalFileName);
        $finalRelPath = 'brochures/stores/'.$uploadId.'-'.$safeName;
        $finalFullPath = Storage::disk('public')->path($finalRelPath);
        File::ensureDirectoryExists(dirname($finalFullPath));

        // فحص بسيط لتوقيع PDF قبل حفظه كبروشور.
        $signature = $this->readSignature($partPath);
        if ($signature !== '%PDF-') {
            @unlink($partPath);
            @unlink($metaPath);
            abort(422, 'The uploaded file is not a valid PDF.');
        }

        // نقل الملف إلى موقعه النهائي.
        File::move($partPath, $finalFullPath);
        @unlink($metaPath);

        return [
            'complete' => true,
            'received' => $received,
            'total' => $totalChunks,
            'brochure_path' => $finalRelPath,
        ];
    }

    private function loadMeta(string $metaPath): ?array
    {
        if (! is_file($metaPath)) {
            return null;
        }

        try {
            $data = json_decode((string) file_get_contents($metaPath), true, 512, JSON_THROW_ON_ERROR);
            return is_array($data) ? $data : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function saveMeta(string $metaPath, array $meta): void
    {
        // محاولة حفظ؛ إذا فشل، ستُعاد عملية الرفع تلقائياً مع القطعة التالية.
        @file_put_contents($metaPath, json_encode($meta), LOCK_EX);
    }

    private function readSignature(string $path): string
    {
        $fh = @fopen($path, 'rb');
        if (! is_resource($fh)) {
            return '';
        }

        $bytes = (string) fread($fh, 5);
        fclose($fh);

        return $bytes;
    }

    private function sanitizePdfFileName(string $name): string
    {
        $name = trim($name);
        $name = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $name);
        $name = basename($name);

        if ($name === '') {
            $name = 'brochure.pdf';
        }

        // ضمان امتداد .pdf.
        if (! preg_match('/\\.pdf$/i', $name)) {
            $name .= '.pdf';
        }

        // الحفاظ على مجموعة محارف آمنة لأنظمة Windows/Linux/macOS.
        $name = preg_replace('/[^A-Za-z0-9 _\\-\\.()]/', '', $name) ?: 'brochure.pdf';
        $name = preg_replace('/\\s+/', ' ', $name);
        $name = trim($name);

        // تجنب الأسماء الطويلة جداً.
        if (strlen($name) > 120) {
            $ext = '.pdf';
            $base = substr($name, 0, 120 - strlen($ext));
            $name = rtrim($base, " .-_").$ext;
        }

        return $name;
    }

    private function cleanupStaleUploads(string $baseDir, int $maxAgeSeconds = 259200): void
    {
        if (! is_dir($baseDir)) {
            return;
        }

        $now = time();

        foreach (glob($baseDir.DIRECTORY_SEPARATOR.'*.json') as $metaPath) {
            $uploadId = basename((string) $metaPath, '.json');
            if ($uploadId === '') {
                continue;
            }

            $partPath = $baseDir.DIRECTORY_SEPARATOR.$uploadId.'.part';
            $metaMtime = @filemtime($metaPath) ?: 0;
            $partMtime = is_file($partPath) ? (@filemtime($partPath) ?: 0) : 0;
            $latest = max($metaMtime, $partMtime);

            if ($latest > 0 && ($now - $latest) > $maxAgeSeconds) {
                @unlink($metaPath);
                if (is_file($partPath)) {
                    @unlink($partPath);
                }
            }
        }

        foreach (glob($baseDir.DIRECTORY_SEPARATOR.'*.part') as $partPath) {
            $uploadId = basename((string) $partPath, '.part');
            if ($uploadId === '') {
                continue;
            }

            $metaPath = $baseDir.DIRECTORY_SEPARATOR.$uploadId.'.json';
            if (is_file($metaPath)) {
                continue;
            }

            $partMtime = @filemtime($partPath) ?: 0;
            if ($partMtime > 0 && ($now - $partMtime) > $maxAgeSeconds) {
                @unlink($partPath);
            }
        }
    }
}

// Summary: خدمة تجزئة رفع البروشور ثم تجميعه وتحفظه بأمان مع تنظيف الملفات المؤقتة.