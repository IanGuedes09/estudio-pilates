@extends('layouts.vitali-dashboard')

@section('title', 'Editar Professor - Studio Vitali')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Editar Professor</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Atualize os dados do professor e, se quiser, redefina a senha.</p>
    </div>

    <form action="{{ route('professores.update', $professor) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="nome" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Nome *</label>
            <input id="nome" name="nome" type="text" value="{{ old('nome', $professor->nome) }}" required
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" />
            @error('nome')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="cpf" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">CPF *</label>
            <input id="cpf" name="cpf" type="text" value="{{ old('cpf', $professor->cpf) }}" required inputmode="numeric" placeholder="000.000.000-00"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" />
            @error('cpf')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">E-mail (login) *</label>
            <input id="email" name="email" type="email" value="{{ old('email', $professor->user?->email ?? $professor->email) }}" required
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" />
            @error('email')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="password" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Nova senha (opcional)</label>
                <input id="password" name="password" type="password"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" />
                @error('password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Confirmar nova senha</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" />
            </div>
        </div>

        <div>
            <label for="telefone" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Telefone</label>
            <input id="telefone" name="telefone" type="text" value="{{ old('telefone', $professor->telefone) }}"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" />
            @error('telefone')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="comissao_percentual" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Comissão por aluno (%) *</label>
            <input id="comissao_percentual" name="comissao_percentual" type="number" step="0.01" min="0" max="100"
                value="{{ old('comissao_percentual', number_format((float) $professor->comissao_percentual, 2, '.', '')) }}" required
                class="w-full max-w-xs rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" />
            @error('comissao_percentual')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input id="ativo" name="ativo" type="checkbox" value="1" @checked(old('ativo', $professor->ativo)) class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
            <label for="ativo" class="text-sm text-slate-700 dark:text-slate-200">Professor ativo</label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Salvar alterações
            </button>
            <a href="{{ route('professores.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                Cancelar
            </a>
        </div>
    </form>
@endsection
