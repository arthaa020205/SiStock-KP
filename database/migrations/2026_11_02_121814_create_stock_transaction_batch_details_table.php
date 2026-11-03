
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transaction_batch_details', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('stock_transaction_detail_id');

            $table->unsignedBigInteger('source_batch_stock_id')->nullable();

            $table->unsignedBigInteger('destination_batch_stock_id')->nullable();

            $table->decimal('quantity', 15, 3);

            $table->timestamps();

            // Foreign key dengan nama pendek
            $table->foreign(
                'stock_transaction_detail_id',
                'stbd_detail_fk'
            )->references('id')
                ->on('stock_transaction_details')
                ->cascadeOnDelete();

            $table->foreign(
                'source_batch_stock_id',
                'stbd_source_fk'
            )->references('id')
                ->on('batch_stocks')
                ->restrictOnDelete();

            $table->foreign(
                'destination_batch_stock_id',
                'stbd_dest_fk'
            )->references('id')
                ->on('batch_stocks')
                ->restrictOnDelete();

            $table->index('source_batch_stock_id');
            $table->index('destination_batch_stock_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transaction_batch_details');
    }
};
