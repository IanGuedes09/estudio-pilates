<?php

namespace App\Support;

use App\Models\AgendaItem;
use App\Models\Aluno;
use App\Models\Pagamento;
use App\Models\User;
use Carbon\Carbon;

class DashboardResumo
{
    /**
     * Indicadores do dashboard a partir do banco (sem mock).
     * Receita/comissao/lucro do mes consideram apenas pagamentos com comprovante (efetivamente recebidos).
     */
    public static function make(?User $user = null): array
    {
        $today = Carbon::today()->toDateString();
        $mes = Carbon::now()->format('Y-m');
        $isProfessor = ($user?->perfil ?? null) === 'Professor';

        $pagamentosRecebidos = Pagamento::query()
            ->where('competencia', $mes)
            ->whereHas('comprovantes');

        if ($isProfessor) {
            $pagamentosRecebidos->whereHas('aluno.professor', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $receitaMes = (clone $pagamentosRecebidos)->sum('valor_liquido');
        $comissaoMes = (clone $pagamentosRecebidos)->sum('valor_comissao');
        $lucroEstudioMes = (clone $pagamentosRecebidos)->sum('valor_estudio');

        $proximasAulasQuery = AgendaItem::query()
            ->with('aluno')
            ->whereDate('data', $today)
            ->orderBy('data')
            ->orderBy('hora_inicio')
            ->limit(8);

        if ($isProfessor) {
            $proximasAulasQuery->whereHas('aluno.professor', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $proximasAulas = $proximasAulasQuery
            ->get()
            ->map(fn (AgendaItem $item) => [
                'data' => $item->data?->format('Y-m-d'),
                'hora_inicio' => $item->hora_inicio ?? '--:--',
                'aluna_nome' => $item->aluno?->nome ?? $item->aluna_nome,
                'tipo_aula' => $item->tipo_aula ?: 'Aula',
            ])
            ->values()
            ->all();

        $alunasAtivas = Aluno::query();
        $aulasHoje = AgendaItem::query()->whereDate('data', $today);
        if ($isProfessor) {
            $alunasAtivas->whereHas('professor', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
            $aulasHoje->whereHas('aluno.professor', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return [
            'alunas_ativas' => $alunasAtivas->count(),
            'aulas_hoje' => $aulasHoje->count(),
            'receita_mes' => (float) $receitaMes,
            'comissao_mes' => (float) $comissaoMes,
            'lucro_estudio_mes' => (float) $lucroEstudioMes,
            'proximas_aulas' => $proximasAulas,
        ];
    }
}
