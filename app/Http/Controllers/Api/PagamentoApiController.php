<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesProfessorScope;
use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\Pagamento;
use App\Models\PagamentoComprovante;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PagamentoApiController extends Controller
{
    use AuthorizesProfessorScope;

    public function index(Request $request): JsonResponse
    {
        // Garante que toda aluna ativa apareca no financeiro do mes selecionado.
        if ($request->filled('competencia')) {
            $this->ensureCompetenciaRows($request->string('competencia')->toString(), $request);
        }

        $query = Pagamento::query()
            ->with(['aluno', 'comprovantes' => function ($q) {
                $q->orderByDesc('created_at');
            }])
            ->whereNotNull('aluno_id')
            ->orderBy('professora_nome')
            ->orderBy('aluna_nome');

        if ($this->isProfessorUser()) {
            $pid = $this->requireCurrentProfessorId();
            $query->whereHas('aluno', fn (Builder $q) => $q->where('professor_id', $pid));
        }

        if ($request->filled('competencia')) {
            $query->where('competencia', $request->string('competencia')->toString());
        }
        if ($request->filled('professora') && ! $this->isProfessorUser()) {
            $query->where('professora_nome', $request->string('professora')->toString());
        }
        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            // PAGO/PENDENTE seguem comprovante: com anexo = PAGO, sem = PENDENTE.
            if ($status === 'PAGO') {
                $query->has('comprovantes');
            } elseif ($status === 'PENDENTE') {
                $query->doesntHave('comprovantes')->where('status', '!=', 'CANCELADO');
            } else {
                $query->where('status', $status);
            }
        }
        if ($request->filled('metodo')) {
            $query->where('metodo', $request->string('metodo')->toString());
        }

        $items = $query->get()->map(function (Pagamento $item) {
            return [
                'id' => $item->id,
                'aluno_id' => $item->aluno_id,
                'aluna_nome' => $item->aluno?->nome ?? $item->aluna_nome,
                'professora_nome' => $item->professora_nome,
                'competencia' => $item->competencia,
                'valor_bruto' => $item->valor_bruto,
                'desconto' => $item->desconto,
                'valor_liquido' => $item->valor_liquido,
                'metodo' => $item->metodo,
                'status' => $this->effectiveStatus($item),
                'data_pagamento' => $item->data_pagamento?->format('Y-m-d'),
                'percentual_comissao' => $item->percentual_comissao,
                'valor_comissao' => $item->valor_comissao,
                'valor_estudio' => $item->valor_estudio,
                'observacoes' => $item->observacoes,
                'comprovantes' => $item->comprovantes->map(function (PagamentoComprovante $c) {
                    return [
                        'id' => $c->id,
                        'competencia' => $c->competencia,
                        'nome_arquivo' => $c->nome_arquivo,
                        'url' => $c->url ?: ($c->caminho_arquivo ? Storage::url($c->caminho_arquivo) : null),
                        'mime_type' => $c->mime_type,
                        'tamanho_bytes' => $c->tamanho_bytes,
                        'created_at' => optional($c->created_at)->toIso8601String(),
                    ];
                })->values(),
            ];
        });

        return response()->json($items);
    }

    public function update(Request $request, Pagamento $pagamento): JsonResponse
    {
        $this->assertPagamentoAccessibleAsProfessor($pagamento);

        $data = $request->validate([
            'metodo' => ['required', 'in:PIX,DINHEIRO,CARTAO,BOLETO,TRANSFERENCIA'],
        ]);

        $pagamento->metodo = $data['metodo'];
        $pagamento->save();

        return response()->json([
            'ok' => true,
            'id' => $pagamento->id,
            'metodo' => $pagamento->metodo,
        ]);
    }

    public function storeComprovante(Request $request, Pagamento $pagamento): JsonResponse
    {
        $this->assertPagamentoAccessibleAsProfessor($pagamento);

        $data = $request->validate([
            'competencia' => ['required', 'regex:/^\d{4}\-\d{2}$/'],
            'arquivo' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('arquivo');
        $path = $file->store('comprovantes', 'public');

        // Permite ajustar competencia no momento do anexo para operacao mensal.
        if ($pagamento->competencia !== $data['competencia']) {
            $pagamento->competencia = $data['competencia'];
            $pagamento->save();
        }

        $comprovante = $pagamento->comprovantes()->create([
            'competencia' => $data['competencia'],
            'nome_arquivo' => $file->getClientOriginalName(),
            'caminho_arquivo' => $path,
            'mime_type' => $file->getClientMimeType(),
            'tamanho_bytes' => $file->getSize(),
            'enviado_por' => optional($request->user())->id,
        ]);

        if ($pagamento->status !== 'CANCELADO') {
            $pagamento->status = 'PAGO';
            $pagamento->data_pagamento = $pagamento->data_pagamento ?? now()->toDateString();
            $pagamento->save();
        }

        return response()->json([
            'id' => $comprovante->id,
            'competencia' => $comprovante->competencia,
            'nome_arquivo' => $comprovante->nome_arquivo,
            'url' => Storage::url($comprovante->caminho_arquivo),
            'mime_type' => $comprovante->mime_type,
            'tamanho_bytes' => $comprovante->tamanho_bytes,
        ], 201);
    }

    public function destroyComprovante(Pagamento $pagamento, PagamentoComprovante $comprovante): JsonResponse
    {
        $this->assertPagamentoAccessibleAsProfessor($pagamento);

        if ($comprovante->pagamento_id !== $pagamento->id) {
            abort(404);
        }

        if ($comprovante->caminho_arquivo) {
            Storage::disk('public')->delete($comprovante->caminho_arquivo);
        }

        $comprovante->delete();

        $pagamento->refresh();
        if ($pagamento->comprovantes()->count() === 0 && $pagamento->status !== 'CANCELADO') {
            $pagamento->status = 'PENDENTE';
            $pagamento->data_pagamento = null;
            $pagamento->save();
        }

        return response()->json(['ok' => true]);
    }

    /**
     * No mes, todos comecam como PENDENTE ate existir comprovante anexado (ai vira PAGO).
     */
    private function effectiveStatus(Pagamento $item): string
    {
        if ($item->status === 'CANCELADO') {
            return 'CANCELADO';
        }

        return $item->comprovantes->isNotEmpty() ? 'PAGO' : 'PENDENTE';
    }

    /**
     * Para uma competencia, cria pendencia financeira para cada aluna ativa sem lancamento.
     */
    private function ensureCompetenciaRows(string $competencia, ?Request $request = null): void
    {
        if (!preg_match('/^\d{4}\-\d{2}$/', $competencia)) {
            return;
        }

        $alunosQuery = Aluno::query()
            ->with('professor:id,nome,comissao_percentual')
            ->orderBy('id');

        if ($request?->user() && ($request->user()->perfil ?? null) === 'Professor') {
            $pid = $request->user()->professorVinculado()?->id;
            if (! $pid) {
                return;
            }
            $alunosQuery->where('professor_id', $pid);
        }

        $alunos = $alunosQuery->get(['id', 'nome', 'valor_mensalidade', 'professor_id']);
        if ($alunos->isEmpty()) {
            return;
        }

        foreach ($alunos as $aluno) {
            $financeiro = $this->buildFinanceFromAluno($aluno);

            $pagamento = Pagamento::query()->firstOrNew([
                'aluno_id' => $aluno->id,
                'competencia' => $competencia,
            ]);

            if (!$pagamento->exists) {
                $pagamento->fill([
                    ...$financeiro,
                    'metodo' => 'PIX',
                    'status' => 'PENDENTE',
                    'data_pagamento' => null,
                    'observacoes' => 'Gerado automaticamente a partir do cadastro de alunos e professores.',
                ]);
                $pagamento->save();
                continue;
            }

            // Mantém histórico: não recalcula pagamentos cancelados ou já quitados por comprovante.
            if ($pagamento->status === 'CANCELADO' || $pagamento->comprovantes()->exists()) {
                continue;
            }

            $pagamento->fill($financeiro);
            $pagamento->status = 'PENDENTE';
            $pagamento->save();
        }
    }

    private function buildFinanceFromAluno(Aluno $aluno): array
    {
        $valorBruto = (float) ($aluno->valor_mensalidade ?? 0);
        $desconto = 0.0;
        $valorLiquido = max(0.0, $valorBruto - $desconto);
        $percentualComissao = (float) ($aluno->professor?->comissao_percentual ?? 0);
        $valorComissao = round($valorLiquido * ($percentualComissao / 100), 2);
        $valorEstudio = round($valorLiquido - $valorComissao, 2);

        return [
            'aluna_nome' => $aluno->nome,
            'professora_nome' => $aluno->professor?->nome ?? 'Sem professor',
            'valor_bruto' => $valorBruto,
            'desconto' => $desconto,
            'valor_liquido' => $valorLiquido,
            'percentual_comissao' => $percentualComissao,
            'valor_comissao' => $valorComissao,
            'valor_estudio' => $valorEstudio,
        ];
    }

}
