<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\StockAdjustmentRequest;
use App\Models\StockOpnameDetail;

class StockOpname extends Model
{
    protected $fillable = [
        'opname_number',
        'opname_date',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'movement_cutoff_id',
    ];

    protected function casts(): array
    {
        return [
            'opname_date' => 'date',
            'approved_at' => 'datetime',
            'movement_cutoff_id' => 'integer',
        ];
    }

    public function adjustmentRequests(): HasManyThrough
    {
        return $this->hasManyThrough(
            StockAdjustmentRequest::class,
            StockOpnameDetail::class,
            'stock_opname_id',
            'stock_opname_detail_id',
            'id',
            'id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(StockOpnameDetail::class);
    }
}