<?php // Name : Rodain Gouzlan Id:

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

/**
 * Model: Product (المنتج)
 *
 * يمثل منتجاً يمكن بيعه في أكثر من فرع (many-to-many مع Store).
 * جدول الربط (pivot) المستخدم: product_store.
 */
class Product extends Model
{
    use HasFactory;

    // الحقول المسموح تعبئتها بشكل جماعي (Mass Assignment).
    protected $fillable = [
        'name',
        'sku',
        'price',
        'status',
    ];

    // تحويل السعر إلى Decimal بدقة رقمين بعد الفاصلة عند القراءة/الكتابة.
    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function stores(): BelongsToMany
    {
        // علاقة many-to-many: المنتج يمكن أن يكون موجوداً في عدة أفرع.
        $relation = $this->belongsToMany(Store::class)->withTimestamps();
        if (Schema::hasColumn('product_store', 'quantity')) {
            $relation->withPivot('quantity');
        }

        return $relation;
    }
}