<?php // Name : Rodain Gouzlan Id:

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Province (المحافظة/الولاية)
 *
 * تستخدم لتوحيد المحافظات وربطها بالأفرع عبر province_id.
 * العلاقة: Province لديها عدة Stores (hasMany).
 */
class Province extends Model
{
    use HasFactory;

    // الحقول المسموح تعبئتها بشكل جماعي (Mass Assignment).
    protected $fillable = [
        'name',
        'code',
    ];

    public function stores(): HasMany
    {
        // كل محافظة تحتوي عدة أفرع (Stores) مرتبطة بها عبر province_id.
        return $this->hasMany(Store::class);
    }
}