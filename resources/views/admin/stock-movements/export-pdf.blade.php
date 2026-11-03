<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pergerakan Stok</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0 0 5px;
            font-size: 16px;
        }

        .header p {
            margin: 0;
            color: #666;
        }

        .filter-info {
            margin-bottom: 12px;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #1e293b;
            color: white;
            padding: 7px 5px;
            border: 1px solid #ddd;
            text-align: center;
        }

        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
        }

        tbody tr:nth-child(even) {
            background-color: #f1f5f9;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>SISTEM INVENTORY STOCK (SiStock)</h2>
        <p>Laporan Riwayat Pergerakan Stok Barang</p>
    </div>

    <div class="filter-info">
        <strong>Dicetak pada:</strong>
        {{ now()->format('d/m/Y H:i') }}
        <br>

        <strong>Total Data:</strong>
        {{ $stockMovements->count() }} pergerakan stok
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Kode Batch</th>
                <th>Lokasi Rak</th>
                <th>Jenis Pergerakan</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Stok Sebelum</th>
                <th>Stok Sesudah</th>
                <th>Petugas</th>
                <th>Keterangan</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($stockMovements as $index => $movement)

                @php
                    $batchStock = $movement->batchStock;
                    $productBatch = $batchStock?->productBatch;
                    $product = $productBatch?->product;

                    $labels = [
                        'in' => 'Barang Masuk',
                        'out' => 'Barang Keluar',
                        'transfer_in' => 'Transfer Masuk',
                        'transfer_out' => 'Transfer Keluar',
                        'adjustment_in' => 'Penyesuaian Masuk',
                        'adjustment_out' => 'Penyesuaian Keluar',
                    ];

                    $isIncrease = in_array($movement->movement_type, [
                        'in',
                        'transfer_in',
                        'adjustment_in',
                    ]);
                @endphp

                <tr>
                    <td class="center">{{ $index + 1 }}</td>

                    <td class="center">
                        {{ $movement->created_at?->format('d/m/Y H:i') ?? '-' }}
                    </td>

                    <td>{{ $product?->code ?? '-' }}</td>

                    <td>{{ $product?->name ?? '-' }}</td>

                    <td>{{ $productBatch?->batch_code ?? '-' }}</td>

                    <td>
                        {{ $batchStock?->rackLocation?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $labels[$movement->movement_type] ?? $movement->movement_type }}
                    </td>

                    <td class="right">
                        {{ $isIncrease ? '+' : '-' }}{{ number_format((float) $movement->quantity, 3, ',', '.') }}
                    </td>

                    <td>
                        {{ $product?->baseUnit?->name ?? '-' }}
                    </td>

                    <td class="right">
                        {{ number_format((float) $movement->balance_before, 3, ',', '.') }}
                    </td>

                    <td class="right">
                        {{ number_format((float) $movement->balance_after, 3, ',', '.') }}
                    </td>

                    <td>
                        {{ $movement->creator?->name ?? '-' }}
                    </td>

                    <td>{{ $movement->notes ?: '-' }}</td>
                </tr>

            @empty
                <tr>
                    <td colspan="13" class="center">
                        Tidak ada data pergerakan stok.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak melalui Sistem Inventory Stock (SiStock).
    </div>

</body>
</html>