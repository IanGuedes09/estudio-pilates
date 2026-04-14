@extends('layouts.vitali-dashboard')

@section('title', 'Editar Aluno - Studio Vitali')

@section('content')
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Editar Aluno</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">Atualize os dados do cadastro principal da aluna.</p>
        </div>
        <a href="{{ route('alunos.index') }}" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-slate-600 dark:hover:bg-slate-500">
            Voltar para Alunos
        </a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white/70 p-6 dark:border-slate-700 dark:bg-slate-900/60">
        <form method="POST" action="{{ route('alunos.update', $aluno) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-700 dark:bg-rose-900/20 dark:text-rose-300">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="nome" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nome Completo</label>
                    <input id="nome" name="nome" type="text" value="{{ old('nome', $aluno->nome) }}" required
                        class="w-full rounded-xl border border-slate-300 bg-white/90 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200">
                </div>

                <div>
                    <label for="email" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">E-mail</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $aluno->email) }}" required
                        class="w-full rounded-xl border border-slate-300 bg-white/90 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200">
                </div>

                <div>
                    <label for="telefone" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Telefone / WhatsApp</label>
                    <input id="telefone" name="telefone" type="text" value="{{ old('telefone', $aluno->telefone) }}" required
                        class="w-full rounded-xl border border-slate-300 bg-white/90 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200">
                </div>

                <div>
                    <label for="plano_aulas_semana" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Plano mensal</label>
                    <select id="plano_aulas_semana" name="plano_aulas_semana" required
                        class="w-full rounded-xl border border-slate-300 bg-white/90 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200">
                        <option value="2" @selected((int) old('plano_aulas_semana', $aluno->plano_aulas_semana ?? 2) === 2)>2x por semana (R$ 200,00)</option>
                        <option value="3" @selected((int) old('plano_aulas_semana', $aluno->plano_aulas_semana) === 3)>3x por semana (R$ 300,00)</option>
                    </select>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">2x/semana = R$ 200,00 | 3x/semana = R$ 300,00</p>
                </div>

                <div>
                    <label for="professor_id" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Professor Responsável</label>
                    <select id="professor_id" name="professor_id"
                        class="w-full rounded-xl border border-slate-300 bg-white/90 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200">
                        <option value="">Selecione</option>
                        @foreach ($professores as $professor)
                            <option value="{{ $professor->id }}" @selected((string) old('professor_id', $aluno->professor_id) === (string) $professor->id)>
                                {{ $professor->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="cpf" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">CPF</label>
                    <input id="cpf" name="cpf" type="text" value="{{ old('cpf', $aluno->cpf) }}"
                        class="w-full rounded-xl border border-slate-300 bg-white/90 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    Salvar Alterações
                </button>
                <a href="{{ route('alunos.index') }}" class="text-sm text-slate-600 underline dark:text-slate-300">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
