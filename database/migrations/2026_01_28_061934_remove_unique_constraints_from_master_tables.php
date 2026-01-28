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
        // Remove unique constraint from ms_brand.brand_code
        Schema::table('ms_brand', function (Blueprint $table) {
            $table->dropUnique(['brand_code']);
        });

        // Remove unique constraint from ms_dealers.dealer_code
        Schema::table('ms_dealers', function (Blueprint $table) {
            $table->dropUnique(['dealer_code']);
        });

        // Remove unique constraint from ms_menus.menu_code
        Schema::table('ms_menus', function (Blueprint $table) {
            $table->dropUnique(['menu_code']);
        });

        // Remove unique constraint from ms_permissions.permission_code
        Schema::table('ms_permissions', function (Blueprint $table) {
            $table->dropUnique(['permission_code']);
        });

        // Remove unique constraint from ms_role.role_code
        Schema::table('ms_role', function (Blueprint $table) {
            $table->dropUnique(['role_code']);
        });

        // Remove unique constraint from ms_role_menus (role_id, menu_id combination)
        Schema::table('ms_role_menus', function (Blueprint $table) {
            $table->dropUnique(['role_id', 'menu_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back unique constraint to ms_brand.brand_code
        Schema::table('ms_brand', function (Blueprint $table) {
            $table->unique('brand_code');
        });

        // Add back unique constraint to ms_dealers.dealer_code
        Schema::table('ms_dealers', function (Blueprint $table) {
            $table->unique('dealer_code');
        });

        // Add back unique constraint to ms_menus.menu_code
        Schema::table('ms_menus', function (Blueprint $table) {
            $table->unique('menu_code');
        });

        // Add back unique constraint to ms_permissions.permission_code
        Schema::table('ms_permissions', function (Blueprint $table) {
            $table->unique('permission_code');
        });

        // Add back unique constraint to ms_role.role_code
        Schema::table('ms_role', function (Blueprint $table) {
            $table->unique('role_code');
        });

        // Add back unique constraint to ms_role_menus (role_id, menu_id combination)
        Schema::table('ms_role_menus', function (Blueprint $table) {
            $table->unique(['role_id', 'menu_id']);
        });
    }
};
