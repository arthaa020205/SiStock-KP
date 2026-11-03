<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransactionBatchDetail extends Model
{
    protected $fillable = [
        'stock_transaction_detail_id',
        'source_batch_stock_id',
        'destination_batch_stock_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
        ];
    }

    public function transactionDetail(): BelongsTo
    {
        return $this->belongsTo(
            StockTransactionDetail::class,
            'stock_transaction_detail_id'
        );
    }

    public function sourceBatchStock(): BelongsTo
    {
        return $this->belongsTo(
            BatchStock::class,
            'source_batch_stock_id'
        );
    }

    public function destinationBatchStock(): BelongsTo
    {
        return $this->belongsTo(
            BatchStock::class,
            'destination_batch_stock_id'
        );
    }
}
