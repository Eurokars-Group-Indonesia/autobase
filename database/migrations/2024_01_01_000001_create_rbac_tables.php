<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table ms_permissions
        Schema::create('ms_permissions', function (Blueprint $table) {
            $table->id('permission_id');
            $table->string('permission_code', 100)->unique();
            $table->string('permission_name', 150);
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_date')->nullable()->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('updated_date')->nullable();
            $table->char('unique_id', 36)->unique();
            $table->enum('is_active', ['0', '1'])->default('1')->nullable();

            // Indexes
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('is_active');
        });

        // Table ms_role
        Schema::create('ms_role', function (Blueprint $table) {
            $table->id('role_id');
            $table->string('role_code', 10)->unique();
            $table->string('role_name', 50);
            $table->string('role_description', 200);
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_date')->nullable()->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('updated_date')->nullable();
            $table->char('unique_id', 36)->unique();
            $table->enum('is_active', ['0', '1'])->default('1')->nullable();

            // Indexes
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('is_active');
        });

        // Table ms_role_permissions
        Schema::create('ms_role_permissions', function (Blueprint $table) {
            $table->id('role_permission_id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_date')->nullable()->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('updated_date')->nullable();
            $table->char('unique_id', 36)->unique();
            $table->enum('is_active', ['0', '1'])->default('1')->nullable();

            // Indexes
            $table->index('role_id');
            $table->index('permission_id');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('is_active');
            
            // Foreign keys
            $table->foreign('role_id')->references('role_id')->on('ms_role')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('permission_id')->references('permission_id')->on('ms_permissions')
                ->onUpdate('restrict')->onDelete('restrict');
        });

        // Table ms_user_roles
        Schema::create('ms_user_roles', function (Blueprint $table) {
            $table->id('user_role_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamp('assigned_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_date')->nullable()->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->dateTime('updated_date')->nullable();
            $table->char('unique_id', 36)->unique();
            $table->enum('is_active', ['0', '1'])->default('1')->nullable();

            // Indexes
            $table->index('user_id');
            $table->index('role_id');
            $table->index('is_active');
            
            // Foreign keys
            $table->foreign('user_id')->references('user_id')->on('ms_users')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('role_id')->references('role_id')->on('ms_role')
                ->onUpdate('restrict')->onDelete('restrict');
        });

        // Add foreign keys to ms_role
        Schema::table('ms_role', function (Blueprint $table) {
            $table->foreign('created_by')->references('user_id')->on('ms_users')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('updated_by')->references('user_id')->on('ms_users')
                ->onUpdate('restrict')->onDelete('restrict');
        });
        
        // Add foreign keys to ms_permissions
        Schema::table('ms_permissions', function (Blueprint $table) {
            $table->foreign('created_by')->references('user_id')->on('ms_users')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('updated_by')->references('user_id')->on('ms_users')
                ->onUpdate('restrict')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_user_roles');
        Schema::dropIfExists('ms_role_permissions');
        Schema::dropIfExists('ms_role');
        Schema::dropIfExists('ms_permissions');
    }
};
