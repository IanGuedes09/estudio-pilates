@extends('layouts.vitali-dashboard')

@section('title', 'Pagamentos - Studio Vitali')

@section('content')
    <div x-data="pagamentosPage()" x-init="load()">
        <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Pagamentos</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300">Controle de recebimentos e comissao por professora.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Voltar ao Dashboard
            </a>
        </div>

        <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Competencia</label>
                <input type="month" x-model="filters.competencia" class="w-full rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</label>
                <select x-model="filters.status" class="w-full rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
                    <option value="">Todos</option>
                    <option value="PAGO">PAGO</option>
                    <option value="PENDENTE">PENDENTE</option>
                    <option value="ATRASADO">ATRASADO</option>
                    <option value="CANCELADO">CANCELADO</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Metodo</label>
                <select x-model="filters.metodo" class="w-full rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
                    <option value="">Todos</option>
                    <option value="PIX">PIX</option>
                    <option value="DINHEIRO">DINHEIRO</option>
                    <option value="CARTAO">CARTAO</option>
                    <option value="BOLETO">BOLETO</option>
                    <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                </select>
            </div>
        </div>

        <div x-show="loading" class="rounded-2xl border border-slate-200 bg-white/70 p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
            Carregando pagamentos...
        </div>

        <div x-show="error" x-text="error" class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-700 dark:bg-rose-900/20 dark:text-rose-300"></div>

        <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3" x-show="!loading && !error">
            <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Valor recebido</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white" x-text="money(totalRecebido())"></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Comissao professoras</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white" x-text="money(totalComissao())"></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                <p class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Liquido do estudio</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white" x-text="money(totalEstudio())"></p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700" x-show="!loading && !error">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-100/80 dark:bg-slate-800/80">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Aluna</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Competencia</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Metodo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Valor liquido</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Comissao</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Estudio</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white/70 dark:divide-slate-700 dark:bg-slate-900/60">
                    <template x-for="item in filteredItems()" :key="item.id">
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white" x-text="item.aluna_nome ?? ('Aluna #' + item.aluna_id)"></td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200" x-text="item.competencia"></td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200" x-text="item.metodo"></td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200" x-text="money(item.valor_liquido)"></td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                                <span x-text="money(item.valor_comissao)"></span>
                                <span class="text-xs text-slate-500 dark:text-slate-400" x-text="'(' + item.percentual_comissao + '%)'"></span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200" x-text="money(item.valor_estudio)"></td>
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
        function pagamentosPage() {
            return {
                loading: false,
                error: '',
                items: [],
                filters: {
                    competencia: '',
                    status: '',
                    metodo: '',
                },
                // Carrega os dados do endpoint mock para validar o fluxo do front.
                async load() {
                    this.loading = true;
                    this.error = '';
                    try {
                        const response = await fetch('{{ route('api.v1.pagamentos.index') }}');
                        if (!response.ok) {
                            throw new Error('Nao foi possivel carregar os pagamentos.');
                        }
                        this.items = await response.json();
                    } catch (e) {
                        this.error = e.message || 'Erro inesperado ao carregar pagamentos.';
                    } finally {
                        this.loading = false;
                    }
                },
                filteredItems() {
                    return this.items.filter((item) => {
                        const byCompetencia = !this.filters.competencia || item.competencia === this.filters.competencia;
                        const byStatus = !this.filters.status || item.status === this.filters.status;
                        const byMetodo = !this.filters.metodo || item.metodo === this.filters.metodo;
                        return byCompetencia && byStatus && byMetodo;
                    });
                },
                totalRecebido() {
                    return this.filteredItems().reduce((sum, item) => sum + Number(item.valor_liquido || 0), 0);
                },
                totalComissao() {
                    return this.filteredItems().reduce((sum, item) => sum + Number(item.valor_comissao || 0), 0);
                },
                totalEstudio() {
                    return this.filteredItems().reduce((sum, item) => sum + Number(item.valor_estudio || 0), 0);
                },
                money(value) {
                    return Number(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                },
                statusClass(status) {
                    const map = {
                        PAGO: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
                        PENDENTE: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                        ATRASADO: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
                        CANCELADO: 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300',
                    };
                    return map[status] || 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300';
                },
            };
        }
    </script>
@endsection
