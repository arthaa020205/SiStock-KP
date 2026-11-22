<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price_per_base_unit', 15, 2)
                ->nullable()
                ->after('price_per_pack');
        });

        Schema::table('product_unit_conversions', function (Blueprint $table) {
            $table->decimal('selling_price', 15, 2)
                ->nullable()
                ->after('conversion_factor');
        });

        // Pindahkan harga satuan dasar lama.
        DB::table('products')->update([
            'price_per_base_unit' => DB::raw('price_per_pcs'),
        ]);

        // Pindahkan harga konversi lama ke masing-masing produk.
        $conversions = DB::table('product_unit_conversions')
            ->get(['id', 'product_id']);

        foreach ($conversions as $conversion) {
            $product = DB::table('products')
                ->where('id', $conversion->product_id)
                ->first();

            if ($product) {
                DB::table('product_unit_conversions')
                    ->where('id', $conversion->id)
                    ->update([
                        'selling_price' => $product->price_per_pack,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('product_unit_conversions', function (Blueprint $table) {
            $table->dropColumn('selling_price');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price_per_base_unit');
        });
    }
};