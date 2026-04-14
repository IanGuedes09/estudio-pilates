<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_items', function (Blueprint $table) {
            $table->foreignId('aluno_id')->nullable()->after('id')->constrained('alunos')->nullOnDelete();
        });

        Schema::table('pagamentos', function (Blueprint $table) {
            $table->foreignId('aluno_id')->nullable()->after('id')->constrained('alunos')->nullOnDelete();
        });

        foreach (DB::table('agenda_items')->whereNull('aluno_id')->cursor() as $row) {
            if (empty($row->aluna_nome)) {
                continue;
            }
            $alunoId = DB::table('alunos')->where('nome', $row->aluna_nome)->value('id');
            if ($alunoId) {
                DB::table('agenda_items')->where('id', $row->id)->update(['aluno_id' => $alunoId]);
            }
        }

        foreach (DB::table('pagamentos')->whereNull('aluno_id')->cursor() as $row) {
            if (empty($row->aluna_nome)) {
                continue;
            }
            $alunoId = DB::table('alunos')->where('nome', $row->aluna_nome)->value('id');
            if ($alunoId) {
                DB::table('pagamentos')->where('id', $row->id)->update(['aluno_id' => $alunoId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('agenda_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('aluno_id');
        });

        Schema::table('pagamentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('aluno_id');
        });
    }
};
