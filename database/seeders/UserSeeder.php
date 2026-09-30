<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'api',
        ]);

        $agentRole = Role::firstOrCreate([
            'name' => 'agent',
            'guard_name' => 'api',
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api',
        ]);

        // Create 2 Users
        $users = [
            [
                'name' => 'User One',
                'phone' => '09111111111',
                'email' => 'user1@gmail.com',
            ],
            [
                'name' => 'User Two',
                'phone' => '09222222222',
                'email' => 'user2@gmail.com',
            ],
        ];

        foreach ($users as $data) {

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => Hash::make('user123'),
                    'status' => 'active',
                ]
            );

            $user->syncRoles([$userRole]);
        }

        // Create 2 Agents
        $agents = [
            [
                'name' => 'Agent One',
                'phone' => '09333333333',
                'email' => 'agent1@gmail.com',
            ],
            [
                'name' => 'Agent Two',
                'phone' => '09444444444',
                'email' => 'agent2@gmail.com',
            ],
        ];

        foreach ($agents as $data) {

            $agent = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => Hash::make('agent123'),
                    'kyc_status' => 'approved',
                    'status' => 'active',
                ]
            );

            $agent->syncRoles([$agentRole]);
        }

        // Create 2 Admins

        $admins = [
            [
                'name' => 'Admin One',
                'phone' => '09555555555',
                'email' => 'admin1@gmail.com',
            ],
            [
                'name' => 'Admin Two',
                'phone' => '09666666666',
                'email' => 'admin2@gmail.com',
            ],
        ];

        foreach ($admins as $data) {

            $admin = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => Hash::make('admin123'),
                    'kyc_status' => 'approved',
                    'status' => 'active',
                ]
            );

            $admin->syncRoles([$adminRole]);
        }
    }
}
