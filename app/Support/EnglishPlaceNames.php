<?php // Name : Rodain Gouzlan Id:

namespace App\Support;

/**
 * Helper: أسماء المحافظات والأفرع باللغة الإنجليزية (Mapping)
 *
 * الهدف:
 * - المشروع يتطلب أن تكون بيانات الأفرع باللغة الإنجليزية.
 * - أحياناً قد تدخل بيانات بالعربية أو بكود فقط، لذلك نوفر Mapping واضح للأسماء والعناوين.
 *
 * أين يُستخدم:
 * - في صفحات عرض الأفرع (Grid/Details)
 * - في خدمة البرشور (Brochure) لتجهيز نصوص العرض
 * - في تطبيع المدخلات (EnglishInputNormalizer) عند تحويل أسماء عربية معروفة إلى إنجليزية
 */
class EnglishPlaceNames
{
    /**
     * إرجاع اسم المحافظة باللغة الإنجليزية اعتماداً على كود المحافظة.
     */
    public static function provinceByCode(?string $code): string
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return '';
        }

        return match ($code) {
            'DAM' => 'Damascus',
            'RDM' => 'Rural Damascus',
            'QUN' => 'Quneitra',
            'DAR' => 'Daraa',
            'SWD' => 'As-Suwayda',
            'HMS' => 'Homs',
            'HMA' => 'Hama',
            'TAR' => 'Tartus',
            'LAT' => 'Latakia',
            'IDL' => 'Idlib',
            'ALP' => 'Aleppo',
            'RAQ' => 'Raqqa',
            'DEZ' => 'Deir ez-Zor',
            'HSK' => 'Al-Hasakah',
            default => '',
        };
    }

    /**
     * اسم بديل للحفاظ على التوافق مع استدعاءات قديمة (alias لـ provinceByCode).
     */
    public static function province(?string $code): string
    {
        return self::provinceByCode($code);
    }

    /**
     * إرجاع اسم الفرع باللغة الإنجليزية اعتماداً على كود الفرع.
     */
    public static function branchByCode(?string $branchCode): string
    {
        $branchCode = strtoupper(trim((string) $branchCode));
        if ($branchCode === '') {
            return '';
        }

        return match ($branchCode) {
            'DAM-001' => 'Damascus Branch (Main)',
            'HMS-002' => 'Homs Branch',
            'ALP-003' => 'Aleppo Branch',
            'LAT-004' => 'Latakia Branch',
            'SWD-005' => 'As-Suwayda Branch',
            'HMA-006' => 'Hama Branch',
            default => '',
        };
    }

    /**
     * إرجاع أفضل اسم عرض (Display Name) للفرع اعتماداً على كود الفرع أو قيمة fallback.
     */
    public static function branchDisplayName(?string $branchCode, ?string $fallback = null): string
    {
        $branchCode = strtoupper(trim((string) $branchCode));
        $fallback = trim((string) $fallback);

        if ($fallback !== '') {
            $byArabic = self::branchNameByArabic($fallback);
            if ($byArabic !== '') {
                return $byArabic;
            }

            return $fallback;
        }

        if ($branchCode !== '') {
            $byCode = self::branchByCode($branchCode);
            if ($byCode !== '') {
                return $byCode;
            }

            $prefix = explode('-', $branchCode, 2)[0] ?? '';
            $provinceName = self::provinceByCode($prefix);
            if ($provinceName !== '') {
                return $provinceName.' Branch';
            }
        }

        return '';
    }

    /**
     * ترجمة أسماء أفرع عربية معروفة إلى الإنجليزية (لتوحيد العرض داخل النظام).
     */
    public static function branchNameByArabic(?string $branchName): string
    {
        $branchName = trim((string) $branchName);
        if ($branchName === '') {
            return '';
        }

        $normalized = preg_replace('/\\s+/u', ' ', $branchName);
        $normalized = str_replace([' (', '( ', ' )', ') '], ['(', '(', ')', ')'], $normalized);

        $damascus = "\u{062F}\u{0645}\u{0634}\u{0642}";
        $main = "\u{0627}\u{0644}\u{0631}\u{0626}\u{064A}\u{0633}\u{064A}";
        $homs = "\u{062D}\u{0645}\u{0635}";
        $aleppo = "\u{062D}\u{0644}\u{0628}";
        $latakia = "\u{0627}\u{0644}\u{0644}\u{0627}\u{0630}\u{0642}\u{064A}\u{0629}";
        $suwayda = "\u{0627}\u{0644}\u{0633}\u{0648}\u{064A}\u{062F}\u{0627}\u{0621}";
        $hama = "\u{062D}\u{0645}\u{0627}\u{0629}";

        if (str_contains($normalized, $damascus)) {
            return str_contains($normalized, $main)
                ? 'Damascus Branch (Main)'
                : 'Damascus Branch';
        }

        if (str_contains($normalized, $homs)) {
            return 'Homs Branch';
        }

        if (str_contains($normalized, $aleppo)) {
            return 'Aleppo Branch';
        }

        if (str_contains($normalized, $latakia)) {
            return 'Latakia Branch';
        }

        if (str_contains($normalized, $suwayda)) {
            return 'As-Suwayda Branch';
        }

        if (str_contains($normalized, $hama)) {
            return 'Hama Branch';
        }

        return '';
    }

    /**
     * إرجاع عنوان تفصيلي باللغة الإنجليزية اعتماداً على كود الفرع (في حال كان معروفاً).
     */
    public static function branchAddressByCode(?string $branchCode): string
    {
        $branchCode = strtoupper(trim((string) $branchCode));
        if ($branchCode === '') {
            return '';
        }

        return match ($branchCode) {
            'DAM-001' => 'Al-Maliki - Al-Jalaa Street',
            'HMS-002' => "Al-Wa'ar District - Al-Hadara Street",
            'ALP-003' => 'Al-Jamiliya - Baron Street',
            'LAT-004' => 'Al-Saliba - Baghdad Street',
            'SWD-005' => 'Al-Qurayya Road - Al-Kurnish Street',
            'HMA-006' => 'Al-Hader - Al-Murabit Street',
            default => '',
        };
    }

    /**
     * إرجاع ساعات الدوام الافتراضية (بداية/نهاية) اعتماداً على كود الفرع (في حال كان معروفاً).
     *
     * @return array{start: string, end: string}
     */
    public static function branchWorkHoursByCode(?string $branchCode): array
    {
        $branchCode = strtoupper(trim((string) $branchCode));
        if ($branchCode === '') {
            return ['start' => '', 'end' => ''];
        }

        return match ($branchCode) {
            'DAM-001' => ['start' => '09:00', 'end' => '17:00'],
            'HMS-002' => ['start' => '09:00', 'end' => '17:00'],
            'ALP-003' => ['start' => '09:00', 'end' => '17:00'],
            'LAT-004' => ['start' => '09:00', 'end' => '17:00'],
            'SWD-005' => ['start' => '08:30', 'end' => '16:30'],
            'HMA-006' => ['start' => '09:30', 'end' => '18:00'],
            default => ['start' => '', 'end' => ''],
        };
    }
}