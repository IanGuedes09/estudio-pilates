@extends('layouts.vitali-dashboard')

@section('title', 'Dashboard - Studio Vitali')

@section('content')
    @php
        $isProfessor = (auth()->user()->perfil ?? null) === 'Professor';
        $todayBr = now()->format('d/m');
    @endphp

    <div class="fade-in-up">
        <h1 class="mb-2 text-3xl font-bold text-slate-900 dark:text-white">Bem-vindo ao Dashboard</h1>
        <p class="mb-6 text-sm text-slate-600 dark:text-slate-300">Acompanhe os atalhos principais do Studio Vitali.</p>
    </div>

    <p class="mb-2 text-slate-700 dark:text-slate-200">
        Você está logado como
        <strong>{{ auth()->user()->name }}</strong>
        (Perfil: <strong>{{ auth()->user()->perfil ?? 'Usuário' }}</strong>)
    </p>

    <p class="mb-6 text-slate-600 dark:text-slate-300">
        Aqui você pode colocar links para seus módulos, relatórios e indicadores principais do estúdio.
    </p>

    @if (!$isProfessor)
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Alunas ativas</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $resumo['alunas_ativas'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Aulas hoje</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $resumo['aulas_hoje'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Receita mês</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">R$ {{ number_format($resumo['receita_mes'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Lucro estúdio</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">R$ {{ number_format($resumo['lucro_estudio_mes'], 2, ',', '.') }}</p>
            </div>
        </div>
    @endif

    <div class="fade-in-up-delay grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        <a href="{{ route('agenda.index') }}" class="group rounded-2xl border border-emerald-300/60 bg-emerald-400/10 p-5 text-emerald-900 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-400/20 dark:border-emerald-400/40 dark:bg-emerald-500/10 dark:text-emerald-100">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/20 text-emerald-700 dark:text-emerald-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-xs uppercase tracking-wider text-emerald-700 dark:text-emerald-200">Agenda</p>
            <p class="mt-2 text-lg font-semibold">Visualizar Agenda</p>
            <p class="mt-1 text-sm text-emerald-800/80 dark:text-emerald-100/80">Consulte e filtre as aulas do dia.</p>
        </a>

        <a href="{{ route('alunos.index') }}" class="group rounded-2xl border border-indigo-300/50 bg-indigo-500/10 p-5 text-indigo-900 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:bg-indigo-500/20 dark:border-indigo-400/40 dark:bg-indigo-500/15 dark:text-indigo-100">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-500/20 text-indigo-700 dark:text-indigo-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V7a2 2 0 012-2h3l2-2h4l2 2h3a2 2 0 012 2v12a2 2 0 01-2 2z" />
                </svg>
            </div>
            <p class="text-xs uppercase tracking-wider text-indigo-700 dark:text-indigo-200">Módulo</p>
            <p class="mt-2 text-lg font-semibold">Gestão de Alunos</p>
            <p class="mt-1 text-sm text-indigo-800/80 dark:text-indigo-100/80">Acessar cadastros e histórico de alunos.</p>
        </a>

        <a href="{{ route('pagamentos.index') }}" class="group rounded-2xl border border-violet-300/60 bg-violet-400/10 p-5 text-violet-900 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-300 hover:bg-violet-400/20 dark:border-violet-400/40 dark:bg-violet-500/10 dark:text-violet-100">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-violet-500/20 text-violet-700 dark:text-violet-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 .895-4 2s1.79 2 4 2 4 .895 4 2-1.79 2-4 2m0-10v10m0-10c1.11 0 2.08.228 2.8.58M12 8c-1.11 0-2.08.228-2.8.58M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-xs uppercase tracking-wider text-violet-700 dark:text-violet-200">Financeiro</p>
            <p class="mt-2 text-lg font-semibold">Controle de Pagamentos</p>
            <p class="mt-1 text-sm text-violet-800/80 dark:text-violet-100/80">Acompanhe recebimentos, comissao e repasses.</p>
        </a>

        @if (!$isProfessor)
            <a href="{{ route('alunos.create') }}" class="group rounded-2xl border border-cyan-300/60 bg-cyan-400/10 p-5 text-cyan-900 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-400/20 dark:border-cyan-400/40 dark:bg-cyan-500/10 dark:text-cyan-100">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-500/20 text-cyan-700 dark:text-cyan-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <p class="text-xs uppercase tracking-wider text-cyan-700 dark:text-cyan-200">Ação rápida</p>
                <p class="mt-2 text-lg font-semibold">Cadastrar Novo Aluno</p>
                <p class="mt-1 text-sm text-cyan-800/80 dark:text-cyan-100/80">Adicionar um novo aluno em poucos passos.</p>
            </a>
        @endif

        @if ((auth()->user()->perfil ?? null) === 'Administrador')
            <a href="{{ route('professores.create') }}" class="group rounded-2xl border border-amber-300/60 bg-amber-400/10 p-5 text-amber-900 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:bg-amber-400/20 dark:border-amber-400/40 dark:bg-amber-500/10 dark:text-amber-100">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/20 text-amber-700 dark:text-amber-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <p class="text-xs uppercase tracking-wider text-amber-700 dark:text-amber-200">Administração</p>
                <p class="mt-2 text-lg font-semibold">Cadastro de Professores</p>
                <p class="mt-1 text-sm text-amber-800/80 dark:text-amber-100/80">Crie o acesso e os dados do professor no sistema.</p>
            </a>
        @endif
    </div>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white/70 p-5 dark:border-slate-700 dark:bg-slate-900/60">
        <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Aulas do dia ({{ $todayBr }})</h2>
        @if (count($proximasAulas) === 0)
            <p class="text-sm text-slate-500 dark:text-slate-400">Nenhuma aula para hoje na agenda.</p>
        @else
            <ul class="space-y-2">
                @foreach ($proximasAulas as $aula)
                    <li class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 dark:border-slate-700">
                        <span class="text-sm text-slate-700 dark:text-slate-200">{{ $aula['hora_inicio'] }} - {{ $aula['aluna_nome'] }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($aula['data'])->format('d/m') }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $aula['tipo_aula'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection