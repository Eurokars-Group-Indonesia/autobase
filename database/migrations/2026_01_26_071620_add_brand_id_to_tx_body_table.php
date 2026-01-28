<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tx_body', function (Blueprint $table) {
            $table->string('brand_code', 50)->after('invoice_no');
            $table->index('brand_code', 'idx_brand_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_body', function (Blueprint $table) {
            $table->dropIndex('idx_brand_code');
            $table->dropColumn('brand_code');
        });
    }
};
