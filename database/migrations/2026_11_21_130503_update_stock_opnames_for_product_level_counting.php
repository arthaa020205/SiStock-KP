<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            // Batas ID pergerakan stok ketika Stock Opname dimulai.
            $table->unsignedBigInteger('movement_cutoff_id')
                ->nullable()
                ->after('approved_at');
        });

        Schema::table('stock_opname_details', function (Blueprint $table) {
            // Satu detail Stock Opname mewakili satu produk.
            $table->foreignId('product_id')
                ->nullable()
                ->after('batch_stock_id')
                ->constrained('products')
                ->restrictOnDelete();

            // Batch tidak lagi wajib untuk pencatatan level produk.
            // Kolom lama tetap dipertahankan untuk riwayat Stock Opname.
            $table->foreignId('batch_stock_id')
                ->nullable()
                ->change();

            // Batas pergerakan stok ketika produk selesai dihitung.
            $table->timestamp('counted_at')->nullable();

            $table->unsignedBigInteger('counted_movement_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn([
                'product_id',
                'counted_at',
                'counted_movement_id',
            ]);

            $table->foreignId('batch_stock_id')
                ->nullable(false)
                ->change();
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropColumn('movement_cutoff_id');
        });
    }
};