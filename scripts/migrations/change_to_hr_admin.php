<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find both admin users
$admin = App\Models\User::where('email', 'admin@smarthr.com')->first();
$admin1 = App\Models\User::where('email', 'admin1@smarthr.com')->first();

$users = collect([$admin, $admin1])->filter();

foreach ($users as $user) {
    echo "User: {$user->fullname}\n";
    echo "Email: {$user->email}\n";
    echo "Current Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    
    // Remove any existing roles
    $user->syncRoles([]);
    
    // Assign HR Admin role
    $hrAdminRole = Spatie\Permission\Models\Role::firstOrCreate(['name' => 'HR Admin']);
    $user->assignRole($hrAdminRole);
    
    echo "✓ Changed to 'HR Admin' role successfully!\n";
    echo "Updated Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "----------------------------------------\n\n";
}

echo "Both users now have HR Admin role.\n";
