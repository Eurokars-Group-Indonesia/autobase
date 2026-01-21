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
        // Update ms_permissions - ubah created_by menjadi NOT NULL dan tambah indexes
        Schema::table('ms_permissions', function (Blueprint $table) {
            // Ubah kolom created_by menjadi NOT NULL
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            
            // Ubah created_date menjadi datetime dengan default CURRENT_TIMESTAMP
            $table->dropColumn('created_date');
        });
        
        Schema::table('ms_permissions', function (Blueprint $table) {
            $table->dateTime('created_date')->nullable()->useCurrent()->after('created_by');
        });
        
        // Tambah indexes jika belum ada
        if (!$this->hasIndex('ms_permissions', 'ms_permissions_created_by_index')) {
            Schema::table('ms_permissions', function (Blueprint $table) {
                $table->index('created_by');
            });
        }
        if (!$this->hasIndex('ms_permissions', 'ms_permissions_updated_by_index')) {
            Schema::table('ms_permissions', function (Blueprint $table) {
                $table->index('updated_by');
            });
        }
        if (!$this->hasIndex('ms_permissions', 'ms_permissions_is_active_index')) {
            Schema::table('ms_permissions', function (Blueprint $table) {
                $table->index('is_active');
            });
        }

        // Update ms_role - ubah created_by menjadi NOT NULL dan tambah indexes
        Schema::table('ms_role', function (Blueprint $table) {
            // Ubah kolom created_by menjadi NOT NULL
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
        });
        
        // Tambah indexes jika belum ada
        if (!$this->hasIndex('ms_role', 'ms_role_created_by_index')) {
            Schema::table('ms_role', function (Blueprint $table) {
                $table->index('created_by');
            });
        }
        if (!$this->hasIndex('ms_role', 'ms_role_updated_by_index')) {
            Schema::table('ms_role', function (Blueprint $table) {
                $table->index('updated_by');
            });
        }
        if (!$this->hasIndex('ms_role', 'ms_role_is_active_index')) {
            Schema::table('ms_role', function (Blueprint $table) {
                $table->index('is_active');
            });
        }

        // Update ms_role_permissions - ubah created_by menjadi NOT NULL dan tambah indexes
        Schema::table('ms_role_permissions', function (Blueprint $table) {
            // Ubah kolom created_by menjadi NOT NULL
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            
            // Ubah created_date menjadi datetime dengan default CURRENT_TIMESTAMP
            $table->dropColumn('created_date');
        });
        
        Schema::table('ms_role_permissions', function (Blueprint $table) {
            $table->dateTime('created_date')->nullable()->useCurrent()->after('created_by');
        });
        
        // Tambah indexes jika belum ada
        if (!$this->hasIndex('ms_role_permissions', 'ms_role_permissions_created_by_index')) {
            Schema::table('ms_role_permissions', function (Blueprint $table) {
                $table->index('created_by');
            });
        }
        if (!$this->hasIndex('ms_role_permissions', 'ms_role_permissions_updated_by_index')) {
            Schema::table('ms_role_permissions', function (Blueprint $table) {
                $table->index('updated_by');
            });
        }
        if (!$this->hasIndex('ms_role_permissions', 'ms_role_permissions_is_active_index')) {
            Schema::table('ms_role_permissions', function (Blueprint $table) {
                $table->index('is_active');
            });
        }

        // Update ms_user_roles - ubah created_by menjadi NOT NULL dan tambah index is_active
        Schema::table('ms_user_roles', function (Blueprint $table) {
            // Ubah kolom created_by menjadi NOT NULL
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            
            // Ubah created_date menjadi datetime dengan default CURRENT_TIMESTAMP
            $table->dropColumn('created_date');
        });
        
        Schema::table('ms_user_roles', function (Blueprint $table) {
            $table->dateTime('created_date')->nullable()->useCurrent()->after('created_by');
        });
        
        // Tambah index is_active jika belum ada
        if (!$this->hasIndex('ms_user_roles', 'ms_user_roles_is_active_index')) {
            Schema::table('ms_user_roles', function (Blueprint $table) {
                $table->index('is_active');
            });
        }

        // Update ms_menus - ubah created_by menjadi NOT NULL, menu_order unsigned, dan tambah indexes
        Schema::table('ms_menus', function (Blueprint $table) {
            // Ubah kolom created_by menjadi NOT NULL
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            
            // Ubah menu_order menjadi unsigned integer
            $table->unsignedInteger('menu_order')->default(0)->change();
            
            // Ubah created_date menjadi datetime dengan default CURRENT_TIMESTAMP
            $table->dropColumn('created_date');
        });
        
        Schema::table('ms_menus', function (Blueprint $table) {
            $table->dateTime('created_date')->nullable()->useCurrent()->after('created_by');
        });
        
        // Tambah indexes jika belum ada
        if (!$this->hasIndex('ms_menus', 'ms_menus_created_by_index')) {
            Schema::table('ms_menus', function (Blueprint $table) {
                $table->index('created_by');
            });
        }
        if (!$this->hasIndex('ms_menus', 'ms_menus_updated_by_index')) {
            Schema::table('ms_menus', function (Blueprint $table) {
                $table->index('updated_by');
            });
        }
        if (!$this->hasIndex('ms_menus', 'ms_menus_is_active_index')) {
            Schema::table('ms_menus', function (Blueprint $table) {
                $table->index('is_active');
            });
        }

        // Update ms_role_menus - ubah created_by menjadi NOT NULL dan tambah indexes
        Schema::table('ms_role_menus', function (Blueprint $table) {
            // Ubah kolom created_by menjadi NOT NULL
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            
            // Ubah created_date menjadi datetime dengan default CURRENT_TIMESTAMP
            $table->dropColumn('created_date');
        });
        
        Schema::table('ms_role_menus', function (Blueprint $table) {
            $table->dateTime('created_date')->nullable()->useCurrent()->after('created_by');
        });
        
        // Tambah indexes jika belum ada
        if (!$this->hasIndex('ms_role_menus', 'ms_role_menus_menu_id_index')) {
            Schema::table('ms_role_menus', function (Blueprint $table) {
                $table->index('menu_id');
            });
        }
        if (!$this->hasIndex('ms_role_menus', 'ms_role_menus_role_id_index')) {
            Schema::table('ms_role_menus', function (Blueprint $table) {
                $table->index('role_id');
            });
        }
        if (!$this->hasIndex('ms_role_menus', 'ms_role_menus_is_active_index')) {
            Schema::table('ms_role_menus', function (Blueprint $table) {
                $table->index('is_active');
            });
        }
    }

    /**
     * Helper method to check if index exists
     */
    private function hasIndex($table, $indexName)
    {
        $indexes = \DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback perubahan
        Schema::table('ms_permissions', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['is_active']);
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('ms_role', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['is_active']);
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('ms_role_permissions', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['is_active']);
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('ms_user_roles', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::table('ms_menus', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['is_active']);
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->integer('menu_order')->default(0)->change();
        });

        Schema::table('ms_role_menus', function (Blueprint $table) {
            $table->dropIndex(['menu_id']);
            $table->dropIndex(['role_id']);
            $table->dropIndex(['is_active']);
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
    }
};
