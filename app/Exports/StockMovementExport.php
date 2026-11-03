<?php

namespace App\Exports;

use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StockMovementExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = StockMovement::with([
            'batchStock.productBatch.product.baseUnit',
            'batchStock.rackLocation',
            'creator',
        ]);

        // Pencarian produk dan kode batch
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];

            $query->whereHas('batchStock.productBatch', function ($batchQuery) use ($search) {
                $batchQuery->where('batch_code', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        // Filter jenis pergerakan
        if (!empty($this->filters['movement_type'])) {
            $query->where('movement_type', $this->filters['movement_type']);
        }

        // Filter tanggal mulai
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        // Filter tanggal akhir
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        $movementLabels = [
            'in' => 'Barang Masuk',
            'out' => 'Barang Keluar',
            'transfer_in' => 'Transfer Masuk',
            'transfer_out' => 'Transfer Keluar',
            'adjustment_in' => 'Penyesuaian Masuk',
            'adjustment_out' => 'Penyesuaian Keluar',
        ];

        return $query
            ->latest('created_at')
            ->get()
            ->map(function ($movement) use ($movementLabels) {
                $batchStock = $movement->batchStock;
                $productBatch = $batchStock?->productBatch;
                $product = $productBatch?->product;

                return [
                    'Tanggal' => $movement->created_at?->format('d/m/Y H:i'),
                    'Kode Produk' => $product?->code ?? '-',
                    'Nama Produk' => $product?->name ?? '-',
                    'Kode Batch' => $productBatch?->batch_code ?? '-',
                    'Lokasi Rak' => $batchStock?->rackLocation?->name ?? '-',
                    'Jenis Pergerakan' => $movementLabels[$movement->movement_type]
                        ?? $movement->movement_type,
                    'Jumlah' => $movement->quantity,
                    'Satuan' => $product?->baseUnit?->name ?? '-',
                    'Stok Sebelum' => $movement->balance_before,
                    'Stok Sesudah' => $movement->balance_after,
                    'Petugas' => $movement->creator?->name ?? '-',
                    'Keterangan' => $movement->notes ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode Produk',
            'Nama Produk',
            'Kode Batch',
            'Lokasi Rak',
            'Jenis Pergerakan',
            'Jumlah',
            'Satuan',
            'Stok Sebelum',
            'Stok Sesudah',
            'Petugas',
            'Keterangan',
        ];
    }
}