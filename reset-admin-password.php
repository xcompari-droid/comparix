<?php
// reset-admin-password.php
// Rulează: php reset-admin-password.php email@domeniu.com parola_noua

if ($argc !== 3) {
    echo "Usage: php reset-admin-password.php email noua_parola\n";
    exit(1);
}

$email = $argv[1];
$newPassword = $argv[2];

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;

$user = User::where('email', $email)->first();
if (!$user) {
    echo "User not found: $email\n";
    exit(1);
}

$user->password = bcrypt($newPassword);
$user->save();
echo "Parola a fost resetată cu succes pentru $email\n";
