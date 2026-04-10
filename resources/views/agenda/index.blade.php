@extends('layouts.vitali-dashboard')

@section('title', 'Agenda - Studio Vitali')

@section('content')
    <div x-data="agendaPage()" x-init="load()">
        <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Agenda</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300">Visualize e filtre as aulas do dia.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Voltar ao Dashboard
            </a>
        </div>

        <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Data</label>
                <input type="date" x-model="filters.data" class="w-full rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Professora</label>
                <select x-model="filters.professora" class="w-full rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
                    <option value="">Todas</option>
                    <template x-for="nome in professoras" :key="nome">
                        <option :value="nome" x-text="nome"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</label>
                <select x-model="filters.status" class="w-full rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
                    <option value="">Todos</option>
                    <option value="AGENDADA">AGENDADA</option>
                    <option value="CONFIRMADA">CONFIRMADA</option>
                    <option value="REALIZADA">REALIZADA</option>
                    <option value="FALTOU">FALTOU</option>
                    <option value="CANCELADA">CANCELADA</option>
                </select>
            </div>
        </div>

        <div x-show="loading" class="rounded-2xl border border-slate-200 bg-white/70 p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
            Carregando agenda...
        </div>

        <div x-show="error" x-text="error" class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-700 dark:bg-rose-900/20 dark:text-rose-300"></div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700" x-show="!loading && !error">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-100/80 dark:bg-slate-800/80">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Horario</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Aluna</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Professora</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white/70 dark:divide-slate-700 dark:bg-slate-900/60">
                    <template x-for="item in filteredItems()" :key="item.id">
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700 dark:text-slate-200" x-text="item.hora_inicio + ' - ' + item.hora_fim"></td>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white" x-text="item.aluna.nome"></td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200" x-text="item.professora.nome"></td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200" x-text="item.tipo_aula"></td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(item.status)" x-text="item.status"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function agendaPage() {
            return {
                loading: false,
                error: '',
                items: [],
                filters: {
                    data: '',
                    professora: '',
                    status: '',
                },
                get professoras() {
                    return [...new Set(this.items.map((item) => item.professora.nome))];
                },
                // Carrega agenda mockada para acelerar desenvolvimento front-first.
                async load() {
                    this.loading = true;
                    this.error = '';
                    try {
                        const response = await fetch('{{ route('api.v1.agenda.index') }}');
                        if (!response.ok) {
                            throw new Error('Nao foi possivel carregar a agenda.');
                        }
                        this.items = await response.json();
                    } catch (e) {
                        this.error = e.message || 'Erro inesperado ao carregar agenda.';
                    } finally {
                        this.loading = false;
                    }
                },
                filteredItems() {
                    // Filtros locais para simular comportamento final da API.
                    return this.items.filter((item) => {
                        const byData = !this.filters.data || item.data === this.filters.data;
                        const byProfessora = !this.filters.professora || item.professora.nome === this.filters.professora;
                        const byStatus = !this.filters.status || item.status === this.filters.status;
                        return byData && byProfessora && byStatus;
                    });
                },
                statusClass(status) {
                    const map = {
                        AGENDADA: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
                        CONFIRMADA: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
                        REALIZADA: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300',
                        FALTOU: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                        CANCELADA: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
                    };
                    return map[status] || 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300';
                },
            };
        }
    </script>
@endsection
