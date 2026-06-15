<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'company_name',
        'category',
        'contact_name',
        'visit_day',
        'delivery_time_hours',
        'minimum_order_amount',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function pendingInvoices(): HasMany
    {
        return $this->hasMany(PendingInvoice::class);
    }

    public function supplierProductPrices(): HasMany
    {
        return $this->hasMany(SupplierProductPrice::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
