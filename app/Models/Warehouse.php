<?php // Name : Rodain Gouzlan Id:

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// نموذج المستودع المرتبط بالفروع (شرط الحذف: لا حذف عند وجود ارتباطات).
/**
 * Model: Warehouse
 *
 * Minimal integration model for Store Branch Management (Block 3).
 * This keeps the relationship clean without implementing the full Warehouse module.
 */
class Warehouse extends Model
{
    use HasFactory;

    // الحقول القابلة للتعبئة الجماعية.
    protected $fillable = [
        'name',
        'location',
        'status',
    ];

    // علاقة المستودعات بالفروع عبر جدول pivot store_warehouse.
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_warehouse')->withTimestamps();
    }
}