<?php

namespace App\Support;

use App\Models\AgendaItem;
use App\Models\Aluno;
use App\Models\Pagamento;
use Carbon\Carbon;

class DashboardResumo
{
    /**
     * Indicadores do dashboard a partir do banco (sem mock).
     * Receita/comissao/lucro do mes consideram apenas pagamentos com comprovante (efetivamente recebidos).
     */
    public static function make(): array
    {
        $today = Carbon::today()->toDateString();
        $mes = Carbon::now()->format('Y-m');

        $pagamentosRecebidos = Pagamento::query()
            ->where('competencia', $mes)
            ->whereHas('comprovantes');

        $receitaMes = (clone $pagamentosRecebidos)->sum('valor_liquido');
        $comissaoMes = (clone $pagamentosRecebidos)->sum('valor_comissao');
        $lucroEstudioMes = (clone $pagamentosRecebidos)->sum('valor_estudio');

        $proximasAulas = AgendaItem::query()
            ->with('aluno')
            ->where('data', '>=', $today)
            ->orderBy('data')
            ->orderBy('hora_inicio')
            ->limit(8)
            ->get()
            ->map(fn (AgendaItem $item) => [
                'hora_inicio' => $item->hora_inicio ?? '--:--',
                'aluna_nome' => $item->aluno?->nome ?? $item->aluna_nome,
                'tipo_aula' => $item->tipo_aula ?: 'Aula',
            ])
            ->values()
            ->all();

        return [
            'alunas_ativas' => Aluno::query()->count(),
            'aulas_hoje' => AgendaItem::query()->whereDate('data', $today)->count(),
            'receita_mes' => (float) $receitaMes,
            'comissao_mes' => (float) $comissaoMes,
            'lucro_estudio_mes' => (float) $lucroEstudioMes,
            'proximas_aulas' => $proximasAulas,
        ];
    }
}
