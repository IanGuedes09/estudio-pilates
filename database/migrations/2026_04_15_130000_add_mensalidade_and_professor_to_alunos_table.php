<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alunos', function (Blueprint $table) {
            $table->decimal('valor_mensalidade', 10, 2)->default(0)->after('telefone');
            $table->foreignId('professor_id')->nullable()->after('valor_mensalidade')->constrained('professores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('alunos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('professor_id');
            $table->dropColumn('valor_mensalidade');
        });
    }
};
