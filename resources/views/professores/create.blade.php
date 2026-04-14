@extends('layouts.vitali-dashboard')

@section('title', 'Cadastro de Professores - Studio Vitali')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Cadastro de Professores</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Dados do professor e credenciais de acesso ao sistema.</p>
    </div>

    <form action="{{ route('professores.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label for="nome" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Nome *</label>
            <input
                id="nome"
                name="nome"
                type="text"
                value="{{ old('nome') }}"
                required
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
            @error('nome')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="cpf" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">CPF *</label>
            <input
                id="cpf"
                name="cpf"
                type="text"
                value="{{ old('cpf') }}"
                required
                inputmode="numeric"
                autocomplete="off"
                placeholder="000.000.000-00"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Somente números ou no formato com pontuação.</p>
            @error('cpf')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">E-mail (login) *</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Será o usuário para entrar no sistema.</p>
            @error('email')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Senha *</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="new-password"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
            @error('password')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Confirmar senha *</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
        </div>

        <div>
            <label for="telefone" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Telefone</label>
            <input
                id="telefone"
                name="telefone"
                type="text"
                value="{{ old('telefone') }}"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
            @error('telefone')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="comissao_percentual" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Comissão por aluno (%) *</label>
            <input
                id="comissao_percentual"
                name="comissao_percentual"
                type="number"
                step="0.01"
                min="0"
                max="100"
                value="{{ old('comissao_percentual', '0') }}"
                required
                class="w-full max-w-xs rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
            />
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Percentual que o professor recebe sobre cada aluno atendido por ele.</p>
            @error('comissao_percentual')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input id="ativo" name="ativo" type="checkbox" value="1" @checked(old('ativo', true)) class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
            <label for="ativo" class="text-sm text-slate-700 dark:text-slate-200">Professor ativo</label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Salvar
            </button>
            <a href="{{ route('professores.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                Cancelar
            </a>
        </div>
    </form>
@endsection
