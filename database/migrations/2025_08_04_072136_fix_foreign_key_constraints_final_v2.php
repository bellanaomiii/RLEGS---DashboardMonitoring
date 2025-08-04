<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // ================================================================
            // 1. MAKE SURE COLUMNS ARE NULLABLE FIRST
            // ================================================================

            // Make witel_id nullable in account_managers
            if (Schema::hasColumn('account_managers', 'witel_id')) {
                Schema::table('account_managers', function (Blueprint $table) {
                    $table->unsignedBigInteger('witel_id')->nullable()->change();
                });
            }

            // Make regional_id nullable in account_managers
            if (Schema::hasColumn('account_managers', 'regional_id')) {
                Schema::table('account_managers', function (Blueprint $table) {
                    $table->unsignedBigInteger('regional_id')->nullable()->change();
                });
            }

            // Make nullable columns in users table
            if (Schema::hasTable('users')) {
                Schema::table('users', function (Blueprint $table) {
                    if (Schema::hasColumn('users', 'account_manager_id')) {
                        $table->unsignedBigInteger('account_manager_id')->nullable()->change();
                    }
                    if (Schema::hasColumn('users', 'witel_id')) {
                        $table->unsignedBigInteger('witel_id')->nullable()->change();
                    }
                    if (Schema::hasColumn('users', 'regional_id')) {
                        $table->unsignedBigInteger('regional_id')->nullable()->change();
                    }
                });
            }

            // ================================================================
            // 2. CLEAN UP INVALID DATA
            // ================================================================

            // Remove invalid references in account_managers
            if (Schema::hasTable('account_managers') && Schema::hasTable('witel')) {
                DB::statement("
                    UPDATE account_managers
                    SET witel_id = NULL
                    WHERE witel_id IS NOT NULL
                    AND witel_id NOT IN (SELECT id FROM witel)
                ");
            }

            if (Schema::hasTable('account_managers') && Schema::hasTable('regional')) {
                DB::statement("
                    UPDATE account_managers
                    SET regional_id = NULL
                    WHERE regional_id IS NOT NULL
                    AND regional_id NOT IN (SELECT id FROM regional)
                ");
            }

            // Remove invalid references in users
            if (Schema::hasTable('users') && Schema::hasTable('account_managers')) {
                DB::statement("
                    UPDATE users
                    SET account_manager_id = NULL
                    WHERE account_manager_id IS NOT NULL
                    AND account_manager_id NOT IN (SELECT id FROM account_managers)
                ");
            }

            if (Schema::hasTable('users') && Schema::hasTable('witel')) {
                DB::statement("
                    UPDATE users
                    SET witel_id = NULL
                    WHERE witel_id IS NOT NULL
                    AND witel_id NOT IN (SELECT id FROM witel)
                ");
            }

            if (Schema::hasTable('users') && Schema::hasTable('regional')) {
                DB::statement("
                    UPDATE users
                    SET regional_id = NULL
                    WHERE regional_id IS NOT NULL
                    AND regional_id NOT IN (SELECT id FROM regional)
                ");
            }

            // ================================================================
            // 3. ADD FOREIGN KEY CONSTRAINTS SAFELY
            // ================================================================

            // account_managers foreign keys
            if (Schema::hasTable('account_managers')) {
                Schema::table('account_managers', function (Blueprint $table) {
                    // witel_id foreign key
                    if (Schema::hasTable('witel') && !$this->foreignKeyExists('account_managers', 'witel_id')) {
                        $table->foreign('witel_id', 'fk_account_managers_witel')
                              ->references('id')
                              ->on('witel')
                              ->onDelete('set null')
                              ->onUpdate('cascade');
                    }

                    // regional_id foreign key
                    if (Schema::hasTable('regional') && !$this->foreignKeyExists('account_managers', 'regional_id')) {
                        $table->foreign('regional_id', 'fk_account_managers_regional')
                              ->references('id')
                              ->on('regional')
                              ->onDelete('set null')
                              ->onUpdate('cascade');
                    }
                });
            }

            // users foreign keys
            if (Schema::hasTable('users')) {
                Schema::table('users', function (Blueprint $table) {
                    // account_manager_id foreign key
                    if (Schema::hasTable('account_managers') && !$this->foreignKeyExists('users', 'account_manager_id')) {
                        $table->foreign('account_manager_id', 'fk_users_account_manager')
                              ->references('id')
                              ->on('account_managers')
                              ->onDelete('set null')
                              ->onUpdate('cascade');
                    }

                    // witel_id foreign key
                    if (Schema::hasTable('witel') && !$this->foreignKeyExists('users', 'witel_id')) {
                        $table->foreign('witel_id', 'fk_users_witel')
                              ->references('id')
                              ->on('witel')
                              ->onDelete('set null')
                              ->onUpdate('cascade');
                    }

                    // regional_id foreign key
                    if (Schema::hasTable('regional') && !$this->foreignKeyExists('users', 'regional_id')) {
                        $table->foreign('regional_id', 'fk_users_regional')
                              ->references('id')
                              ->on('regional')
                              ->onDelete('set null')
                              ->onUpdate('cascade');
                    }
                });
            }

            // revenues foreign keys (CASCADE DELETE)
            if (Schema::hasTable('revenues')) {
                Schema::table('revenues', function (Blueprint $table) {
                    if (!$this->foreignKeyExists('revenues', 'account_manager_id')) {
                        $table->foreign('account_manager_id', 'fk_revenues_account_manager')
                              ->references('id')
                              ->on('account_managers')
                              ->onDelete('cascade')
                              ->onUpdate('cascade');
                    }

                    if (!$this->foreignKeyExists('revenues', 'corporate_customer_id')) {
                        $table->foreign('corporate_customer_id', 'fk_revenues_corporate_customer')
                              ->references('id')
                              ->on('corporate_customers')
                              ->onDelete('cascade')
                              ->onUpdate('cascade');
                    }

                    if (Schema::hasTable('divisi') && !$this->foreignKeyExists('revenues', 'divisi_id')) {
                        $table->foreign('divisi_id', 'fk_revenues_divisi')
                              ->references('id')
                              ->on('divisi')
                              ->onDelete('cascade')
                              ->onUpdate('cascade');
                    }
                });
            }

            // pivot tables foreign keys
            if (Schema::hasTable('account_manager_divisi')) {
                Schema::table('account_manager_divisi', function (Blueprint $table) {
                    if (!$this->foreignKeyExists('account_manager_divisi', 'account_manager_id')) {
                        $table->foreign('account_manager_id', 'fk_am_divisi_account_manager')
                              ->references('id')
                              ->on('account_managers')
                              ->onDelete('cascade')
                              ->onUpdate('cascade');
                    }

                    if (!$this->foreignKeyExists('account_manager_divisi', 'divisi_id')) {
                        $table->foreign('divisi_id', 'fk_am_divisi_divisi')
                              ->references('id')
                              ->on('divisi')
                              ->onDelete('cascade')
                              ->onUpdate('cascade');
                    }
                });
            }

            if (Schema::hasTable('account_manager_customer')) {
                Schema::table('account_manager_customer', function (Blueprint $table) {
                    if (!$this->foreignKeyExists('account_manager_customer', 'account_manager_id')) {
                        $table->foreign('account_manager_id', 'fk_am_customer_account_manager')
                              ->references('id')
                              ->on('account_managers')
                              ->onDelete('cascade')
                              ->onUpdate('cascade');
                    }

                    if (!$this->foreignKeyExists('account_manager_customer', 'corporate_customer_id')) {
                        $table->foreign('corporate_customer_id', 'fk_am_customer_corporate_customer')
                              ->references('id')
                              ->on('corporate_customers')
                              ->onDelete('cascade')
                              ->onUpdate('cascade');
                    }
                });
            }

            echo "✅ All foreign keys created successfully!\n";

        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            throw $e;
        } finally {
            // Always re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Drop all foreign keys with explicit names
            $foreignKeys = [
                'account_managers' => ['fk_account_managers_witel', 'fk_account_managers_regional'],
                'users' => ['fk_users_account_manager', 'fk_users_witel', 'fk_users_regional'],
                'revenues' => ['fk_revenues_account_manager', 'fk_revenues_corporate_customer', 'fk_revenues_divisi'],
                'account_manager_divisi' => ['fk_am_divisi_account_manager', 'fk_am_divisi_divisi'],
                'account_manager_customer' => ['fk_am_customer_account_manager', 'fk_am_customer_corporate_customer']
            ];

            foreach ($foreignKeys as $tableName => $constraints) {
                if (Schema::hasTable($tableName)) {
                    foreach ($constraints as $constraint) {
                        try {
                            DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$constraint}`");
                            echo "✅ Dropped foreign key: {$constraint}\n";
                        } catch (\Exception $e) {
                            echo "⚠️ Could not drop foreign key {$constraint}: " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Check if foreign key exists
     */
    private function foreignKeyExists($tableName, $columnName)
    {
        try {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.key_column_usage
                WHERE table_name = ?
                AND column_name = ?
                AND referenced_table_name IS NOT NULL
                AND table_schema = DATABASE()
            ", [$tableName, $columnName]);

            return count($foreignKeys) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};