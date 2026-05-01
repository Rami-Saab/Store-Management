<?php // Name : Rodain Gouzlan Id:

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Support\UserContact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

/**
 * Model: Store (يمثل الفرع/المتجر)
 *
 * أهم الحقول في جدول stores:
 * - name          : اسم الفرع
 * - branch_code   : كود الفرع (Unique) مثل: DAM-001
 * - province_id   : المحافظة (علاقة belongsTo)
 * - city/address  : معلومات العنوان
 * - phone/email   : معلومات التواصل
 * - status        : حالة الفرع (active/inactive/under_maintenance)
 * - workday_*     : بداية/نهاية الدوام (وقت)
 * - brochure_path : مسار ملف PDF للبرشور داخل storage/public
 * - created_by / updated_by : توثيق من قام بإنشاء/تعديل السجل (إن كان موجوداً في قاعدة البيانات)
 *
 * العلاقات (Relationships):
 * - province()          : المحافظة
 * - users()             : جميع المستخدمين المعيّنين للفرع عبر pivot store_user
 * - manager()           : المدير المعين للفرع عبر manager_id
 * - employees()         : الموظفون المرتبطون بالفرع عبر pivot store_user
 * - products()          : المنتجات المرتبطة بالفرع عبر pivot product_store
 * - warehouses()        : المستودعات المرتبطة بالفرع عبر pivot store_warehouse
 *
 * Scopes:
 * - scopeFilterName / Province / Status / Phone : تستخدم لتجميع منطق الفلاتر في مكان واحد.
 */
class Store extends Model
{
    use HasFactory;

    // الحقول المسموح تعبئتها بشكل جماعي (Mass Assignment) عند create/update.
    protected $fillable = [
        'name',
        'branch_code',
        'province_id',
        'city',
        'address',
        'phone',
        'description',
        'email',
        'status',
        'working_hours',
        'workday_starts_at',
        'workday_ends_at',
        'opening_date',
        'brochure_path',
        'manager_id',
        'department_id',
        'created_by',
        'updated_by',
    ];

    // تحويل بعض الحقول إلى أنواع مناسبة تلقائياً عند القراءة/الكتابة.
    protected $casts = [
        'opening_date' => 'date',
    ];

    public function province(): BelongsTo
    {
        // كل فرع ينتمي إلى محافظة واحدة.
        return $this->belongsTo(Province::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employees(): BelongsToMany
    {
        // الموظفون المرتبطون بالفرع عبر pivot store_user.
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        // alias لتفادي كسر أي استدعاء قديم (يمثل الموظفين المرتبطين بالفرع).
        return $this->employees();
    }

    public function manager(): BelongsTo
    {
        // المدير المعيّن للفرع عبر manager_id.
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function products(): BelongsToMany
    {
        // المنتجات المرتبطة بالفرع عبر pivot product_store (الفرع يبيع عدة منتجات والمنتج موجود بعدة أفرع).
        $relation = $this->belongsToMany(Product::class)->withTimestamps();
        if (Schema::hasColumn('product_store', 'quantity')) {
            $relation->withPivot('quantity');
        }

        return $relation;
    }

    public function warehouses(): BelongsToMany
    {
        // المستودعات المرتبطة بالفرع عبر pivot store_warehouse.
        return $this->belongsToMany(Warehouse::class, 'store_warehouse')->withTimestamps();
    }

    public function getPhoneAttribute($value): string
    {
        // Accessor: عند قراءة phone من النموذج نعيده بصيغة منسقة/قياسية.
        return UserContact::phone($value, false);
    }

    public function setPhoneAttribute($value): void
    {
        // Mutator: قبل حفظ phone في قاعدة البيانات نقوم بتطبيعه لصيغة موحدة.
        $this->attributes['phone'] = UserContact::phone($value, false);
    }

    public function createdBy(): BelongsTo
    {
        // المستخدم الذي أنشأ السجل (إن كان العمود موجوداً).
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        // المستخدم الذي قام بآخر تعديل (إن كان العمود موجوداً).
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeFilterName(Builder $query, ?string $name): Builder
    {
        // Scope: فلترة بالاسم (LIKE) عند وجود قيمة بحث.
        $name = trim((string) $name);
        if ($name === '') {
            return $query;
        }

        return $query->where('name', 'like', '%'.$name.'%');
    }

    public function scopeFilterProvince(Builder $query, $provinceId): Builder
    {
        // Scope: فلترة بالمحافظة (province_id).
        if (! $provinceId) {
            return $query;
        }

        return $query->where('province_id', $provinceId);
    }

    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        // Scope: فلترة بالحالة (active/inactive/under_maintenance).
        $status = trim((string) $status);
        if ($status === '') {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeFilterPhone(Builder $query, ?string $phone): Builder
    {
        // Scope: فلترة برقم الهاتف (بحث جزئي) بعد إزالة أي أحرف غير رقمية.
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (! $digits) {
            return $query;
        }

        return $query->where('phone', 'like', '%'.$digits.'%');
    }
}