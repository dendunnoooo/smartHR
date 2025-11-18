<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Renaming 'Super Admin' role to 'HR Admin'...\n\n";

$superAdminRole = Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();

if ($superAdminRole) {
    // Check users with this role before renaming
    $users = App\Models\User::role('Super Admin')->get();
    
    echo "Users with 'Super Admin' role:\n";
    foreach ($users as $user) {
        echo "- {$user->fullname} ({$user->email})\n";
    }
    echo "\n";
    
    // Rename the role
    $superAdminRole->name = 'HR Admin';
    $superAdminRole->save();
    
    echo "✓ 'Super Admin' role renamed to 'HR Admin' successfully!\n\n";
    
    // Verify the change
    echo "Verification:\n";
    foreach ($users as $user) {
        $user->refresh();
        echo "- {$user->fullname}: " . $user->roles->pluck('name')->implode(', ') . "\n";
    }
    
    echo "\n✓ All users with 'Super Admin' role are now 'HR Admin'.\n";
    echo "Note: The dashboard still checks for 'Super Admin' role to display 'HR HEAD'.\n";
    echo "You may need to update the dashboard logic.\n";
} else {
    echo "- 'Super Admin' role not found\n";
}
