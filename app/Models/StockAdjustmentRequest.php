<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentRequest extends Model
{
    protected $fillable = [
        'stock_opname_detail_id',
        'status',
        'request_reason',
        'approval_notes',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function stockOpnameDetail(): BelongsTo
    {
        return $this->belongsTo(
            StockOpnameDetail::class,
            'stock_opname_detail_id'
        );
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function detail(): BelongsTo
    {
        return $this->belongsTo(
            StockOpnameDetail::class,
            'stock_opname_detail_id'
        );
    }
}
