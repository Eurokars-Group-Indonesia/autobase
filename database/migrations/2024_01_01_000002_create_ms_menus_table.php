<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ms_menus', function (Blueprint $table) {
            $table->id('menu_id');
            $table->string('menu_code', 50)->unique();
            $table->string('menu_name', 100);
            $table->string('menu_url', 255)->nullable();
            $table->string('menu_icon', 50)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('menu_order')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_date')->nullable()->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('updated_date')->nullable();
            $table->char('unique_id', 36)->unique();
            $table->enum('is_active', ['0', '1'])->default('1')->nullable();

            // Indexes
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('parent_id');
            $table->index('menu_order');
            $table->index('is_active');
        });

        Schema::create('ms_role_menus', function (Blueprint $table) {
            $table->id('role_menu_id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_date')->nullable()->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('updated_date')->nullable();
            $table->char('unique_id', 36)->unique();
            $table->enum('is_active', ['0', '1'])->default('1')->nullable();

            // Indexes
            $table->index('menu_id');
            $table->index('role_id');
            $table->index('is_active');
            
            // Unique constraint
            $table->unique(['role_id', 'menu_id']);
            
            // Foreign keys
            $table->foreign('role_id')->references('role_id')->on('ms_role')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('menu_id')->references('menu_id')->on('ms_menus')
                ->onUpdate('restrict')->onDelete('restrict');
        });
        
        // Add foreign keys to ms_menus
        Schema::table('ms_menus', function (Blueprint $table) {
            $table->foreign('created_by')->references('user_id')->on('ms_users')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('updated_by')->references('user_id')->on('ms_users')
                ->onUpdate('restrict')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_role_menus');
        Schema::dropIfExists('ms_menus');
    }
};
