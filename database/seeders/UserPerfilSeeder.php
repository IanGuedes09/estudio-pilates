<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserPerfilSeeder extends Seeder
{
    public function run(): void
    {
        // Senha inicial padrao para primeiro acesso (recomendar troca apos login).
        $defaultPassword = Hash::make('12345678');

        User::query()->updateOrCreate(
            ['email' => 'karine@studiovitali.com'],
            [
                'name' => 'Karine',
                'perfil' => 'Administrador',
                'password' => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'iandavi.guedes@gmail.com'],
            [
                'name' => 'Ian Davi',
                'perfil' => 'Administrador',
                'password' => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'diegommega@gmail.com'],
            [
                'name' => 'Diego Mega',
                'perfil' => 'Professor',
                'password' => $defaultPassword,
                'email_verified_at' => now(),
            ]
        );
    }
}
