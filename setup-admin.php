<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('email', 'admin@comparix.ro')->first();

if ($user) {
    $user->password = bcrypt('password');
    $user->email_verified_at = now();
    $user->save();
    
    echo "Admin user updated:\n";
    echo "Email: admin@comparix.ro\n";
    echo "Password: password\n";
    echo "Email verified: Yes\n";
    echo "User ID: " . $user->id . "\n";
} else {
    $user = App\Models\User::create([
        'name' => 'Admin',
        'email' => 'admin@comparix.ro',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
    
    echo "Admin user created:\n";
    echo "Email: admin@comparix.ro\n";
    echo "Password: password\n";
    echo "User ID: " . $user->id . "\n";
}
