<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdmin extends Command
{
    protected $signature = 'make:admin';

    protected $description = 'Crée le compte administrateur';

    public function handle(): int
    {
        $user = User::updateOrCreate(
            ['email' => 'arnaud@plombier-sos.fr'],
            [
                'username' => 'arnaud',
                'password' => Hash::make('PlombierSOS2026!'),
                'is_admin' => true,
            ]
        );

        $this->info("Admin créé : {$user->email}");

        return self::SUCCESS;
    }
}
