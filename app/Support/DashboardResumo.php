<?php

namespace App\Support;

use App\Models\AgendaItem;
use App\Models\Aluno;
use App\Models\Pagamento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DashboardResumo
{
    /**
     * Indicadores do dashboard a partir do banco (sem mock).
     * Receita/comissao/lucro do mes consideram apenas pagamentos com comprovante (efetivamente recebidos).
     * Perfil Professor: métricas restritas às alunas vinculadas a ele.
     */
    public static function make(?User $user = null): array
    {
        $today = Carbon::today()->toDateString();
        $mes = Carbon::now()->format('Y-m');

        $professorId = null;
        if ($user && ($user->perfil ?? null) === 'Professor') {
            $professorId = $user->professorVinculado()?->id;
        }

        $pagamentosRecebidos = Pagamento::query()
            ->where('competencia', $mes)
            ->whereHas('comprovantes');

        if ($professorId !== null) {
            $pagamentosRecebidos->whereHas('aluno', fn (Builder $q) => $q->where('professor_id', $professorId));
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

        if ($professorId !== null) {
            $proximasAulasQuery->whereHas('aluno', fn (Builder $q) => $q->where('professor_id', $professorId));
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

        if ($professorId !== null) {
            $alunasAtivas->where('professor_id', $professorId);
            $aulasHoje->whereHas('aluno', fn (Builder $q) => $q->where('professor_id', $professorId));
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
