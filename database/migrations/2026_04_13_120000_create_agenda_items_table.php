<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agenda_items', function (Blueprint $table) {
            $table->id();
            // Campos minimos para operacao manual da agenda (sem depender de FKs agora).
            $table->string('aluna_nome');
            $table->string('professora_nome')->nullable();
            $table->date('data')->nullable();
            $table->string('hora_inicio', 5)->nullable();
            $table->string('hora_fim', 5)->nullable();
            $table->string('tipo_aula')->nullable();
            // Status cobre fluxo de falta e reposicao.
            $table->string('status')->default('AGENDADA');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_items');
    }
};
