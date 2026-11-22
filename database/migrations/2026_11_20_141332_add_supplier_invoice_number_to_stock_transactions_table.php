<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->string('supplier_invoice_number', 100)
                ->nullable()
                ->after('supplier_id');

            $table->unique(
                ['supplier_id', 'supplier_invoice_number'],
                'stock_transactions_invoice_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropUnique('stock_transactions_invoice_unique');
            $table->dropColumn('supplier_invoice_number');
        });
    }
};