<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== CHECKING NOTIFICATIONS ===\n\n";
    
    // Get all users with unread notifications
    $users = \App\Models\User::whereHas('unreadNotifications')->get();
    
    echo "Users with unread notifications: " . $users->count() . "\n\n";
    
    foreach ($users as $user) {
        echo "User: {$user->firstname} {$user->lastname} ({$user->email})\n";
        echo "Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
        echo "Unread count: " . $user->unreadNotifications->count() . "\n";
        
        echo "Latest notifications:\n";
        foreach ($user->unreadNotifications->take(3) as $notif) {
            echo "  - Type: " . class_basename($notif->type) . "\n";
            echo "    Message: " . ($notif->data['message'] ?? 'N/A') . "\n";
            echo "    Created: " . $notif->created_at->diffForHumans() . "\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
