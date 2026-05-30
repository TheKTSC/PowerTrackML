<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'testuser+' . uniqid() . '@example.test';

$user = User::create([
    'nom' => 'User Test',
    'email' => $email,
    'password' => Hash::make('secret123'),
    'type_compte' => 'Particulier',
    'nombre_utilisateurs' => 1,
    'cout_kwh' => 100,
    'devise' => 'FCFA',
]);

if ($user) {
    echo "Created user id={$user->id} email={$user->email}\n";
} else {
    echo "Failed to create user\n";
}
