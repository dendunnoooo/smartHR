<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Find user
    $user = \App\Models\User::where('firstname', 'LIKE', '%Jack%')
        ->orWhere('lastname', 'LIKE', '%Geonzon%')
        ->first();
    
    if (!$user) {
        echo "User not found. Searching all users with similar names...\n";
        $users = \App\Models\User::where('firstname', 'LIKE', '%jack%')
            ->orWhere('lastname', 'LIKE', '%geonzon%')
            ->get();
        
        if ($users->isEmpty()) {
            echo "No users found matching 'Jack' or 'Geonzon'\n";
            exit;
        }
        
        echo "Found " . $users->count() . " user(s):\n";
        foreach ($users as $u) {
            echo "- {$u->firstname} {$u->lastname} (ID: {$u->id}, Email: {$u->email})\n";
        }
        exit;
    }
    
    echo "Found user: {$user->firstname} {$user->lastname}\n";
    echo "Email: {$user->email}\n";
    echo "Current tokens: " . ($user->leave_tokens ?? 0) . "\n\n";
    
    // Ask for token amount
    echo "How many tokens to grant? ";
    $tokens = (int)trim(fgets(STDIN));
    
    if ($tokens <= 0) {
        echo "Invalid token amount\n";
        exit;
    }
    
    // Grant tokens
    $user->leave_tokens = ($user->leave_tokens ?? 0) + $tokens;
    $user->save();
    
    echo "\n✓ Granted {$tokens} tokens to {$user->firstname} {$user->lastname}\n";
    echo "New balance: {$user->leave_tokens} tokens\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
