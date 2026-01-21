<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ms_brand', function (Blueprint $table) {
            $table->id('brand_id');
            $table->string('brand_code', 50)->unique();
            $table->string('brand_name', 100);
            $table->string('brand_group', 100)->nullable();
            $table->string('country_origin', 100)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_date')->nullable()->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('updated_date')->nullable();
            $table->char('unique_id', 36)->unique()->comment('UUIDV4, di gunakan untuk Get Data dari URL');
            $table->enum('is_active', ['0', '1'])->nullable()->default('1');
            
            // Indexes
            $table->index('created_by', 'idx_brand_created_by');
            $table->index('updated_by', 'idx_brand_updated_by');
            $table->index('is_active', 'idx_brand_is_active');
            
            // Foreign keys
            $table->foreign('created_by', 'fk_brand_created_by')->references('user_id')->on('ms_users')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('updated_by', 'fk_brand_updated_by')->references('user_id')->on('ms_users')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_brand');
    }
};
