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
        Schema::create('ms_users', function (Blueprint $table) {
            $table->id('user_id');
            $table->unsignedBigInteger('dealer_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('name', 150);
            $table->string('email', 150)->nullable()->unique();
            $table->string('full_name', 150);
            $table->string('password', 255);
            $table->string('phone', 20)->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->char('unique_id', 36)->unique();
            $table->enum('is_active', ['0', '1'])->default('1');

            // Indexes
            $table->index('dealer_id');
            $table->index('brand_id');
            $table->index('created_by');
            $table->index('updated_by');

            // Foreign Keys - Self Reference only
            $table->foreign('created_by')->references('user_id')->on('ms_users')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('updated_by')->references('user_id')->on('ms_users')
                ->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('ms_users');
    }
};
