<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Requests;

/**
 * Form Request: تحديث الفرع
 *
 * هذا الملف يرث من StoreRequest لأن قواعد التحقق بين الإنشاء والتحديث متطابقة تقريباً.
 * تم الفصل فقط لتمييز نوع العملية في Controllers (Create vs Update) مع نفس قواعد التحقق.
 */

class UpdateStoreRequest extends StoreRequest
{
}