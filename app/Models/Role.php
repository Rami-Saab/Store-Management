<?php // Name : Rodain Gouzlan Id:

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model: Role (الدور)
 *
 * يمثل دور المستخدم داخل النظام (مثل: admin, store_manager, store_employee).
 * يرتبط بالمستخدمين عبر pivot role_user وبالصلاحيات عبر pivot permission_role.
 */
class Role extends Model
{
    // الحقول القابلة للتعبئة الجماعية.
    protected $fillable = [
        'name',
        'slug',
    ];

    public function permissions(): BelongsToMany
    {
        // الصلاحيات المرتبطة بهذا الدور (many-to-many).
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        // المستخدمون الذين يحملون هذا الدور (many-to-many).
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}

// Summary: نموذج يعرّف الأدوار ويربطها بالمستخدمين والصلاحيات عبر جداول pivot.