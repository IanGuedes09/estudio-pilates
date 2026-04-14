@extends('layouts.vitali-dashboard')

@section('title', 'Alunos - Studio Vitali')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />
    <style>
        .alertify .ajs-dialog {
            border: 1px solid rgba(148, 163, 184, 0.35);
            border-radius: 16px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
            overflow: hidden;
            max-width: 520px;
        }

        .alertify .ajs-header {
            background: linear-gradient(90deg, #4f46e5 0%, #6366f1 100%);
            color: #ffffff;
            border-bottom: 0;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 14px 18px;
        }

        .alertify .ajs-body {
            color: #334155;
            font-size: 0.96rem;
            line-height: 1.5;
            padding: 18px;
        }

        .alertify .ajs-footer {
            border-top: 1px solid rgba(148, 163, 184, 0.25);
            background: rgba(248, 250, 252, 0.9);
            padding: 12px 16px;
        }

        .alertify .ajs-button {
            border-radius: 10px;
            border: 0;
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0.02em;
            padding: 8px 14px;
            margin-left: 8px;
        }

        .alertify .ajs-ok {
            background: #e11d48;
            color: #ffffff;
        }

        .alertify .ajs-ok:hover {
            background: #be123c;
        }

        .alertify .ajs-cancel {
            background: #be123c;
            color: #ffffff;
        }

        .alertify .ajs-cancel:hover {
            background: #9f1239;
        }

        .dark .alertify .ajs-dialog {
            border: 1px solid rgba(99, 102, 241, 0.35);
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.55);
        }

        .dark .alertify .ajs-header {
            background: linear-gradient(90deg, #312e81 0%, #4338ca 100%);
            color: #e2e8f0;
        }

        .dark .alertify .ajs-body {
            color: #cbd5e1;
        }

        .dark .alertify .ajs-footer {
            border-top: 1px solid rgba(99, 102, 241, 0.25);
            background: rgba(15, 23, 42, 0.75);
        }

        .dark .alertify .ajs-cancel {
            background: #be123c;
            color: #ffffff;
        }

        .dark .alertify .ajs-cancel:hover {
            background: #9f1239;
        }
    </style>

    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Alunos</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">Cadastro principal de alunas para agenda, pagamentos e dashboard.</p>
        </div>
        <a href="{{ route('alunos.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
            Novo aluno
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-700 dark:bg-rose-900/20 dark:text-rose-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/60">
            <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Alunas cadastradas</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $alunos->count() }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/60">
            <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Com agenda vinculada</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $alunos->where('agenda_items_count', '>', 0)->count() }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/60">
            <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Com pagamentos vinculados</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $alunos->where('pagamentos_count', '>', 0)->count() }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
        @if ($alunos->isEmpty())
            <div class="bg-white/70 p-6 text-sm text-slate-600 dark:bg-slate-900/60 dark:text-slate-300">
                Nenhum aluno cadastrado ainda.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-100/80 dark:bg-slate-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">E-mail</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Telefone</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Mensalidade</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Professor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Agenda</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Pagamentos</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white/70 dark:divide-slate-700 dark:bg-slate-900/60">
                        @foreach ($alunos as $aluno)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white">{{ $aluno->nome }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $aluno->email ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $aluno->telefone }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">R$ {{ number_format((float) $aluno->valor_mensalidade, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $aluno->professor?->nome ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $aluno->agenda_items_count }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $aluno->pagamentos_count }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('alunos.edit', $aluno) }}" class="rounded-lg bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-200 dark:bg-indigo-500/20 dark:text-indigo-300 dark:hover:bg-indigo-500/30">
                                            Editar
                                        </a>
                                        <form method="POST" action="{{ route('alunos.destroy', $aluno) }}" class="delete-aluno-form" data-aluno-nome="{{ $aluno->nome }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-200 dark:bg-rose-500/20 dark:text-rose-300 dark:hover:bg-rose-500/30">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('.delete-aluno-form');
            forms.forEach((form) => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const nome = form.dataset.alunoNome || 'esta aluna';
                    alertify.confirm(
                        'Confirmar exclusão',
                        `Excluir ${nome}? Esta ação não poderá ser desfeita.`,
                        function () {
                            form.submit();
                        },
                        function () {}
                    ).set('labels', { ok: 'Excluir', cancel: 'Cancelar' });
                });
            });
        });
    </script>
@endsection
