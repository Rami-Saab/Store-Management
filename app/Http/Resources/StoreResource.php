<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Store Resource
 *
 * Transforms Store model to standardized JSON API response.
 * Uses conditional loading to prevent N+1 queries.
 */
class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $manager = null;
        if ($this->relationLoaded('manager') && $this->manager) {
            $manager = [
                'id' => $this->manager->id,
                'name' => $this->manager->name,
                'email' => $this->manager->email,
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'branch_code' => $this->branch_code,
            'status' => $this->status,
            'province_id' => $this->province_id,
            'city' => $this->city,
            'address' => $this->address,
            'phone' => $this->phone,
            'description' => $this->description,
            'email' => $this->email,
            'working_hours' => $this->working_hours,
            'workday_starts_at' => $this->workday_starts_at,
            'workday_ends_at' => $this->workday_ends_at,
            'opening_date' => $this->opening_date?->format('Y-m-d'),
            'brochure_path' => $this->brochure_path,
            'department_id' => $this->department_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            'province' => $this->whenLoaded('province', fn () => [
                'id' => $this->province?->id,
                'name' => $this->province?->name,
                'code' => $this->province?->code,
            ]),

            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department?->id,
                'name' => $this->department?->name,
                'slug' => $this->department?->slug,
            ]),

            'manager' => $manager,

            'employees' => $this->whenLoaded('employees', fn () => $this->employees->map(
                fn ($employee) => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'role' => $employee->role,
                ]
            )->values()),

            'products' => $this->whenLoaded('products', fn () => $this->products->map(
                fn ($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $product->pivot?->quantity,
                ]
            )->values()),

            'warehouses' => $this->whenLoaded('warehouses', fn () => $this->warehouses->map(
                fn ($warehouse) => [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'location' => $warehouse->location,
                ]
            )->values()),

            'employees_count' => $this->when(
                property_exists($this->resource, 'employees_count'),
                (int) $this->employees_count
            ),

            'products_count' => $this->when(
                property_exists($this->resource, 'products_count'),
                (int) $this->products_count
            ),
        ];
    }
}