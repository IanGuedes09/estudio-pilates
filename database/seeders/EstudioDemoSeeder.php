<?php

namespace Database\Seeders;

use App\Models\AgendaItem;
use App\Models\Aluno;
use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Preenche pagamentos e agenda a partir dos alunos, apenas se as tabelas estiverem vazias.
 */
class EstudioDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! Aluno::query()->exists()) {
            return;
        }

        $mes = now()->format('Y-m');
        $professoras = ['Karine', 'Prof. Ana'];

        if (! Pagamento::query()->exists()) {
            foreach (Aluno::query()->orderBy('id')->get() as $index => $aluno) {
                $bruto = 350 + ($index * 30);
                $desconto = $index % 2 === 1 ? 20.0 : 0.0;
                $liquido = $bruto - $desconto;
                $pct = $index % 2 === 0 ? 45 : 40;
                $comissao = round($liquido * $pct / 100, 2);
                $estudio = round($liquido - $comissao, 2);

                Pagamento::query()->create([
                    'aluno_id' => $aluno->id,
                    'aluna_nome' => $aluno->nome,
                    'professora_nome' => $professoras[$index % 2],
                    'competencia' => $mes,
                    'valor_bruto' => $bruto,
                    'desconto' => $desconto,
                    'valor_liquido' => $liquido,
                    'metodo' => $index % 2 === 0 ? 'PIX' : 'CARTAO',
                    'status' => 'PENDENTE',
                    'data_pagamento' => null,
                    'percentual_comissao' => $pct,
                    'valor_comissao' => $comissao,
                    'valor_estudio' => $estudio,
                    'observacoes' => null,
                ]);
            }
        }

        if (! AgendaItem::query()->exists()) {
            $alunos = Aluno::query()->orderBy('nome')->get();
            $segunda = Carbon::now()->startOfWeek();

            foreach ($alunos->take(6) as $i => $aluno) {
                $dia = $segunda->copy()->addDays($i % 5);
                $hora = 8 + ($i % 4);

                AgendaItem::query()->create([
                    'aluno_id' => $aluno->id,
                    'aluna_nome' => $aluno->nome,
                    'professora_nome' => $professoras[$i % 2],
                    'data' => $dia->toDateString(),
                    'hora_inicio' => sprintf('%02d:00', $hora),
                    'hora_fim' => sprintf('%02d:50', $hora),
                    'tipo_aula' => $i % 2 === 0 ? 'Pilates Aparelho' : 'Pilates Solo',
                    'status' => 'AGENDADA',
                    'observacoes' => null,
                ]);
            }
        }
    }
}
