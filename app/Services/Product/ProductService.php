<?php // Name : Rodain Gouzlan Id:

namespace App\Services\Product;

/**
 * Service: خدمات المنتجات (Products)
 *
 * الهدف:
 * - توفير استعلامات جاهزة للمنتجات المتاحة (available) مع ترتيب ثابت.
 * - في صفحات ربط المنتجات بالأفرع نحتاج لإظهار الأفرع المرتبطة بكل منتج
 *   لذلك يتم eager loading للعلاقة stores.
 */

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    public function queryAvailable(array $columns = ['id', 'name', 'sku', 'price']): Builder
    {
        // Query Builder جاهز لإرجاع المنتجات المتاحة فقط (status = available) مع أعمدة محددة.
        return Product::query()
            ->select($columns)
            ->where('status', 'available')
            ->orderBy('name');
    }

    public function availableWithStores(array $columns = ['id', 'name', 'sku', 'price']): Collection
    {
        // جلب المنتجات المتاحة مع تحميل الأفرع المرتبطة بها لعرضها في صفحة ربط المنتجات.
        $version = (int) Cache::get('product_store_version', 1);
        $columnsKey = md5(implode('|', $columns));
        $cacheKey = 'products_available_with_stores_v1:'.$version.':'.$columnsKey;

        return Cache::remember($cacheKey, now()->addSeconds(30), function () use ($columns): Collection {
            return $this->queryAvailable($columns)
                ->with(['stores:id,branch_code,name'])
                ->get();
        });
    }
}