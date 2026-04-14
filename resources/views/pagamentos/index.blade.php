@extends('layouts.vitali-dashboard')

@section('title', 'Pagamentos - Studio Vitali')

@section('content')
    <div x-data="pagamentosPage()" x-init="init()">
        <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Pagamentos</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300">Controle de recebimentos e comissao por professora.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Voltar ao Dashboard
            </a>
        </div>

        {{-- Painel unico de filtros: mes + status + metodo + professora + acao Buscar --}}
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white/90 p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
            <div class="mb-4 flex flex-col gap-1 border-b border-slate-200 pb-3 dark:border-slate-600">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">Filtros</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Escolha a competencia e os filtros, depois clique em Buscar para ver as alunas e o status de cada pagamento.</p>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-3">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Competencia (mes)</label>
                    <input type="month"
                           x-model="filters.competencia"
                           class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm [color-scheme:light] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:[color-scheme:dark]">
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</label>
                    <select x-model="filters.status"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                        <option value="">Todos</option>
                        <option value="PAGO">PAGO</option>
                        <option value="PENDENTE">PENDENTE</option>
                        <option value="ATRASADO">ATRASADO</option>
                        <option value="CANCELADO">CANCELADO</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Metodo</label>
                    <select x-model="filters.metodo"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                        <option value="">Todos</option>
                        <option value="PIX">PIX</option>
                        <option value="DINHEIRO">DINHEIRO</option>
                        <option value="CARTAO">CARTAO</option>
                        <option value="BOLETO">BOLETO</option>
                        <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                    </select>
                </div>
                <div class="lg:col-span-3">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Professora</label>
                    <select x-model="activeProfessor"
                            :disabled="isProfessorMode"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                        <option value="">Todas</option>
                        <template x-for="nome in professorasFromItems" :key="nome">
                            <option :value="nome" x-text="nome"></option>
                        </template>
                    </select>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row lg:col-span-2">
                    <button type="button"
                            @click="buscar()"
                            class="flex-1 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                        Buscar
                    </button>
                    <button type="button"
                            @click="limparFiltros()"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        Limpar
                    </button>
                </div>
            </div>
        </div>

        <div x-show="loading" class="rounded-2xl border border-slate-200 bg-white/70 p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
            Carregando pagamentos...
        </div>

        <div x-show="error" x-text="error" class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-700 dark:bg-rose-900/20 dark:text-rose-300"></div>

        {{-- Estado inicial: ainda nao buscou --}}
        <div x-show="!loading && !error && !hasBusca"
             class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/80 p-10 text-center dark:border-slate-600 dark:bg-slate-900/40">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Selecione a competencia e clique em <span class="font-semibold text-indigo-600 dark:text-indigo-400">Buscar</span> para ver as alunas e o status dos pagamentos.</p>
        </div>

        {{-- Apos busca: resumo + tabela --}}
        <template x-if="hasBusca">
            <div>
                <div x-show="!isProfessorMode" class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
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

                <div x-show="visibleItems().length === 0 && !loading"
                     class="mb-4 rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                    Nenhum pagamento encontrado com estes filtros nesta competencia.
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700" x-show="visibleItems().length > 0 || loading">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-100/80 dark:bg-slate-800/80">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Professora</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Aluna</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Metodo</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Mensalidade</th>
                                    <th x-show="!isProfessorMode" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Valor liquido</th>
                                    <th x-show="!isProfessorMode" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Comissao</th>
                                    <th x-show="!isProfessorMode" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Estudio</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Comprovante</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white/70 dark:divide-slate-700 dark:bg-slate-900/60">
                                <template x-for="item in visibleItems()" :key="item.id">
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200" x-text="item.professora_nome"></td>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white" x-text="item.aluna_nome ?? ('Aluna #' + item.aluna_id)"></td>
                                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                                            <select
                                                class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs font-semibold text-slate-700 shadow-sm transition focus:border-indigo-500 focus:outline-none dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200"
                                                :value="item.metodo"
                                                :disabled="isSavingMetodo(item.id)"
                                                @change="atualizarMetodo(item, $event.target.value)"
                                            >
                                                <option value="PIX">PIX</option>
                                                <option value="DINHEIRO">DINHEIRO</option>
                                                <option value="CARTAO">CARTAO</option>
                                                <option value="BOLETO">BOLETO</option>
                                                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200" x-text="money(item.valor_bruto)"></td>
                                        <td x-show="!isProfessorMode" class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200" x-text="money(item.valor_liquido)"></td>
                                        <td x-show="!isProfessorMode" class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
                                            <span x-text="money(item.valor_comissao)"></span>
                                            <span class="text-xs text-slate-500 dark:text-slate-400" x-text="'(' + item.percentual_comissao + '%)'"></span>
                                        </td>
                                        <td x-show="!isProfessorMode" class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200" x-text="money(item.valor_estudio)"></td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(item.status)" x-text="item.status"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-col gap-2">
                                                <input type="file"
                                                       class="hidden"
                                                       :id="'comprovante-file-' + item.id"
                                                       accept=".pdf,.png,.jpg,.jpeg,.webp"
                                                       @change="onFileChosen(item, $event)">
                                                <button type="button"
                                                        class="inline-flex w-full max-w-[11rem] items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                                        :disabled="isUploading(item.id)"
                                                        @click="abrirSeletorArquivo(item.id)">
                                                    <span x-text="isUploading(item.id) ? 'Enviando...' : 'Anexar arquivo'"></span>
                                                </button>
                                                <div class="mt-1 space-y-2 border-t border-slate-200 pt-2 dark:border-slate-600">
                                                    <template x-for="c in item.comprovantes || []" :key="c.id">
                                                        <div class="flex flex-wrap items-center gap-1.5">
                                                            <button type="button"
                                                                    class="rounded-lg bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-800 transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600"
                                                                    :title="c.nome_arquivo"
                                                                    @click="visualizarComprovante(c.url)">
                                                                Visualizar
                                                            </button>
                                                            <button type="button"
                                                                    class="rounded-lg border border-rose-300 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 disabled:opacity-60 dark:border-rose-700 dark:bg-rose-900/30 dark:text-rose-300 dark:hover:bg-rose-900/50"
                                                                    :title="'Excluir ' + c.nome_arquivo"
                                                                    :disabled="isDeletingComprovante(c.id)"
                                                                    @click="excluirComprovante(item, c)">
                                                                <span x-text="isDeletingComprovante(c.id) ? '...' : 'Excluir'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        function pagamentosPage() {
            const currentUserPerfil = @js(auth()->user()->perfil ?? null);
            const currentUserName = @js(auth()->user()->name ?? null);
            return {
                loading: false,
                error: '',
                items: [],
                hasBusca: false,
                activeProfessor: '',
                isProfessorMode: currentUserPerfil === 'Professor',
                currentProfessorName: currentUserName,
                uploading: {},
                deletingComprovante: {},
                savingMetodo: {},
                filters: {
                    competencia: '',
                    status: '',
                    metodo: '',
                },
                init() {
                    const now = new Date();
                    const y = now.getFullYear();
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    this.filters.competencia = `${y}-${m}`;
                    if (this.isProfessorMode) {
                        this.activeProfessor = this.currentProfessorName || '';
                    }
                    // Busca automatica ao abrir, usando o mes atual.
                    this.load();
                },
                get professorasFromItems() {
                    return [...new Set(this.items.map((i) => i.professora_nome).filter(Boolean))].sort();
                },
                buscar() {
                    if (!this.filters.competencia) {
                        this.error = 'Selecione a competencia (mes) antes de buscar.';
                        return;
                    }
                    this.error = '';
                    this.load();
                },
                limparFiltros() {
                    this.filters.competencia = '';
                    this.filters.status = '';
                    this.filters.metodo = '';
                    this.activeProfessor = '';
                    this.error = '';
                    this.hasBusca = false;
                    this.items = [];
                },
                async load() {
                    this.loading = true;
                    this.error = '';
                    try {
                        const params = new URLSearchParams();
                        params.set('competencia', this.filters.competencia);
                        if (this.filters.status) {
                            params.set('status', this.filters.status);
                        }
                        if (this.filters.metodo) {
                            params.set('metodo', this.filters.metodo);
                        }
                        const response = await fetch(`{{ route('api.v1.pagamentos.index') }}?${params.toString()}`);
                        if (!response.ok) {
                            throw new Error('Nao foi possivel carregar os pagamentos.');
                        }
                        this.items = await response.json();
                        this.hasBusca = true;
                        if (this.isProfessorMode) {
                            this.activeProfessor = this.currentProfessorName || '';
                        }
                        if (this.activeProfessor && !this.professorasFromItems.includes(this.activeProfessor)) {
                            this.activeProfessor = '';
                        }
                    } catch (e) {
                        this.error = e.message || 'Erro inesperado ao carregar pagamentos.';
                        this.hasBusca = false;
                    } finally {
                        this.loading = false;
                    }
                },
                filteredItems() {
                    return this.items;
                },
                visibleItems() {
                    if (!this.activeProfessor) {
                        return this.filteredItems();
                    }
                    return this.filteredItems().filter((item) => item.professora_nome === this.activeProfessor);
                },
                totalRecebido() {
                    return this.visibleItems().reduce((sum, item) => sum + Number(item.valor_liquido || 0), 0);
                },
                totalComissao() {
                    return this.visibleItems().reduce((sum, item) => sum + Number(item.valor_comissao || 0), 0);
                },
                totalEstudio() {
                    return this.visibleItems().reduce((sum, item) => sum + Number(item.valor_estudio || 0), 0);
                },
                abrirSeletorArquivo(itemId) {
                    document.getElementById('comprovante-file-' + itemId)?.click();
                },
                onFileChosen(item, event) {
                    const file = event.target.files?.[0];
                    event.target.value = '';
                    if (!file) {
                        return;
                    }
                    this.enviarComprovanteDireto(item, file);
                },
                isUploading(itemId) {
                    return Boolean(this.uploading[itemId]);
                },
                isSavingMetodo(itemId) {
                    return Boolean(this.savingMetodo[itemId]);
                },
                isDeletingComprovante(comprovanteId) {
                    return Boolean(this.deletingComprovante[comprovanteId]);
                },
                async atualizarMetodo(item, metodo) {
                    const previous = item.metodo;
                    item.metodo = metodo;
                    this.error = '';
                    this.savingMetodo[item.id] = true;
                    try {
                        const response = await fetch(`{{ url('/api/v1/pagamentos') }}/${item.id}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ metodo }),
                        });
                        if (!response.ok) {
                            throw new Error('Nao foi possivel atualizar o metodo de pagamento.');
                        }
                    } catch (e) {
                        item.metodo = previous;
                        this.error = e.message || 'Erro ao atualizar metodo.';
                    } finally {
                        this.savingMetodo[item.id] = false;
                    }
                },
                visualizarComprovante(url) {
                    if (!url) {
                        return;
                    }
                    const href = url.startsWith('http') ? url : (window.location.origin + url);
                    window.open(href, '_blank', 'noopener,noreferrer');
                },
                async excluirComprovante(item, comprovante) {
                    if (!confirm('Excluir este comprovante? O status volta para pendente se nao houver mais anexos.')) {
                        return;
                    }
                    this.error = '';
                    this.deletingComprovante[comprovante.id] = true;
                    try {
                        const response = await fetch(`{{ url('/api/v1/pagamentos') }}/${item.id}/comprovantes/${comprovante.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        });
                        if (!response.ok) {
                            throw new Error('Nao foi possivel excluir o comprovante.');
                        }
                        await this.load();
                    } catch (e) {
                        this.error = e.message || 'Erro ao excluir comprovante.';
                    } finally {
                        this.deletingComprovante[comprovante.id] = false;
                    }
                },
                async enviarComprovanteDireto(item, file) {
                    const competencia = this.filters.competencia;
                    if (!competencia) {
                        this.error = 'Competencia invalida. Busque novamente.';
                        return;
                    }

                    this.error = '';
                    this.uploading[item.id] = true;
                    try {
                        const form = new FormData();
                        form.append('competencia', competencia);
                        form.append('arquivo', file);

                        const response = await fetch(`{{ url('/api/v1/pagamentos') }}/${item.id}/comprovantes`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: form,
                        });

                        if (!response.ok) {
                            throw new Error('Nao foi possivel anexar comprovante.');
                        }

                        await this.load();
                    } catch (e) {
                        this.error = e.message || 'Erro ao anexar comprovante.';
                    } finally {
                        this.uploading[item.id] = false;
                    }
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
