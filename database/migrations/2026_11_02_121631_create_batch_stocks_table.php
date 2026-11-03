<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_batch_id')
                ->constrained('product_batches')
                ->restrictOnDelete();

            $table->foreignId('rack_location_id')
                ->constrained('rack_locations')
                ->restrictOnDelete();

            $table->decimal('quantity', 15, 3)->default(0);

            $table->timestamps();

            $table->unique([
                'product_batch_id',
                'rack_location_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_stocks');
    }
};