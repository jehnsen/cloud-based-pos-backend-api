<?php

namespace App\Models;

use App\Traits\BelongsToStore;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasUuid, BelongsToStore, SoftDeletes;

    protected $fillable = [
        'uuid',
        'store_id',
        'code',
        'name',
        'company_name',
        'tin',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'province',
        'postal_code',
        'contact_person',
        'contact_person_phone',
        'payment_terms_days',
        'lead_time_days',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'payment_terms_days' => 'integer',
            'lead_time_days' => 'integer',
        ];
    }

    // Relationships
    public function supplierProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'supplier_products')
            ->withPivot(['supplier_sku', 'supplier_price', 'lead_time_days', 'minimum_order_quantity', 'is_preferred', 'notes'])
            ->withTimestamps();
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
