<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PendingInvoice extends Model
{
    protected $fillable = [
        'supplier_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'delivery_date',
        'total_amount',
        'tax_amount',
        'notes',
        'status',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'    => 'date',
            'due_date'      => 'date',
            'delivery_date' => 'date',
            'received_at'   => 'datetime',
            'total_amount'  => 'integer',
            'tax_amount'    => 'integer',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PendingInvoiceItem::class, 'pending_invoice_id');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && $this->status === 'pending';
    }

    public static function statuses(): array
    {
        return ['pending', 'received', 'paid'];
    }
}
