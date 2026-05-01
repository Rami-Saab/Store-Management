<?php // Name : Rodain Gouzlan Id:

namespace App\Support;

/**
 * Helper: ثيم/ألوان بطاقة الفرع
 *
 * الهدف:
 * - لإظهار بطاقات الأفرع بألوان مختلفة بشكل متناسق داخل واجهة النظام (Grid Cards).
 * - اختيار اللون يتم اعتماداً على prefix من كود الفرع (مثل DAM, ALP...) أو عبر hash ثابت في حال كود غير معروف.
 */
class BranchTheme
{
    /**
     * إنشاء ثيم ثابت (ألوان + تدرج) اعتماداً على كود الفرع.
     *
     * @return array{from: string, to: string, gradient: string, tailwind: string}
     */
    public static function themeForBranchCode(?string $branchCode, ?string $fallbackKey = null): array
    {
        $branchCode = strtoupper(trim((string) $branchCode));
        $prefix = $branchCode !== '' ? (explode('-', $branchCode, 2)[0] ?? $branchCode) : '';

        $known = [
            'DAM' => ['from' => '#3b82f6', 'to' => '#9333ea'],
            'ALP' => ['from' => '#6366f1', 'to' => '#2563eb'],
            'HMS' => ['from' => '#a855f7', 'to' => '#db2777'],
            'LAT' => ['from' => '#06b6d4', 'to' => '#2563eb'],
            'SWD' => ['from' => '#14b8a6', 'to' => '#059669'],
            'HMA' => ['from' => '#f97316', 'to' => '#dc2626'],
        ];

        if ($prefix !== '' && isset($known[$prefix])) {
            $colors = $known[$prefix];
        } else {
            $hashSource = $branchCode !== '' ? $branchCode : (string) $fallbackKey;
            $hashHex = $hashSource !== '' ? substr(sha1($hashSource), 0, 8) : '00000000';
            $hash = hexdec($hashHex);

            $hue = fmod(($hash * 137.508), 360.0);
            $knownHues = array_map(
                fn (array $item) => self::hexToHsl($item['from'] ?? '#2563eb')['h'],
                array_values($known)
            );
            $hue = self::shiftHueAway($hue, $knownHues, 26);

            $sat = 68 + (($hash >> 8) % 12);     // 68 - 79
            $light = 46 + (($hash >> 16) % 10);  // 46 - 55
            $toLight = max(34, $light - 12);

            $from = self::hslToHex($hue, $sat, $light);
            $to = self::hslToHex(fmod($hue + 24, 360.0), max(62, $sat - 6), $toLight);
            $colors = ['from' => $from, 'to' => $to];
        }

        $from = self::normalizeHex($colors['from'] ?? '#2563eb');
        $to = self::normalizeHex($colors['to'] ?? '#4f46e5');

        return [
            'from' => $from,
            'to' => $to,
            'gradient' => 'linear-gradient(90deg, '.$from.', '.$to.')',
            'tailwind' => 'from-['.$from.'] to-['.$to.']',
        ];
    }

    private static function normalizeHex(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '#2563eb';
        }

        return str_starts_with($value, '#') ? $value : '#'.$value;
    }

    /**
     * @return array{h: float, s: float, l: float}
     */
    private static function hexToHsl(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = 0.0;
        $s = 0.0;
        $l = ($max + $min) / 2;

        if ($max !== $min) {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r:
                    $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = ($b - $r) / $d + 2;
                    break;
                case $b:
                    $h = ($r - $g) / $d + 4;
                    break;
            }
            $h *= 60;
        }

        return ['h' => $h, 's' => $s * 100, 'l' => $l * 100];
    }

    private static function hslToHex(float $h, float $s, float $l): string
    {
        $h = fmod($h, 360.0);
        if ($h < 0) {
            $h += 360.0;
        }
        $s = max(0.0, min(100.0, $s)) / 100;
        $l = max(0.0, min(100.0, $l)) / 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60.0, 2) - 1));
        $m = $l - $c / 2;

        $r = 0.0;
        $g = 0.0;
        $b = 0.0;

        if ($h < 60) {
            $r = $c; $g = $x; $b = 0;
        } elseif ($h < 120) {
            $r = $x; $g = $c; $b = 0;
        } elseif ($h < 180) {
            $r = 0; $g = $c; $b = $x;
        } elseif ($h < 240) {
            $r = 0; $g = $x; $b = $c;
        } elseif ($h < 300) {
            $r = $x; $g = 0; $b = $c;
        } else {
            $r = $c; $g = 0; $b = $x;
        }

        $r = (int) round(($r + $m) * 255);
        $g = (int) round(($g + $m) * 255);
        $b = (int) round(($b + $m) * 255);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    private static function shiftHueAway(float $hue, array $blocked, float $minDistance): float
    {
        $attempts = 0;
        $candidate = $hue;
        while ($attempts < 8) {
            $tooClose = false;
            foreach ($blocked as $blockedHue) {
                $distance = abs($candidate - $blockedHue);
                $distance = min($distance, 360 - $distance);
                if ($distance < $minDistance) {
                    $tooClose = true;
                    break;
                }
            }
            if (! $tooClose) {
                break;
            }
            $candidate = fmod($candidate + 31 + ($attempts * 7), 360.0);
            $attempts++;
        }

        return $candidate;
    }
}