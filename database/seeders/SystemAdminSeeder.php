<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if system admin already exists
        $existingAdmin = User::where('type', UserType::SYSTEM_ADMIN)->first();
        
        if ($existingAdmin) {
            $this->command->warn('System Admin already exists: ' . $existingAdmin->email);
            return;
        }

        // Create System Admin role if it doesn't exist
        $role = \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'System Admin'],
            ['guard_name' => 'web']
        );

        // Create System Admin account
        $systemAdmin = User::create([
            'firstname' => 'System',
            'middlename' => '',
            'lastname' => 'Administrator',
            'email' => 'sysadmin@smarthr.com',
            'username' => 'sysadmin',
            'type' => UserType::SYSTEM_ADMIN,
            'password' => Hash::make('SysAdmin@2025'),
            'is_active' => true,
            'created_by' => null,
        ]);

        // Assign System Admin role
        $systemAdmin->assignRole($role);

        $this->command->info('System Admin role and account created successfully!');
        $this->command->info('Email: sysadmin@smarthr.com');
        $this->command->info('Username: sysadmin');
        $this->command->info('Password: SysAdmin@2025');
        $this->command->warn('Please change the password after first login!');
    }
}
