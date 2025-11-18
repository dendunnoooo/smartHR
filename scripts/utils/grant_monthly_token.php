<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $user = \App\Models\User::where(function($q) {
        $q->whereRaw('LOWER(firstname) LIKE ?', ['%jack%'])
          ->orWhereRaw('LOWER(lastname) LIKE ?', ['%geonzon%']);
    })->first();
    
    if (!$user) {
        echo "User not found\n";
        exit;
    }
    
    echo "User: {$user->firstname} {$user->lastname}\n";
    
    // Get or create monthly token record
    $monthlyToken = $user->monthlyToken;
    if (!$monthlyToken) {
        $monthlyToken = new \App\Models\MonthlyToken();
        $monthlyToken->user_id = $user->id;
        $monthlyToken->tokens = 0;
    }
    
    echo "Current monthly tokens: {$monthlyToken->tokens}\n";
    
    // Grant 1 token
    $monthlyToken->tokens = $monthlyToken->tokens + 1;
    $monthlyToken->save();
    
    echo "✓ Granted 1 monthly token\n";
    echo "New balance: {$monthlyToken->tokens} monthly tokens\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
