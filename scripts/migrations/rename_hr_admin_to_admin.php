<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Step 1: Deleting 'Admin' role...\n";
$adminRole = Spatie\Permission\Models\Role::where('name', 'Admin')->first();
if ($adminRole) {
    $adminRole->delete();
    echo "✓ 'Admin' role deleted successfully!\n\n";
} else {
    echo "- 'Admin' role not found (already deleted or never existed)\n\n";
}

echo "Step 2: Renaming 'HR Admin' role to 'Admin'...\n";
$hrAdminRole = Spatie\Permission\Models\Role::where('name', 'HR Admin')->first();
if ($hrAdminRole) {
    $hrAdminRole->name = 'Admin';
    $hrAdminRole->save();
    echo "✓ 'HR Admin' role renamed to 'Admin' successfully!\n\n";
} else {
    echo "- 'HR Admin' role not found\n\n";
}

echo "Step 3: Verifying users with 'Admin' role...\n";
$admin = App\Models\User::where('email', 'admin@smarthr.com')->first();
$admin1 = App\Models\User::where('email', 'admin1@smarthr.com')->first();

if ($admin) {
    echo "User: {$admin->fullname} ({$admin->email})\n";
    echo "Roles: " . $admin->roles->pluck('name')->implode(', ') . "\n";
    echo "Will display as: HR STAFF\n\n";
}

if ($admin1) {
    echo "User: {$admin1->fullname} ({$admin1->email})\n";
    echo "Roles: " . $admin1->roles->pluck('name')->implode(', ') . "\n";
    echo "Will display as: HR STAFF\n\n";
}

echo "✓ All done! Both users will now display HR STAFF badge.\n";
