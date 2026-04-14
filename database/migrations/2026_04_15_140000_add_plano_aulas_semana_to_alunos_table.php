<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alunos', function (Blueprint $table) {
            $table->unsignedTinyInteger('plano_aulas_semana')->default(2)->after('valor_mensalidade');
        });

        DB::table('alunos')
            ->where('valor_mensalidade', '>=', 300)
            ->update(['plano_aulas_semana' => 3]);
    }

    public function down(): void
    {
        Schema::table('alunos', function (Blueprint $table) {
            $table->dropColumn('plano_aulas_semana');
        });
    }
};
