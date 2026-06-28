<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'type',
        'quantity_change',
        'stock_before',
        'stock_after',
        'unit_cost',
        'reference_id',
        'reference_type',
        'employee_id',
        'notes',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'stock_before'    => 'integer',
        'stock_after'     => 'integer',
        'unit_cost'       => 'integer',
    ];

    // ─── Relaciones ────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    // ─── Factory method ────────────────────────────────────

    /**
     * Crea un movimiento de stock y actualiza el producto/variante atómicamente.
     */
    public static function record(
        Product $product,
        int $quantityChange,
        string $type,
        ?Model $reference = null,
        ?int $variantId = null,
        ?int $unitCost = null,
        ?string $notes = null,
        ?int $employeeId = null
    ): self {
        $target = $variantId
            ? ProductVariant::findOrFail($variantId)
            : $product;

        $stockBefore = $target->stock;
        $stockAfter  = $stockBefore + $quantityChange;

        $movement = static::create([
            'product_id'         => $product->id,
            'product_variant_id' => $variantId,
            'type'               => $type,
            'quantity_change'    => $quantityChange,
            'stock_before'       => $stockBefore,
            'stock_after'        => $stockAfter,
            'unit_cost'          => $unitCost,
            'reference_id'       => $reference?->getKey(),
            'reference_type'     => $reference ? get_class($reference) : null,
            'employee_id'        => $employeeId,
            'notes'              => $notes,
        ]);

        $target->update(['stock' => $stockAfter]);

        return $movement;
    }

    // ─── Scopes ────────────────────────────────────────────

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            'purchase'   => 'Compra',
            'sale'       => 'Venta',
            'return_in'  => 'Devolución entrada',
            'return_out' => 'Devolución salida',
            'adjustment' => 'Ajuste manual',
            'damage'     => 'Merma',
            'loss'       => 'Pérdida',
            'transfer'   => 'Transferencia',
            default      => $this->type,
        };
    }
}
