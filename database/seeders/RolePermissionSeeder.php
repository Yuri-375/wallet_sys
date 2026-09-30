<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [

            // Dashboard
            'view dashboard',

            // User Management
            'view users',
            'create users',
            'update users',
            'delete users',

            // KYC
            'upload kyc documents',
            'view kyc',
            'approve kyc',
            'reject kyc',

            // Wallet
            'view wallet',
            'freeze wallet',
            'unfreeze wallet',

            // Transactions
            'view transactions',
            'view all transactions',
            'create transfer',

            // Top Up
            'create top up',
            'view top ups',
            'verify top up',
            'reject top up',

            // Bills
            'pay bills',
            'view bill payments',
            'verify bill payments',

            // Fraud
            'view fraud reports',
            'investigate fraud',
            'resolve fraud',

            // Statements
            'view statements',
            // 'generate statement',

            // Agents
            'manage agents',
        ];

        /*
        |--------------------------------------------------------------------------
        | Create permissions and roles for BOTH guards
        |--------------------------------------------------------------------------
        */

        foreach (['web', 'api'] as $guard) {

            /*
            |--------------------------------------------------------------------------
            | Create Permissions
            |--------------------------------------------------------------------------
            */

            foreach ($permissions as $permissionName) {

                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => $guard,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Create Roles
            |--------------------------------------------------------------------------
            */

            $admin = Role::firstOrCreate([
                'name' => 'admin',
                'guard_name' => $guard,
            ]);

            $agent = Role::firstOrCreate([
                'name' => 'agent',
                'guard_name' => $guard,
            ]);

            $user = Role::firstOrCreate([
                'name' => 'user',
                'guard_name' => $guard,
            ]);

            /*
            |--------------------------------------------------------------------------
            | ADMIN PERMISSIONS
            |--------------------------------------------------------------------------
            */

            $admin->syncPermissions([

                // Dashboard
                'view dashboard',

                // User Management
                'view users',
                'create users',
                'update users',
                'delete users',

                // KYC
                'view kyc',
                'approve kyc',
                'reject kyc',

                // Wallet
                'view wallet',
                'freeze wallet',
                'unfreeze wallet',

                // Transactions
                'view transactions',
                'view all transactions',
                'create transfer',

                // Top Up
                'create top up',
                'view top ups',
                'verify top up',
                'reject top up',

                // Bills
                'pay bills',
                'view bill payments',
                'verify bill payments',

                // Fraud
                'view fraud reports',
                'investigate fraud',
                'resolve fraud',

                // Statements
                'view statements',

                // Agents
                'manage agents',
            ]);

            /*
            |--------------------------------------------------------------------------
            | AGENT PERMISSIONS
            |--------------------------------------------------------------------------
            */

            $agent->syncPermissions([

                // Dashboard
                'view dashboard',

                // Users
                'view users',

                // KYC
                'view kyc',
                'approve kyc',
                'reject kyc',

                // Wallet
                //'view wallet',

                // Transactions
                'view transactions',

                // Top Up
                'create top up',
                'view top ups',
                'verify top up',
                'reject top up',

                // Bills
                'view bill payments',
                'verify bill payments',

                // Statements
                'view statements',
            ]);

            /*
            |--------------------------------------------------------------------------
            | USER PERMISSIONS
            |--------------------------------------------------------------------------
            */

            $user->syncPermissions([

                // Dashboard
                'view dashboard',

                // KYC
                'view kyc',
                'upload kyc documents',

                // Wallet
                'view wallet',

                // Transactions
                'view transactions',
                'create transfer',

                // Top Up
                'view top ups',

                // Bills
                'pay bills',
                'view bill payments',

                // Statements
                'view statements',
            ]);
        }
    }
}
