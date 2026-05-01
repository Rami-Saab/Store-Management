<?php // Name : Rodain Gouzlan Id:

namespace App\Support;

/**
 * Helper: تطبيع المدخلات الإنجليزية (Input Normalization)
 *
 * الهدف:
 * - كثير من المستخدمين ينسخون نصوصاً تحتوي على رموز ومسافات "غير مرئية" أو علامات ترقيم عربية.
 * - هذا يؤدي لفشل regex أو لتخزين بيانات غير متجانسة.
 * - لذلك نقوم بتحويل هذه الرموز إلى بدائل إنجليزية/قياسية قدر الإمكان قبل التحقق والحفظ.
 *
 * مثال:
 * - تحويل الأرقام العربية إلى أرقام لاتينية.
 * - تحويل الفاصلة العربية إلى فاصلة إنجليزية.
 */
class EnglishInputNormalizer
{
    /**
     * تطبيع النص المدخل لتفادي مشاكل المسافات وعلامات الترقيم غير القياسية (غير ASCII).
     */
    public static function normalize(?string $value): string
    {
        // إذا كانت القيمة null نعيد نص فارغ لتفادي أخطاء لاحقة.
        if ($value === null) {
            return '';
        }

        // تحويل القيمة إلى string (حتى لو كانت نوع آخر قابل للتحويل).
        $text = (string) $value;

        // استبدال رموز Unicode "غير المرئية" أو علامات الترقيم العربية بما يقابلها ASCII قدر الإمكان.
        // الهدف: منع فشل regex وتوحيد البيانات المخزنة.
        $text = strtr($text, [
            "\u{00A0}" => ' ',
            "\u{2007}" => ' ',
            "\u{202F}" => ' ',
            "\u{2009}" => ' ',
            "\u{200A}" => ' ',
            "\u{200B}" => '',
            "\u{200C}" => '',
            "\u{200D}" => '',
            "\u{FEFF}" => '',
            "\u{2013}" => '-',
            "\u{2014}" => '-',
            "\u{2212}" => '-',
            "\u{2018}" => "'",
            "\u{2019}" => "'",
            "\u{201C}" => '"',
            "\u{201D}" => '"',
            "\u{060C}" => ',',
            "\u{061B}" => ';',
            "\u{061F}" => '?',
            "\u{066A}" => '%',
            "\u{066B}" => '.',
            "\u{066C}" => ',',
            "\u{2026}" => '...',
        ]);

        // تحويل الأرقام العربية/الفارسية إلى أرقام لاتينية.
        $text = self::replaceArabicDigits($text);

        // إزالة المسافات الزائدة في البداية والنهاية.
        return trim($text);
    }

    public static function normalizeCity(?string $value): string
    {
        // أولاً نطبع النص بشكل عام.
        $text = self::normalize($value);

        // خريطة تحويل أسماء مدن عربية شائعة إلى أسماء إنجليزية موحدة.
        $arabicMap = [
            "\u{062F}\u{0645}\u{0634}\u{0642}" => 'Damascus',
            "\u{0631}\u{064A}\u{0641} \u{062F}\u{0645}\u{0634}\u{0642}" => 'Rural Damascus',
            "\u{062D}\u{0644}\u{0628}" => 'Aleppo',
            "\u{062D}\u{0645}\u{0635}" => 'Homs',
            "\u{062D}\u{0645}\u{0627}\u{0629}" => 'Hama',
            "\u{0627}\u{0644}\u{0644}\u{0627}\u{0630}\u{0642}\u{064A}\u{0629}" => 'Latakia',
            "\u{0627}\u{0644}\u{0633}\u{0648}\u{064A}\u{062F}\u{0627}\u{0621}" => 'As-Suwayda',
            "\u{0637}\u{0631}\u{0637}\u{0648}\u{0633}" => 'Tartus',
            "\u{062F}\u{0631}\u{0639}\u{0627}" => 'Daraa',
            "\u{0627}\u{0644}\u{0642}\u{0646}\u{064A}\u{0637}\u{0631}\u{0629}" => 'Quneitra',
            "\u{0627}\u{0644}\u{0631}\u{0642}\u{0629}" => 'Raqqa',
            "\u{062F}\u{064A}\u{0631} \u{0627}\u{0644}\u{0632}\u{0648}\u{0631}" => 'Deir ez-Zor',
            "\u{0627}\u{0644}\u{062D}\u{0633}\u{0643}\u{0629}" => 'Al-Hasakah',
            "\u{0627}\u{062F}\u{0644}\u{0628}" => 'Idlib',
            "\u{0625}\u{062F}\u{0644}\u{0628}" => 'Idlib',
        ];

        // إذا احتوى النص على أحد الأسماء العربية نعيد المقابل الإنجليزي.
        foreach ($arabicMap as $arabic => $english) {
            if (str_contains($text, $arabic)) {
                return $english;
            }
        }

        // fallback: إن لم يوجد تطابق نعيد النص كما هو بعد التطبيع.
        return $text;
    }

    public static function normalizeBranchName(?string $value): string
    {
        // تطبيع الاسم ثم محاولة تحويل الاسم العربي إلى اسم إنجليزي موحد إن كان معروفاً.
        $text = self::normalize($value);
        $byArabic = EnglishPlaceNames::branchNameByArabic($text);

        return $byArabic !== '' ? $byArabic : $text;
    }

    public static function normalizeBranchCode(?string $value): string
    {
        // توحيد الكود إلى uppercase ثم استبدال المسافات/underscore بشرطة.
        $text = strtoupper(self::normalize($value));
        $text = str_replace([' ', '_'], '-', $text);
        // دمج الشرطات المتتالية إلى شرطة واحدة.
        $text = preg_replace('/-+/', '-', $text) ?? $text;

        return $text;
    }

    public static function normalizePhone(?string $value): string
    {
        // نطبع النص ثم نحذف بعض الرموز الشائعة في كتابة الهاتف.
        $text = self::normalize($value);
        $text = str_replace([' ', '-', '(', ')'], '', $text);

        return $text;
    }

    /**
     * تطبيع بيانات الفرع (Store payload) قبل التحقق (Validation).
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public static function normalizeStorePayload(array $payload): array
    {
        // مصفوفة تحتوي القيم المطبّعة فقط (سنقوم بدمجها فوق payload الأصلي).
        $normalized = [];

        // تطبيع اسم الفرع (يدعم تحويل أسماء عربية معروفة إلى إنجليزي).
        if (array_key_exists('name', $payload)) {
            $normalized['name'] = self::normalizeBranchName(is_string($payload['name']) ? $payload['name'] : null);
        }
        // تطبيع كود الفرع.
        if (array_key_exists('branch_code', $payload)) {
            $normalized['branch_code'] = self::normalizeBranchCode(is_string($payload['branch_code']) ? $payload['branch_code'] : null);
        }
        // تطبيع المدينة.
        if (array_key_exists('city', $payload)) {
            $normalized['city'] = self::normalizeCity(is_string($payload['city']) ? $payload['city'] : null);
        }
        // تطبيع العنوان.
        if (array_key_exists('address', $payload)) {
            $normalized['address'] = self::normalize(is_string($payload['address']) ? $payload['address'] : null);
        }
        // تطبيع الوصف.
        if (array_key_exists('description', $payload)) {
            $normalized['description'] = self::normalize(is_string($payload['description']) ? $payload['description'] : null);
        }
        // تطبيع الهاتف (إزالة رموز).
        if (array_key_exists('phone', $payload)) {
            $normalized['phone'] = self::normalizePhone(is_string($payload['phone']) ? $payload['phone'] : null);
        }
        // البريد: نكتفي بعمل trim هنا (التحقق الأساسي يحدث في validation).
        if (array_key_exists('email', $payload)) {
            $normalized['email'] = trim((string) $payload['email']);
        }

        // دمج القيم المطبّعة فوق payload الأصلي مع الحفاظ على المفاتيح الأخرى كما هي.
        return array_merge($payload, $normalized);
    }

    private static function replaceArabicDigits(string $text): string
    {
        // استبدال الأرقام العربية (٠١٢٣...) والفارسية (۰۱۲۳...) بما يقابلها 0-9.
        return strtr($text, [
            "\u{0660}" => '0', "\u{0661}" => '1', "\u{0662}" => '2', "\u{0663}" => '3', "\u{0664}" => '4',
            "\u{0665}" => '5', "\u{0666}" => '6', "\u{0667}" => '7', "\u{0668}" => '8', "\u{0669}" => '9',
            "\u{06F0}" => '0', "\u{06F1}" => '1', "\u{06F2}" => '2', "\u{06F3}" => '3', "\u{06F4}" => '4',
            "\u{06F5}" => '5', "\u{06F6}" => '6', "\u{06F7}" => '7', "\u{06F8}" => '8', "\u{06F9}" => '9',
        ]);
    }
}