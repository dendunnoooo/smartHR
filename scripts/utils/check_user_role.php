<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find Wayne's user
$user = App\Models\User::where('firstname', 'Wayne')->where('lastname', 'Anave')->first();

if ($user) {
    echo "User: {$user->fullname}\n";
    echo "Email: {$user->email}\n";
    echo "Type: {$user->type->value}\n";
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    
    // Check if user has Super Admin role
    if (!$user->hasRole('Super Admin')) {
        echo "\nUser does NOT have 'Super Admin' role. Assigning now...\n";
        
        // Create or get Super Admin role
        $role = Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin']);
        $user->assignRole($role);
        
        echo "✓ 'Super Admin' role assigned successfully!\n";
    } else {
        echo "\n✓ User already has 'Super Admin' role.\n";
    }
} else {
    echo "User 'Wayne Anave' not found.\n";
}
