<?php

namespace Database\Seeders;

use App\Models\Aluno;
use Illuminate\Database\Seeder;

class AlunoSeeder extends Seeder
{
    public function run(): void
    {
        $alunos = [
            ['nome' => 'Karine Designer', 'email' => 'karine.designer@example.com', 'telefone' => '21999990001', 'cpf' => null],
            ['nome' => 'Lorrane Magalhães', 'email' => 'lorrane.m@example.com', 'telefone' => '21999990002', 'cpf' => null],
            ['nome' => 'Rafaela Ferreira', 'email' => 'rafaela.f@example.com', 'telefone' => '21999990003', 'cpf' => null],
            ['nome' => 'Elisabeth Souza', 'email' => 'elisabeth.s@example.com', 'telefone' => '21999990004', 'cpf' => null],
            ['nome' => 'Fátima Ribeiro', 'email' => 'fatima.r@example.com', 'telefone' => '21999990005', 'cpf' => null],
            ['nome' => 'Alessandra Lima', 'email' => 'alessandra.l@example.com', 'telefone' => '21999990006', 'cpf' => null],
        ];

        foreach ($alunos as $row) {
            Aluno::query()->firstOrCreate(
                ['email' => $row['email']],
                [
                    'nome' => $row['nome'],
                    'telefone' => $row['telefone'],
                    'cpf' => $row['cpf'],
                ]
            );
        }
    }
}
