<?php // Name : Rodain Gouzlan Id:

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Department (القسم الإداري)
 *
 * يمثل أقسام العمل داخل النظام (مثل: Store Management).
 * يُستخدم لربط المستخدمين والفروع ضمن نطاق قسم محدد عبر department_id.
 */
class Department extends Model
{
    // الحقول القابلة للتعبئة الجماعية (Mass Assignment).
    protected $fillable = [
        'name',
        'slug',
    ];

    public function users(): HasMany
    {
        // المستخدمون المرتبطون بهذا القسم.
        return $this->hasMany(User::class);
    }

    public function stores(): HasMany
    {
        // الفروع المرتبطة بهذا القسم (مثلاً فروع قسم إدارة الفروع).
        return $this->hasMany(Store::class);
    }
}

// Summary: نموذج يمثل الأقسام ويربط المستخدمين والفروع ضمن نطاق قسم محدد.