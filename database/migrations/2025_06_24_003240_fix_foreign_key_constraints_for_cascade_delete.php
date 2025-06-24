<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ✅ DISABLE foreign key checks temporarily untuk menghindari error saat mengubah constraint
        Schema::disableForeignKeyConstraints();

        // ================================================================
        // 1. FIX REVENUES TABLE - Primary foreign keys dengan cascade delete
        // ================================================================
        if (Schema::hasTable('revenues')) {
            Schema::table('revenues', function (Blueprint $table) {
                // Drop existing foreign keys jika ada
                try {
                    $table->dropForeign(['account_manager_id']);
                } catch (Exception $e) {
                    // Ignore jika foreign key tidak ada
                }
                
                try {
                    $table->dropForeign(['corporate_customer_id']);
                } catch (Exception $e) {
                    // Ignore jika foreign key tidak ada
                }
                
                try {
                    $table->dropForeign(['divisi_id']);
                } catch (Exception $e) {
                    // Ignore jika foreign key tidak ada
                }

                // ✅ ADD NEW foreign keys dengan CASCADE DELETE
                $table->foreign('account_manager_id')
                      ->references('id')
                      ->on('account_managers')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');

                $table->foreign('corporate_customer_id')
                      ->references('id')
                      ->on('corporate_customers')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');

                $table->foreign('divisi_id')
                      ->references('id')
                      ->on('divisi')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            });
        }

        // ================================================================
        // 2. FIX ACCOUNT_MANAGERS TABLE - Witel dan Regional relationships
        // ================================================================
        if (Schema::hasTable('account_managers')) {
            Schema::table('account_managers', function (Blueprint $table) {
                // Drop existing foreign keys
                try {
                    $table->dropForeign(['witel_id']);
                } catch (Exception $e) {
                    // Ignore jika foreign key tidak ada
                }
                
                try {
                    $table->dropForeign(['regional_id']);
                } catch (Exception $e) {
                    // Ignore jika foreign key tidak ada
                }

                // ✅ ADD NEW foreign keys - SET NULL untuk witel dan regional
                // Karena jika witel/regional dihapus, account manager masih bisa exist
                $table->foreign('witel_id')
                      ->references('id')
                      ->on('witel')
                      ->onDelete('set null')
                      ->onUpdate('cascade');

                $table->foreign('regional_id')
                      ->references('id')
                      ->on('regional')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            });
        }

        // ================================================================
        // 3. FIX USERS TABLE - Account Manager, Witel, Regional relationships
        // ================================================================
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Drop existing foreign keys
                try {
                    $table->dropForeign(['account_manager_id']);
                } catch (Exception $e) {
                    // Ignore jika foreign key tidak ada
                }
                
                try {
                    $table->dropForeign(['witel_id']);
                } catch (Exception $e) {
                    // Ignore jika foreign key tidak ada
                }
                
                try {
                    $table->dropForeign(['regional_id']);
                } catch (Exception $e) {
                    // Ignore jika foreign key tidak ada
                }

                // ✅ ADD NEW foreign keys - SET NULL untuk semua relasi users
                // Karena user bisa tetap exist meskipun account manager/witel/regional dihapus
                $table->foreign('account_manager_id')
                      ->references('id')
                      ->on('account_managers')
                      ->onDelete('set null')
                      ->onUpdate('cascade');

                $table->foreign('witel_id')
                      ->references('id')
                      ->on('witel')
                      ->onDelete('set null')
                      ->onUpdate('cascade');

                $table->foreign('regional_id')
                      ->references('id')
                      ->on('regional')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            });
        }

        // ================================================================
        // 4. FIX PIVOT TABLE - account_manager_divisi (Many-to-Many)
        // ================================================================
        if (Schema::hasTable('account_manager_divisi')) {
            Schema::table('account_manager_divisi', function (Blueprint $table) {
                // Drop existing foreign keys
                try {
                    $table->dropForeign(['account_manager_id']);
                } catch (Exception $e) {
                    // Ignore jika foreign key tidak ada
                }
                
                try {
                    $table->dropForeign(['divisi_id']);
                } catch (Exception $e) {
                    // Ignore jika foreign key tidak ada
                }

                // ✅ ADD NEW foreign keys dengan CASCADE DELETE untuk pivot table
                $table->foreign('account_manager_id')
                      ->references('id')
                      ->on('account_managers')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');

                $table->foreign('divisi_id')
                      ->references('id')
                      ->on('divisi')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            });
        }

        // ================================================================
        // 5. FIX PIVOT TABLE - account_manager_customer (Many-to-Many)
        // ================================================================
        if (Schema::hasTable('account_manager_customer')) {
            Schema::table('account_manager_customer', function (Blueprint $table) {
                // Drop existing foreign keys
                try {
                    $table->dropForeign(['account_manager_id']);
                } catch (Exception $e) {
                    // Ignore jika foreign key tidak ada
                }
                
                try {
                    $table->dropForeign(['corporate_customer_id']);
                } catch (Exception $e) {
                    // Ignore jika foreign key tidak ada
                }

                // ✅ ADD NEW foreign keys dengan CASCADE DELETE untuk pivot table
                $table->foreign('account_manager_id')
                      ->references('id')
                      ->on('account_managers')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');

                $table->foreign('corporate_customer_id')
                      ->references('id')
                      ->on('corporate_customers')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            });
        }

        // ================================================================
        // 6. CREATE MISSING INDEXES untuk performa yang lebih baik
        // ================================================================
        
        // Index untuk revenues table
        if (Schema::hasTable('revenues')) {
            Schema::table('revenues', function (Blueprint $table) {
                // Composite index untuk query yang sering digunakan
                if (!$this->indexExists('revenues', 'revenues_bulan_index')) {
                    $table->index('bulan', 'revenues_bulan_index');
                }
                
                if (!$this->indexExists('revenues', 'revenues_composite_index')) {
                    $table->index(['account_manager_id', 'corporate_customer_id', 'bulan'], 'revenues_composite_index');
                }
                
                if (!$this->indexExists('revenues', 'revenues_divisi_bulan_index')) {
                    $table->index(['divisi_id', 'bulan'], 'revenues_divisi_bulan_index');
                }
            });
        }

        // Index untuk account_managers table
        if (Schema::hasTable('account_managers')) {
            Schema::table('account_managers', function (Blueprint $table) {
                if (!$this->indexExists('account_managers', 'account_managers_nama_index')) {
                    $table->index('nama', 'account_managers_nama_index');
                }
                
                if (!$this->indexExists('account_managers', 'account_managers_nik_index')) {
                    $table->index('nik', 'account_managers_nik_index');
                }
                
                if (!$this->indexExists('account_managers', 'account_managers_witel_regional_index')) {
                    $table->index(['witel_id', 'regional_id'], 'account_managers_witel_regional_index');
                }
            });
        }

        // Index untuk corporate_customers table
        if (Schema::hasTable('corporate_customers')) {
            Schema::table('corporate_customers', function (Blueprint $table) {
                if (!$this->indexExists('corporate_customers', 'corporate_customers_nama_index')) {
                    $table->index('nama', 'corporate_customers_nama_index');
                }
                
                if (!$this->indexExists('corporate_customers', 'corporate_customers_nipnas_index')) {
                    $table->index('nipnas', 'corporate_customers_nipnas_index');
                }
            });
        }

        // ✅ ENABLE foreign key checks kembali
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // ✅ DISABLE foreign key checks
        Schema::disableForeignKeyConstraints();

        // ================================================================
        // ROLLBACK: Drop all foreign keys yang ditambahkan
        // ================================================================
        
        // 1. Drop foreign keys dari revenues table
        if (Schema::hasTable('revenues')) {
            Schema::table('revenues', function (Blueprint $table) {
                $table->dropForeign(['account_manager_id']);
                $table->dropForeign(['corporate_customer_id']);
                $table->dropForeign(['divisi_id']);
                
                // Drop indexes
                $table->dropIndex('revenues_bulan_index');
                $table->dropIndex('revenues_composite_index');
                $table->dropIndex('revenues_divisi_bulan_index');
            });
        }

        // 2. Drop foreign keys dari account_managers table
        if (Schema::hasTable('account_managers')) {
            Schema::table('account_managers', function (Blueprint $table) {
                $table->dropForeign(['witel_id']);
                $table->dropForeign(['regional_id']);
                
                // Drop indexes
                $table->dropIndex('account_managers_nama_index');
                $table->dropIndex('account_managers_nik_index');
                $table->dropIndex('account_managers_witel_regional_index');
            });
        }

        // 3. Drop foreign keys dari users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['account_manager_id']);
                $table->dropForeign(['witel_id']);
                $table->dropForeign(['regional_id']);
            });
        }

        // 4. Drop foreign keys dari account_manager_divisi table
        if (Schema::hasTable('account_manager_divisi')) {
            Schema::table('account_manager_divisi', function (Blueprint $table) {
                $table->dropForeign(['account_manager_id']);
                $table->dropForeign(['divisi_id']);
            });
        }

        // 5. Drop foreign keys dari account_manager_customer table
        if (Schema::hasTable('account_manager_customer')) {
            Schema::table('account_manager_customer', function (Blueprint $table) {
                $table->dropForeign(['account_manager_id']);
                $table->dropForeign(['corporate_customer_id']);
            });
        }

        // 6. Drop indexes dari corporate_customers table
        if (Schema::hasTable('corporate_customers')) {
            Schema::table('corporate_customers', function (Blueprint $table) {
                $table->dropIndex('corporate_customers_nama_index');
                $table->dropIndex('corporate_customers_nipnas_index');
            });
        }

        // ✅ ENABLE foreign key checks kembali
        Schema::enableForeignKeyConstraints();
    }

    /**
     * ✅ HELPER: Check if index exists untuk menghindari error duplicate index
     */
    private function indexExists($table, $indexName)
    {
        $indexes = Schema::getConnection()
                        ->getDoctrineSchemaManager()
                        ->listTableIndexes($table);
        
        return array_key_exists($indexName, $indexes);
    }
};