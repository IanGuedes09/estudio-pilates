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
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->string('aluna_nome');
            $table->string('professora_nome');
            $table->string('competencia', 7);
            $table->decimal('valor_bruto', 10, 2)->default(0);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->decimal('valor_liquido', 10, 2)->default(0);
            $table->string('metodo')->default('PIX');
            $table->string('status')->default('PENDENTE');
            $table->date('data_pagamento')->nullable();
            $table->unsignedTinyInteger('percentual_comissao')->default(0);
            $table->decimal('valor_comissao', 10, 2)->default(0);
            $table->decimal('valor_estudio', 10, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
