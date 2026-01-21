<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ms_dealers', function (Blueprint $table) {
            $table->id('dealer_id');
            $table->string('dealer_code', 50)->unique();
            $table->string('dealer_name', 150);
            $table->string('city', 100)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_date')->nullable()->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('updated_date')->nullable();
            $table->char('unique_id', 36)->unique()->comment('UUIDV4, di gunakan untuk Get Data dari URL');
            $table->enum('is_active', ['0', '1'])->nullable()->default('1');
            
            // Indexes
            $table->index('created_by', 'idx_dealer_created_by');
            $table->index('updated_by', 'idx_dealer_updated_by');
            $table->index('is_active', 'idx_dealer_is_active');
            
            // Foreign keys
            $table->foreign('created_by', 'fk_dealer_created_by')->references('user_id')->on('ms_users')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('updated_by', 'fk_dealer_updated_by')->references('user_id')->on('ms_users')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_dealers');
    }
};
