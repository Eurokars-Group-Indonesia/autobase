<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tx_header', function (Blueprint $table) {
            $table->string('account_company', 50)->nullable()->after('engine_no');
        });
    }

    public function down(): void
    {
        Schema::table('tx_header', function (Blueprint $table) {
            $table->dropColumn('account_company');
        });
    }
};
