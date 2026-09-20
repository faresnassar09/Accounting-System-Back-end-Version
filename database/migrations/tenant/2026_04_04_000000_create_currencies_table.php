<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create currencies table
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('code', 3)->unique();
                $table->string('name', 100);
                $table->string('symbol', 10);
                $table->decimal('exchange_rate', 15, 6)->default(1.000000);
                $table->boolean('is_base')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed default global currencies
            DB::table('currencies')->insert([
                [
                    'code'          => 'USD',
                    'name'          => 'US Dollar',
                    'symbol'        => '$',
                    'exchange_rate' => 1.000000,
                    'is_base'       => true,
                    'is_active'     => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'code'          => 'EUR',
                    'name'          => 'Euro',
                    'symbol'        => '€',
                    'exchange_rate' => 1.080000,
                    'is_base'       => false,
                    'is_active'     => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'code'          => 'GBP',
                    'name'          => 'British Pound',
                    'symbol'        => '£',
                    'exchange_rate' => 1.280000,
                    'is_base'       => false,
                    'is_active'     => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'code'          => 'SAR',
                    'name'          => 'Saudi Riyal',
                    'symbol'        => 'ر.س',
                    'exchange_rate' => 0.266667,
                    'is_base'       => false,
                    'is_active'     => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'code'          => 'EGP',
                    'name'          => 'Egyptian Pound',
                    'symbol'        => 'ج.م',
                    'exchange_rate' => 0.020202,
                    'is_base'       => false,
                    'is_active'     => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'code'          => 'AED',
                    'name'          => 'UAE Dirham',
                    'symbol'        => 'د.إ',
                    'exchange_rate' => 0.272294,
                    'is_base'       => false,
                    'is_active'     => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
            ]);
        }

        // 2. Add multi-currency columns to journal_entries
        Schema::table('journal_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entries', 'currency_code')) {
                $table->string('currency_code', 3)->default('USD')->after('branch_id');
            }
            if (!Schema::hasColumn('journal_entries', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 6)->default(1.000000)->after('currency_code');
            }
        });

        // 3. Normalize roles and ensure standard permission sets
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roleRenames = [
            'super_administrator'        => 'super_admin',
            'finance_manager_controller' => 'finance_manager',
            'general_accountant'         => 'accountant',
            'financial_auditor_read_only'=> 'auditor',
        ];

        foreach ($roleRenames as $oldName => $newName) {
            $existingOld = Role::where('name', $oldName)->where('guard_name', 'admin')->first();
            $existingNew = Role::where('name', $newName)->where('guard_name', 'admin')->first();

            if ($existingOld && !$existingNew) {
                $existingOld->update(['name' => $newName]);
            } elseif ($existingOld && $existingNew) {
                DB::table('model_has_roles')->where('role_id', $existingOld->id)->update(['role_id' => $existingNew->id]);
                $existingOld->delete();
            }
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
        $superAdmin->syncPermissions(Permission::where('guard_name', 'admin')->get());

        $financeManager = Role::firstOrCreate(['name' => 'finance_manager', 'guard_name' => 'admin']);
        $financeManager->syncPermissions([
            'view_reports',
            'create_journal_entries',
            'reverse_journal_entries',
            'manage_financial_closing',
            'manage_budgets',
            'manage_currencies',
        ]);

        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'admin']);
        $accountant->syncPermissions([
            'view_reports',
            'create_journal_entries',
        ]);

        $auditor = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'admin']);
        $auditor->syncPermissions([
            'view_reports',
        ]);
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entries', 'exchange_rate')) {
                $table->dropColumn('exchange_rate');
            }
            if (Schema::hasColumn('journal_entries', 'currency_code')) {
                $table->dropColumn('currency_code');
            }
        });

        Schema::dropIfExists('currencies');
    }
};
