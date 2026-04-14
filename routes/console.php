<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('estudio:sanitize-aluno-links', function () {
    $this->info('Iniciando saneamento de vinculos aluno_id...');

    $alunos = DB::table('alunos')->get(['id', 'nome', 'email']);

    $nomeMap = [];
    $emailMap = [];
    foreach ($alunos as $aluno) {
        $nomeMap[mb_strtolower(trim((string) $aluno->nome))] = $aluno->id;
        if (!empty($aluno->email)) {
            $emailMap[mb_strtolower(trim((string) $aluno->email))] = $aluno->id;
        }
    }

    $agendaAtualizados = 0;
    foreach (DB::table('agenda_items')->whereNull('aluno_id')->get() as $row) {
        $id = $nomeMap[mb_strtolower(trim((string) $row->aluna_nome))] ?? null;
        if ($id) {
            DB::table('agenda_items')->where('id', $row->id)->update(['aluno_id' => $id]);
            $agendaAtualizados++;
        }
    }

    $pagAtualizados = 0;
    foreach (DB::table('pagamentos')->whereNull('aluno_id')->get() as $row) {
        $id = $nomeMap[mb_strtolower(trim((string) $row->aluna_nome))] ?? null;
        if ($id) {
            DB::table('pagamentos')->where('id', $row->id)->update(['aluno_id' => $id]);
            $pagAtualizados++;
        }
    }

    $this->info("Agenda atualizada: {$agendaAtualizados}");
    $this->info("Pagamentos atualizados: {$pagAtualizados}");
    $this->info('Saneamento concluido.');
})->purpose('Vincula agenda/pagamentos ao cadastro de alunos');
