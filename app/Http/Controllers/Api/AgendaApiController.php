<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgendaItem;
use App\Models\Aluno;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgendaApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AgendaItem::query()
            ->with('aluno.professor')
            ->whereNotNull('aluno_id')
            ->orderBy('data')
            ->orderBy('hora_inicio');

        $this->applyProfessorScope($query, $request);

        if ($request->filled('mes')) {
            $mes = $request->string('mes')->toString();
            $query->where(function ($q) use ($mes) {
                $q->whereNull('data')
                    ->orWhere('data', 'like', $mes.'%');
            });
        }

        if ($request->filled('professora')) {
            $query->where('professora_nome', $request->string('professora')->toString());
        }

        $items = $query->get()->map(fn (AgendaItem $item) => $this->itemToArray($item));

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'aluno_id' => ['required', 'exists:alunos,id'],
            'data' => ['required', 'date_format:Y-m-d'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fim' => ['nullable', 'date_format:H:i'],
            'professora_nome' => ['nullable', 'string', 'max:255'],
            'tipo_aula' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ]);

        $aluno = Aluno::query()->findOrFail($data['aluno_id']);
        $this->assertProfessorAlunoAccess($request, $aluno->id);
        $horaInicio = $data['hora_inicio'];
        $this->assertProfessorAllowedHour($request, $horaInicio);
        $horaFim = $data['hora_fim'] ?? $this->suggestHoraFim($horaInicio);

        $item = AgendaItem::query()->create([
            'aluno_id' => $aluno->id,
            'aluna_nome' => $aluno->nome,
            'professora_nome' => $this->resolveProfessorNome($request, $data['professora_nome'] ?? null),
            'data' => $data['data'],
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim,
            'tipo_aula' => $data['tipo_aula'] ?? 'Aula',
            'status' => $data['status'] ?? 'AGENDADA',
            'observacoes' => $data['observacoes'] ?? null,
        ]);

        return response()->json($this->itemToArray($item->load('aluno')), 201);
    }

    public function update(Request $request, AgendaItem $agendaItem): JsonResponse
    {
        $this->assertProfessorAlunoAccess($request, $agendaItem->aluno_id);

        $data = $request->validate([
            'data' => ['nullable', 'date_format:Y-m-d'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fim' => ['nullable', 'date_format:H:i'],
            'status' => ['nullable', 'string', 'max:50'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (array_key_exists('hora_inicio', $data) && $data['hora_inicio']) {
            $this->assertProfessorAllowedHour($request, $data['hora_inicio']);
        }

        $agendaItem->fill($data);
        $agendaItem->save();

        return response()->json($this->itemToArray($agendaItem->load('aluno')));
    }

    public function destroy(AgendaItem $agendaItem): JsonResponse
    {
        $this->assertProfessorAlunoAccess(request(), $agendaItem->aluno_id);
        $agendaItem->delete();

        return response()->json(['ok' => true]);
    }

    private function itemToArray(AgendaItem $item): array
    {
        return [
            'id' => $item->id,
            'data' => $item->data?->format('Y-m-d'),
            'hora_inicio' => $item->hora_inicio,
            'hora_fim' => $item->hora_fim,
            'aluna' => [
                'id' => $item->aluno_id,
                'nome' => $item->aluno?->nome ?? $item->aluna_nome,
                'professor_nome' => $item->aluno?->professor?->nome,
            ],
            'professora' => ['nome' => $item->professora_nome],
            'tipo_aula' => $item->tipo_aula,
            'status' => $item->status,
            'observacoes' => $item->observacoes,
        ];
    }

    private function suggestHoraFim(string $horaInicio): string
    {
        [$h, $m] = array_map('intval', explode(':', $horaInicio));
        $ts = mktime($h, $m + 50, 0);

        return sprintf('%02d:%02d', (int) date('H', $ts), (int) date('i', $ts));
    }

    private function applyProfessorScope($query, Request $request): void
    {
        if (($request->user()?->perfil ?? null) !== 'Professor') {
            return;
        }

        $query
            ->whereBetween('hora_inicio', ['14:00', '19:59'])
            ->whereHas('aluno.professor', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
    }

    private function assertProfessorAlunoAccess(Request $request, ?int $alunoId): void
    {
        if (($request->user()?->perfil ?? null) !== 'Professor') {
            return;
        }

        if (!$alunoId) {
            abort(403, 'Aluno inválido para este perfil.');
        }

        $allowed = Aluno::query()
            ->whereKey($alunoId)
            ->whereHas('professor', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->exists();

        if (!$allowed) {
            abort(403, 'Você só pode acessar alunos vinculados ao seu cadastro.');
        }
    }

    private function assertProfessorAllowedHour(Request $request, string $horaInicio): void
    {
        if (($request->user()?->perfil ?? null) !== 'Professor') {
            return;
        }

        $hora = (int) substr($horaInicio, 0, 2);
        if ($hora < 14 || $hora > 19) {
            abort(403, 'Professor só pode operar agenda no período da tarde.');
        }
    }

    private function resolveProfessorNome(Request $request, ?string $fallback): string
    {
        if (($request->user()?->perfil ?? null) === 'Professor') {
            return $request->user()->name;
        }

        return $fallback ?: 'Karine';
    }
}
