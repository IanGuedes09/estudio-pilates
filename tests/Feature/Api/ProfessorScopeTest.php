<?php

namespace Tests\Feature\Api;

use App\Models\AgendaItem;
use App\Models\Aluno;
use App\Models\Pagamento;
use App\Models\Professor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessorScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_professor_only_sees_own_students_in_api_catalog(): void
    {
        $profUser = User::factory()->create(['perfil' => 'Professor']);
        $professor = $profUser->professorVinculado();

        $otherProfessor = Professor::query()->create([
            'nome' => 'Outra Prof',
            'email' => 'outra@example.com',
            'ativo' => true,
            'comissao_percentual' => 0,
        ]);

        $ownAluno = Aluno::query()->create([
            'nome' => 'Aluna A',
            'email' => 'aluna.a@example.com',
            'telefone' => '11999999999',
            'plano_aulas_semana' => 2,
            'valor_mensalidade' => 200,
            'professor_id' => $professor->id,
        ]);

        $otherAluno = Aluno::query()->create([
            'nome' => 'Aluna B',
            'email' => 'aluna.b@example.com',
            'telefone' => '11988888888',
            'plano_aulas_semana' => 2,
            'valor_mensalidade' => 200,
            'professor_id' => $otherProfessor->id,
        ]);

        $response = $this->actingAs($profUser)->getJson('/api/v1/alunos');

        $response->assertOk();
        $ids = collect($response->json())->pluck('id')->all();

        $this->assertContains($ownAluno->id, $ids);
        $this->assertNotContains($otherAluno->id, $ids);
    }

    public function test_professor_cannot_edit_other_professors_payment_or_agenda(): void
    {
        $profUser = User::factory()->create(['perfil' => 'Professor']);
        $professor = $profUser->professorVinculado();

        $otherProfessor = Professor::query()->create([
            'nome' => 'Outra Prof',
            'email' => 'outra2@example.com',
            'ativo' => true,
            'comissao_percentual' => 0,
        ]);

        $otherAluno = Aluno::query()->create([
            'nome' => 'Aluna X',
            'email' => 'aluna.x@example.com',
            'telefone' => '11977777777',
            'plano_aulas_semana' => 2,
            'valor_mensalidade' => 200,
            'professor_id' => $otherProfessor->id,
        ]);

        $pagamento = Pagamento::query()->create([
            'aluno_id' => $otherAluno->id,
            'aluna_nome' => $otherAluno->nome,
            'professora_nome' => $otherProfessor->nome,
            'competencia' => now()->format('Y-m'),
            'valor_bruto' => 200,
            'desconto' => 0,
            'valor_liquido' => 200,
            'metodo' => 'PIX',
            'status' => 'PENDENTE',
            'percentual_comissao' => 0,
            'valor_comissao' => 0,
            'valor_estudio' => 200,
        ]);

        $agenda = AgendaItem::query()->create([
            'aluno_id' => $otherAluno->id,
            'aluna_nome' => $otherAluno->nome,
            'professora_nome' => $otherProfessor->nome,
            'data' => now()->toDateString(),
            'hora_inicio' => '09:00',
            'hora_fim' => '09:50',
            'tipo_aula' => 'Aula',
            'status' => 'AGENDADA',
        ]);

        $this->actingAs($profUser)
            ->patchJson("/api/v1/pagamentos/{$pagamento->id}", ['metodo' => 'DINHEIRO'])
            ->assertStatus(403);

        $this->actingAs($profUser)
            ->patchJson("/api/v1/agenda/{$agenda->id}", ['status' => 'CANCELADA'])
            ->assertStatus(403);
    }

    public function test_professor_only_sees_own_payments_in_api_list(): void
    {
        $profUser = User::factory()->create(['perfil' => 'Professor']);
        $professor = $profUser->professorVinculado();

        $otherProfessor = Professor::query()->create([
            'nome' => 'Outra Prof',
            'email' => 'outra3@example.com',
            'ativo' => true,
            'comissao_percentual' => 0,
        ]);

        $ownAluno = Aluno::query()->create([
            'nome' => 'Aluna P',
            'email' => 'aluna.p@example.com',
            'telefone' => '11966666666',
            'plano_aulas_semana' => 2,
            'valor_mensalidade' => 200,
            'professor_id' => $professor->id,
        ]);

        $otherAluno = Aluno::query()->create([
            'nome' => 'Aluna Q',
            'email' => 'aluna.q@example.com',
            'telefone' => '11955555555',
            'plano_aulas_semana' => 2,
            'valor_mensalidade' => 200,
            'professor_id' => $otherProfessor->id,
        ]);

        $competencia = now()->format('Y-m');

        $ownPagamento = Pagamento::query()->create([
            'aluno_id' => $ownAluno->id,
            'aluna_nome' => $ownAluno->nome,
            'professora_nome' => 'Prof',
            'competencia' => $competencia,
            'valor_bruto' => 200,
            'desconto' => 0,
            'valor_liquido' => 200,
            'metodo' => 'PIX',
            'status' => 'PENDENTE',
            'percentual_comissao' => 0,
            'valor_comissao' => 0,
            'valor_estudio' => 200,
        ]);

        $otherPagamento = Pagamento::query()->create([
            'aluno_id' => $otherAluno->id,
            'aluna_nome' => $otherAluno->nome,
            'professora_nome' => $otherProfessor->nome,
            'competencia' => $competencia,
            'valor_bruto' => 200,
            'desconto' => 0,
            'valor_liquido' => 200,
            'metodo' => 'PIX',
            'status' => 'PENDENTE',
            'percentual_comissao' => 0,
            'valor_comissao' => 0,
            'valor_estudio' => 200,
        ]);

        $response = $this->actingAs($profUser)->getJson("/api/v1/pagamentos?competencia={$competencia}");

        $response->assertOk();
        $ids = collect($response->json())->pluck('id')->all();

        $this->assertContains($ownPagamento->id, $ids);
        $this->assertNotContains($otherPagamento->id, $ids);
    }

    public function test_professor_cannot_create_agenda_for_other_professors_student(): void
    {
        $profUser = User::factory()->create(['perfil' => 'Professor']);
        $profUser->professorVinculado();

        $otherProfessor = Professor::query()->create([
            'nome' => 'Outra Prof',
            'email' => 'outra4@example.com',
            'ativo' => true,
            'comissao_percentual' => 0,
        ]);

        $otherAluno = Aluno::query()->create([
            'nome' => 'Aluna Z',
            'email' => 'aluna.z@example.com',
            'telefone' => '11944444444',
            'plano_aulas_semana' => 2,
            'valor_mensalidade' => 200,
            'professor_id' => $otherProfessor->id,
        ]);

        $this->actingAs($profUser)
            ->postJson('/api/v1/agenda', [
                'aluno_id' => $otherAluno->id,
                'data' => now()->toDateString(),
                'hora_inicio' => '10:00',
                'hora_fim' => '10:50',
                'tipo_aula' => 'Aula',
                'status' => 'AGENDADA',
            ])
            ->assertStatus(403);
    }
}

