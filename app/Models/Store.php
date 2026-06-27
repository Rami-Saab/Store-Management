<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\UserContact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

/**
 * Store Model
 *
 * Represents a branch/store in the multi-branch management system.
 *
 * @property int $id
 * @property string $name
 * @property string $branch_code
 * @property int $province_id
 * @property string $city
 * @property string $address
 * @property string $phone
 * @property string|null $description
 * @property string|null $email
 * @property string $status
 * @property string|null $working_hours
 * @property string|null $workday_starts_at
 * @property string|null $workday_ends_at
 * @property \Illuminate\Support\Carbon|null $opening_date
 * @property string|null $brochure_path
 * @property int|null $manager_id
 * @property int|null $department_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read Province $province
 * @property-read Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $employees
 * @property-read User|null $manager
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Warehouse> $warehouses
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
class Store extends Model
{
    use HasFactory;

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

    protected $casts = [
        'opening_date' => 'date',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->employees();
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function products(): BelongsToMany
    {
        $relation = $this->belongsToMany(Product::class)->withTimestamps();
        if (Schema::hasColumn('product_store', 'quantity')) {
            $relation->withPivot('quantity');
        }

        return $relation;
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'store_warehouse')->withTimestamps();
    }

    public function getPhoneAttribute(?string $value): string
    {
        return UserContact::phone($value, false);
    }

    public function setPhoneAttribute(string|null $value): void
    {
        $this->attributes['phone'] = UserContact::phone($value, false);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeFilterName(Builder $query, ?string $name): Builder
    {
        $name = trim((string) $name);
        if ($name === '') {
            return $query;
        }

        return $query->where('name', 'like', '%'.$name.'%');
    }

    public function scopeFilterProvince(Builder $query, int|string|null $provinceId): Builder
    {
        if (! $provinceId) {
            return $query;
        }

        return $query->where('province_id', $provinceId);
    }

    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        $status = trim((string) $status);
        if ($status === '') {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeFilterPhone(Builder $query, ?string $phone): Builder
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (! $digits) {
            return $query;
        }

        return $query->where('phone', 'like', '%'.$digits.'%');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }
}