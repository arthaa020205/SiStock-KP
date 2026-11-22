<?php

namespace App\Notifications;

use App\Models\StockOpname;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockOpnameSubmitted extends Notification
{
    use Queueable;

    protected StockOpname $opname;
    protected int $differenceCount;

    public function __construct(
        StockOpname $opname,
        int $differenceCount
    ) {
        $this->opname = $opname;
        $this->differenceCount = $differenceCount;
    }

    /**
     * Gunakan database notification.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data yang disimpan ke tabel notifications.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'stock_adjustment_request',

            'title' => 'Permintaan Adjustment Baru',

            'message' => "Stock Opname {$this->opname->opname_number} memiliki {$this->differenceCount} produk dengan selisih stok dan menunggu persetujuan Admin.",

            'url' => route(
                'admin.stock-opnames.show',
                $this->opname->id
            ),

            'icon' => 'fa-clipboard-check',

            'color' => 'blue',

            'stock_opname_id' => $this->opname->id,

            'opname_number' => $this->opname->opname_number,

            'difference_count' => $this->differenceCount,
        ];
    }
}