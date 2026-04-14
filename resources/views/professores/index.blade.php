@extends('layouts.vitali-dashboard')

@section('title', 'Professores - Studio Vitali')

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
    </style>

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Gestão de Professores</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Lista dos professores cadastrados no sistema.</p>
        </div>
        @if ((auth()->user()->perfil ?? null) === 'Administrador')
            <a href="{{ route('professores.create') }}" class="shrink-0 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Novo professor
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-300 bg-emerald-100 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-100">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-rose-300 bg-rose-100 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-100">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white/80 shadow-sm dark:border-slate-700 dark:bg-slate-900/60">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-100 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                <tr>
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">CPF</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Telefone</th>
                    <th class="px-4 py-3">Comissão / aluno</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($professores as $professor)
                    <tr class="border-t border-slate-200 dark:border-slate-700">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ $professor->nome }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300 font-mono text-xs">{{ $professor->cpf_formatado ?: '-' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $professor->user?->email ?? $professor->email ?: '-' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $professor->telefone ?: '-' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ number_format((float) $professor->comissao_percentual, 2, ',', '.') }}%</td>
                        <td class="px-4 py-3">
                            @if ($professor->ativo)
                                <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">Ativo</span>
                            @else
                                <span class="rounded-full bg-slate-200 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">Inativo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('professores.edit', $professor) }}" class="rounded-lg bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-200 dark:bg-indigo-500/20 dark:text-indigo-300 dark:hover:bg-indigo-500/30">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('professores.destroy', $professor) }}" class="delete-professor-form" data-professor-nome="{{ $professor->nome }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-200 dark:bg-rose-500/20 dark:text-rose-300 dark:hover:bg-rose-500/30">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                            Nenhum professor cadastrado ainda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('.delete-professor-form');
            forms.forEach((form) => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const nome = form.dataset.professorNome || 'este professor';
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
