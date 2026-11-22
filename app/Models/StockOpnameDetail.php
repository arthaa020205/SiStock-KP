<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpnameDetail extends Model
{
    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'batch_stock_id',
        'system_quantity',
        'physical_quantity',
        'difference_quantity',
        'is_counted',
        'counted_at',
        'counted_movement_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'system_quantity' => 'decimal:3',
            'physical_quantity' => 'decimal:3',
            'difference_quantity' => 'decimal:3',
            'is_counted' => 'boolean',
            'counted_at' => 'datetime',
            'counted_movement_id' => 'integer',
        ];
    }

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Tetap dipertahankan untuk riwayat Stock Opname lama.
    public function batchStock(): BelongsTo
    {
        return $this->belongsTo(BatchStock::class);
    }

    public function adjustmentRequests(): HasMany
    {
        return $this->hasMany(StockAdjustmentRequest::class);
    }
}