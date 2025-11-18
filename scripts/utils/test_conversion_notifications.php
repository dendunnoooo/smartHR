<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== TESTING TOKEN CONVERSION NOTIFICATION ===\n\n";
    
    // Find an employee to test with
    $employee = \App\Models\User::where('email', 'employee@smarthr.com')->first();
    if (!$employee) {
        echo "Employee not found\n";
        exit;
    }
    
    echo "Employee: {$employee->firstname} {$employee->lastname}\n";
    echo "Current notifications: " . $employee->unreadNotifications->count() . "\n\n";
    
    // Find HR users who should receive the notification
    $hrUsers = \App\Models\User::role(['Admin', 'HR Admin'])->get();
    echo "HR Users found: " . $hrUsers->count() . "\n";
    foreach ($hrUsers as $hr) {
        echo "  - {$hr->firstname} {$hr->lastname} ({$hr->email}) - Roles: " . $hr->roles->pluck('name')->join(', ') . "\n";
    }
    echo "\n";
    
    // Check if TokenConversionRequested notification class exists
    if (class_exists('\App\Notifications\TokenConversionRequested')) {
        echo "TokenConversionRequested class: EXISTS ✓\n";
    } else {
        echo "TokenConversionRequested class: NOT FOUND ✗\n";
    }
    
    if (class_exists('\App\Notifications\TokenConversionApproved')) {
        echo "TokenConversionApproved class: EXISTS ✓\n";
    } else {
        echo "TokenConversionApproved class: NOT FOUND ✗\n";
    }
    
    if (class_exists('\App\Notifications\TokenConversionRejected')) {
        echo "TokenConversionRejected class: EXISTS ✓\n";
    } else {
        echo "TokenConversionRejected class: NOT FOUND ✗\n";
    }
    
    // Check recent conversions
    echo "\n=== RECENT TOKEN CONVERSIONS ===\n";
    $conversions = \App\Models\TokenConversion::orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    if ($conversions->count() == 0) {
        echo "No conversions found.\n";
    } else {
        foreach ($conversions as $conv) {
            echo "\nConversion ID: {$conv->id}\n";
            echo "User: {$conv->user->firstname} {$conv->user->lastname}\n";
            echo "Type: {$conv->conversion_type}\n";
            echo "Status: {$conv->status}\n";
            echo "Tokens: {$conv->tokens_converted}\n";
            echo "Created: {$conv->created_at->format('Y-m-d H:i:s')}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
