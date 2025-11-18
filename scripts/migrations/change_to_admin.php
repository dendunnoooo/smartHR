<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find Wayne's user
$user = App\Models\User::where('firstname', 'Wayne')->where('lastname', 'Anave')->first();

if ($user) {
    echo "Current User: {$user->fullname}\n";
    echo "Current Email: {$user->email}\n";
    echo "Current Type: {$user->type->value}\n";
    echo "Current Roles: " . $user->roles->pluck('name')->implode(', ') . "\n\n";
    
    // Remove Super Admin role and assign Admin role
    $user->removeRole('Super Admin');
    $user->removeRole('HR Admin');
    
    // Create or get Admin role
    $adminRole = Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
    $user->assignRole($adminRole);
    
    echo "✓ Changed to 'Admin' role successfully!\n\n";
    
    echo "Updated Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "This will display as: HR STAFF\n";
} else {
    echo "User 'Wayne Anave' not found.\n";
}
