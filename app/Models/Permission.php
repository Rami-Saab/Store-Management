<?php // Name : Rodain Gouzlan Id:

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model: Permission (الصلاحية)
 *
 * يمثل صلاحية دقيقة يمكن ربطها بالأدوار (Roles) عبر permission_role.
 * تُستخدم لاحقاً لفحص السماح/المنع داخل السياسات أو الواجهات.
 */
class Permission extends Model
{
    // الحقول القابلة للتعبئة الجماعية.
    protected $fillable = [
        'name',
        'slug',
    ];

    public function roles(): BelongsToMany
    {
        // الأدوار التي تمتلك هذه الصلاحية (many-to-many).
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
}

// Summary: نموذج للصلاحيات التي يمكن ربطها بالأدوار للتحكم بالوصول داخل النظام.