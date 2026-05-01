<?php // Name : Rodain Gouzlan Id:

namespace App\Support;

/**
 * Helper: توحيد معلومات التواصل (البريد/الهاتف)
 *
 * الهدف من هذا الملف:
 * - إعطاء بريد افتراضي ثابت عند عدم توفر بريد (وفق متطلبات المشروع).
 * - تطبيع رقم الهاتف ليصبح بصيغة موحدة تبدأ بـ 09 دائماً.
 *
 * ملاحظة:
 * - هذا التوحيد يُستخدم في Models (User/Store) و Services (مثل عرض البرشور).
 */
class UserContact
{
    // بريد افتراضي موحد لكل الموظفين عند عرض البيانات (حسب متطلبات المشروع).
    public const DEFAULT_EMAIL = 'staff@branch.com';
    // هاتف افتراضي موحد يبدأ بـ 09 (حسب متطلبات المشروع).
    public const DEFAULT_PHONE = '0900000000';

    public static function email(?string $email = null, ?string $name = null, ?int $id = null): string
    {
        $email = trim((string) $email);
        if ($email !== '') {
            return $email;
        }

        $slug = '';
        if ($name) {
            $slug = \Illuminate\Support\Str::slug($name, '.');
        }

        if ($slug === '') {
            return $id ? ('staff.'.$id.'@branch.com') : self::DEFAULT_EMAIL;
        }

        $suffix = $id ? '.'.$id : '';
        return $slug.$suffix.'@branch.com';
    }

    public static function phone(?string $phone = null, bool $withFallback = true): string
    {
        // استخراج الأرقام فقط من الهاتف (إزالة أي رموز مثل + أو مسافات).
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            // إذا لم يوجد رقم نعيد رقم افتراضي (أو فارغ حسب withFallback).
            return $withFallback ? self::DEFAULT_PHONE : '';
        }

        // إذا كان الرقم يبدأ بكود سوريا 963 نحذفه.
        if (str_starts_with($digits, '963')) {
            $digits = substr($digits, 3);
        }

        // إذا كان أصلاً يبدأ بـ 09 فهو جاهز.
        if (str_starts_with($digits, '09')) {
            return $digits;
        }

        // إذا بدأ بـ 9 فقط نضيف 0 في البداية ليصبح 09.
        if (str_starts_with($digits, '9')) {
            return '0'.$digits;
        }

        // إذا بدأ بـ 0 نحذفها ثم نعيد بناء الصيغة لاحقاً.
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if ($digits === '') {
            return $withFallback ? self::DEFAULT_PHONE : '';
        }

        // في النهاية نضمن أن الهاتف يبدأ دائماً بـ 09.
        return '09'.$digits;
    }
}