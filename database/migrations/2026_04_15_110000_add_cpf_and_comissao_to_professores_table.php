<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professores', function (Blueprint $table) {
            $table->string('cpf', 11)->nullable()->unique()->after('nome');
            $table->decimal('comissao_percentual', 5, 2)->default(0)->after('telefone');
        });
    }

    public function down(): void
    {
        Schema::table('professores', function (Blueprint $table) {
            $table->dropColumn(['cpf', 'comissao_percentual']);
        });
    }
};
