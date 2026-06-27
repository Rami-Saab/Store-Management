<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$escapePdf = static function (string $value): string {
    $value = str_replace('\\', '\\\\', $value);
    $value = str_replace('(', '\\(', $value);
    $value = str_replace(')', '\\)', $value);
    return $value;
};

$nameMap = [
    'DAM-001' => 'Damascus Main Branch',
    'HMS-002' => 'Homs Branch',
    'ALP-003' => 'Aleppo Branch',
    'LAT-004' => 'Lattakia Branch',
    'SWD-005' => 'Sweida Branch',
    'HMA-006' => 'Hama Branch',
];

$cityMap = [
    'DAM' => 'Damascus',
    'HMS' => 'Homs',
    'ALP' => 'Aleppo',
    'LAT' => 'Lattakia',
    'SWD' => 'Sweida',
    'HMA' => 'Hama',
];

$addressMap = [
    'DAM-001' => 'Al Maliki, Al Jalaa St.',
    'HMS-002' => 'Al Waer, Al Hadara St.',
    'ALP-003' => 'Al Jamilia, Baron St.',
    'LAT-004' => 'Al Salibeh, Baghdad St.',
    'SWD-005' => 'Al Qarya Rd., Corniche St.',
    'HMA-006' => 'Al Hadir, Al Murabit St.',
];

$buildPdf = static function (
    array $details,
    array $services,
    string $hours,
    string $footer,
    string $headline,
    string $title,
    string $summary,
    array $socials
) use ($escapePdf): string {
    $accent = "0.06 0.45 0.42";
    $contentLines = [];
    $contentLines[] = "0.06 0.45 0.42 rg";
    $contentLines[] = "0 760 595 82 re f";
    $contentLines[] = "0 0 0 rg";
    $contentLines[] = "BT";
    $contentLines[] = "/F1 22 Tf";
    $contentLines[] = "70 800 Td";
    $contentLines[] = "(".$escapePdf($title).") Tj";
    $contentLines[] = "0 -22 Td";
    $contentLines[] = "/F1 12 Tf";
    $contentLines[] = "(".$escapePdf($headline).") Tj";
    $contentLines[] = "ET";
    $contentLines[] = "BT";
    $contentLines[] = "/F1 12 Tf";
    $contentLines[] = "70 734 Td";
    $contentLines[] = "(".$escapePdf($summary).") Tj";
    $contentLines[] = "ET";
    $contentLines[] = "{$accent} RG";
    $contentLines[] = "70 712 m 520 712 l S";
    $contentLines[] = "0 0 0 rg";
    $contentLines[] = "BT";
    $contentLines[] = "/F1 14 Tf";
    $contentLines[] = "70 694 Td";
    $contentLines[] = "(Branch Overview) Tj";
    $contentLines[] = "0 -18 Td";
    $contentLines[] = "/F1 12 Tf";

    foreach ($details as $label => $value) {
        $line = $escapePdf($label.': '.$value);
        $contentLines[] = "({$line}) Tj";
        $contentLines[] = "0 -16 Td";
    }

    $contentLines[] = "0 -6 Td";
    $contentLines[] = "/F1 14 Tf";
    $contentLines[] = "(Highlights) Tj";
    $contentLines[] = "0 -18 Td";
    $contentLines[] = "/F1 12 Tf";

    foreach ($services as $service) {
        $line = $escapePdf('- '.$service);
        $contentLines[] = "({$line}) Tj";
        $contentLines[] = "0 -16 Td";
    }

    $contentLines[] = "0 -6 Td";
    $contentLines[] = "(".$escapePdf('Working hours: '.$hours).") Tj";
    $contentLines[] = "0 -18 Td";
    $contentLines[] = "(".$escapePdf($footer).") Tj";
    $contentLines[] = "0 -18 Td";
    $contentLines[] = "(Social: ".$escapePdf(implode(' | ', $socials)).") Tj";
    $contentLines[] = "ET";

    $content = implode("\n", $contentLines);
    $contentBytes = $content;

    $objects = [];
    $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>\nendobj\n";
    $objects[] = "4 0 obj\n<< /Length ".strlen($contentBytes)." >>\nstream\n{$contentBytes}\nendstream\nendobj\n";
    $objects[] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

    $header = "%PDF-1.4\n";
    $body = '';
    $offsets = [];
    $pos = strlen($header);

    foreach ($objects as $obj) {
        $offsets[] = $pos;
        $body .= $obj;
        $pos += strlen($obj);
    }

    $xref = "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
    foreach ($offsets as $off) {
        $xref .= sprintf("%010d 00000 n \n", $off);
    }

    $trailer = "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$pos}\n%%EOF\n";

    return $header.$body.$xref.$trailer;
};

$services = [
    'Fast in-store pickup and guidance',
    'Real-time product availability checks',
    'Warranty and returns assistance',
    'Order tracking and support desk',
];

$footer = 'Contact: +963 11 123 4567 | info@gmail.com';
$socials = [
    'Instagram @store_branches',
    'Facebook /store.branches',
    'X @store_branches',
];

\App\Models\Store::query()->with(['province', 'assignedManager'])->get()->each(function ($store) use ($nameMap, $cityMap, $addressMap, $services, $footer, $buildPdf, $socials) {
    $branchCode = (string) $store->branch_code;
    $provinceCode = (string) ($store->province?->code ?? '');
    $branchName = $nameMap[$branchCode] ?? ('Branch '.$branchCode);
    $city = $cityMap[$provinceCode] ?? 'N/A';
    $address = $addressMap[$branchCode] ?? 'N/A';
    $managerName = $store->assignedManager->first()?->name ?? 'Unassigned';
    $openingDate = $store->opening_date?->format('Y-m-d') ?? 'N/A';
    if ($store->workday_starts_at && $store->workday_ends_at) {
        $format12h = static function (string $time): string {
            [$h, $m] = array_map('intval', explode(':', $time, 2));
            $suffix = $h >= 12 ? 'PM' : 'AM';
            $h = $h % 12;
            if ($h === 0) {
                $h = 12;
            }
            return sprintf('%d:%02d %s', $h, $m, $suffix);
        };
        $hours = $format12h($store->workday_starts_at).' - '.$format12h($store->workday_ends_at);
    } elseif ($store->working_hours) {
        $hours = (string) $store->working_hours;
        if (preg_match('/^(\\d{2}):(\\d{2})\\s*-\\s*(\\d{2}):(\\d{2})$/', $hours, $matches)) {
            $format12h = static function (string $time): string {
                [$h, $m] = array_map('intval', explode(':', $time, 2));
                $suffix = $h >= 12 ? 'PM' : 'AM';
                $h = $h % 12;
                if ($h === 0) {
                    $h = 12;
                }
                return sprintf('%d:%02d %s', $h, $m, $suffix);
            };
            $hours = $format12h($matches[1].':'.$matches[2]).' - '.$format12h($matches[3].':'.$matches[4]);
        }
    } else {
        $hours = '09:00 AM - 05:00 PM';
    }

    $details = [
        'Branch Name' => $branchName,
        'Branch Code' => $branchCode,
        'City' => $city,
        'Address' => $address,
        'Phone' => $store->phone ?: 'N/A',
        'Email' => $store->email ?: 'N/A',
        'Opening Date' => $openingDate,
        'Manager' => $managerName,
    ];

    $headline = 'Your reliable local hub for products and support.';
    $title = $branchName.' Brochure';
    $summary = 'Located in '.$city.', this branch provides quality products, fair prices, and excellent service.';
    $pdf = $buildPdf($details, $services, $hours, $footer, $headline, $title, $summary, $socials);
    $path = 'brochures/stores/branch-'.$branchCode.'.pdf';

    Storage::disk('public')->put($path, $pdf);
    $store->update(['brochure_path' => $path]);
});
