<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::firstOrCreate(
    ['email' => 'admin@comparix.ro'],
    [
        'name' => 'Admin',
        'password' => bcrypt('password')
    ]
);

$user->password = bcrypt('password');
$user->save();

echo "Admin user credentials:\n";
echo "Email: admin@comparix.ro\n";
echo "Password: password\n";
