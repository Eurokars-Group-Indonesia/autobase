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
            $table->unsignedBigInteger('brand_id')->after('invoice_no');
            $table->index('brand_id', 'idx_brand_id');
            
            // Foreign key
            $table->foreign('brand_id', 'fk_tx_body_brand_id')
                ->references('brand_id')
                ->on('ms_brand')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_body', function (Blueprint $table) {
            $table->dropForeign('fk_tx_body_brand_id');
            $table->dropIndex('idx_brand_id');
            $table->dropColumn('brand_id');
        });
    }
};
