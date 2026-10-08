<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_reports'             => 'View Financial Reports',
            'create_journal_entries'   => 'Create Manual Journal Entries',
            'reverse_journal_entries'  => 'Reverse and Void Journal Entries',
            'manage_financial_closing' => 'Manage Financial Year-End Closing',
            'manage_budgets'           => 'Manage Budgets and Allocations',
            'manage_currencies'        => 'Manage Currencies and Exchange Rates',
        ];

        foreach ($permissions as $permName => $label) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'admin'],
                ['lable' => $label]
            );
        }

        // 1. Super Admin Role
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'admin'],
            ['lable' => 'Super Administrator']
        );
        $superAdmin->syncPermissions(Permission::where('guard_name', 'admin')->get());

        // 2. Finance Manager Role
        $financeManager = Role::firstOrCreate(
            ['name' => 'finance_manager', 'guard_name' => 'admin'],
            ['lable' => 'Finance Manager / Controller']
        );
        $financeManager->syncPermissions([
            'view_reports',
            'create_journal_entries',
            'reverse_journal_entries',
            'manage_financial_closing',
            'manage_budgets',
            'manage_currencies',
        ]);

        // 3. Accountant Role
        $accountant = Role::firstOrCreate(
            ['name' => 'accountant', 'guard_name' => 'admin'],
            ['lable' => 'General Accountant']
        );
        $accountant->syncPermissions([
            'view_reports',
            'create_journal_entries',
        ]);

        // 4. Auditor Role
        $auditor = Role::firstOrCreate(
            ['name' => 'auditor', 'guard_name' => 'admin'],
            ['lable' => 'Financial Auditor (Read-Only)']
        );
        $auditor->syncPermissions([
            'view_reports',
        ]);
    }

    public function down(): void
    {
        // Keep permissions intact
    }
};
