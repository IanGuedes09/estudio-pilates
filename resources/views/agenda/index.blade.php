@extends('layouts.vitali-dashboard')

@section('title', 'Agenda - Studio Vitali')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />
    <div x-data="agendaPage()" x-init="init()">
        <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Agenda</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300">Quadro manual para organizar alunas por horario, no estilo da folha.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Voltar ao Dashboard
            </a>
        </div>

        <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Mes</label>
                <input type="month" x-model="filters.mes" @change="onMonthChange()" class="w-full rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Professora</label>
                <select x-model="filters.professora" @change="buildBoardFromItems()" :disabled="isProfessorMode" class="w-full rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
                    <option value="">Todas</option>
                    <template x-for="nome in professoras" :key="nome">
                        <option :value="nome" x-text="nome"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Semana do mes</label>
                <select x-model.number="filters.weekIndex" @change="buildBoardFromItems()" class="w-full rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
                    <template x-for="week in availableWeeks()" :key="week.value">
                        <option :value="week.value" x-text="week.label"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Periodo</label>
                <select x-model="filters.periodo" @change="buildBoardFromItems()" :disabled="isProfessorMode" class="w-full rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
                    <option value="">Todos</option>
                    <option value="MANHA">Manha (08h as 12h)</option>
                    <option value="TARDE">Tarde (14h as 19h)</option>
                </select>
                <p x-show="isProfessorMode" class="mt-1 text-[10px] font-medium text-slate-500 dark:text-slate-400">Perfil Professor: acesso restrito ao período da tarde.</p>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Acoes</label>
                <div class="grid grid-cols-1 gap-2">
                    <button type="button" @click="replicarSemanaNoMes()" :disabled="savingReplicar" class="w-full rounded-xl border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 disabled:opacity-60 dark:border-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300 dark:hover:bg-indigo-900/30">
                        <span x-text="savingReplicar ? 'Replicando...' : 'Replicar semana no mes'"></span>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="loading" class="rounded-2xl border border-slate-200 bg-white/70 p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
            Carregando agenda...
        </div>

        <div x-show="error" x-text="error" class="rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-700 dark:bg-rose-900/20 dark:text-rose-300"></div>

        <div x-show="!loading && !error" class="grid grid-cols-1 gap-4 lg:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 dark:border-slate-700 dark:bg-slate-900/60 lg:col-span-1">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Nomes disponiveis</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Alunas cadastradas (e aulas ainda sem horario neste mes) — arraste para o horario.</p>
                <div class="mt-2">
                    <input
                        type="text"
                        x-model.trim="poolSearch"
                        placeholder="Filtrar por nome..."
                        class="w-full rounded-lg border border-slate-300 bg-white/90 px-2.5 py-1.5 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200"
                    >
                </div>
                <div class="mt-3 min-h-40 rounded-xl border border-dashed border-slate-300 p-2 dark:border-slate-700"
                     @dragover.prevent
                     @drop.prevent="dropOnPool()">
                    <template x-if="filteredPoolEntries().length === 0">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Sem nomes na fila.</p>
                    </template>
                    <template x-for="entry in filteredPoolEntries()" :key="entry.poolKey">
                        <div class="mb-2 cursor-move rounded-lg bg-indigo-100 px-2 py-1 text-sm font-medium text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300"
                             draggable="true"
                             @dragstart="startDragFromPool(entry)">
                            <p><span x-text="entry.name"></span> <span class="text-[10px] font-bold" x-show="entry.slotLabel" x-text="'(' + entry.slotLabel + ')'"></span></p>
                            <p class="text-[10px] font-medium text-indigo-700/80 dark:text-indigo-300/80"
                               x-show="entry.professorAlunoNome"
                               x-text="'Prof.: ' + entry.professorAlunoNome"></p>
                        </div>
                    </template>
                </div>

                <h2 class="mt-4 text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Reposicao pendente</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Faltas de ultima hora ficam aqui para remanejar.</p>
                <div class="mt-3 min-h-40 rounded-xl border border-dashed border-amber-300 p-2 dark:border-amber-700"
                     @dragover.prevent
                     @drop.prevent="dropOnReposicaoQueue()">
                    <template x-if="reposicaoQueue.length === 0">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Sem reposicoes pendentes.</p>
                    </template>
                    <template x-for="entry in reposicaoQueue" :key="'q-' + (entry.agendaItemId || entry.id)">
                        <div class="mb-2 cursor-move rounded-lg bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300"
                             draggable="true"
                             @dragstart="startDragFromQueue(entry)"
                        >
                            <p><span x-text="entry.name"></span> <span class="text-[10px] font-bold" x-show="entry.slotLabel" x-text="'(' + entry.slotLabel + ')'"></span></p>
                            <p class="text-[10px] font-medium text-amber-700/80 dark:text-amber-300/80" x-show="entry.faltaData" x-text="'Faltou: ' + formatDate(entry.faltaData)"></p>
                        </div>
                    </template>
                </div>
            </div>

            <div class="overflow-auto rounded-2xl border border-slate-200 bg-white/70 p-2 dark:border-slate-700 dark:bg-slate-900/60 lg:col-span-4">
                <div class="min-w-[900px]">
                    <div class="grid grid-cols-6">
                        <div class="border-b border-r border-slate-200 p-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:text-slate-300">Horario</div>
                        <template x-for="day in displayedWeekDays()" :key="day.iso">
                            <div class="border-b border-r border-slate-200 p-2 text-xs font-semibold uppercase tracking-wider text-slate-500 last:border-r-0 dark:border-slate-700 dark:text-slate-300" x-text="day.label + ' ' + day.shortDate"></div>
                        </template>
                    </div>
                    <template x-for="hora in horarios" :key="hora">
                        <div class="grid grid-cols-6">
                            <div class="border-b border-r border-slate-200 p-2 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200" x-text="hora"></div>
                            <template x-for="day in displayedWeekDays()" :key="day.iso + hora">
                                <div class="min-h-20 border-b border-r border-slate-200 p-2 last:border-r-0 dark:border-slate-700"
                                     @dragover.prevent
                                     @drop.prevent="dropOnSlot(day.iso, hora)">
                                    <template x-for="entry in entriesForSlot(day.iso, hora)" :key="'s-' + (entry.agendaItemId || entry.id)">
                                        <div class="mb-1 rounded-lg px-2 py-1 text-xs font-semibold"
                                             :class="[
                                                isPastSlot(day.iso, hora) ? 'cursor-not-allowed opacity-75' : 'cursor-move',
                                                (entry.status === 'REPOSICAO' || entry.status === 'REPOSICAO_PENDENTE')
                                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300'
                                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300'
                                             ]"
                                             :draggable="!isPastSlot(day.iso, hora)"
                                             @dragstart="startDragFromSlot(entry, day.iso, hora)">
                                            <div class="flex items-center justify-between gap-1">
                                                <span x-text="entry.name"></span>
                                                <span class="rounded-full bg-white/70 px-1.5 py-0.5 text-[10px] font-bold text-slate-700 dark:bg-slate-900/70 dark:text-slate-200" x-text="entry.status"></span>
                                            </div>
                                            <p class="mt-0.5 text-[10px] font-medium text-slate-600 dark:text-slate-300"
                                               x-show="entry.professorAlunoNome"
                                               x-text="'Prof.: ' + entry.professorAlunoNome"></p>
                                            <div class="mt-1 flex gap-1">
                                                <button type="button"
                                                    class="rounded bg-amber-200 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-amber-600/20 dark:text-amber-300"
                                                    :disabled="isPastSlot(day.iso, hora)"
                                                    @click.stop="markAsFaltou(day.iso, hora, entry)">
                                                    Faltou
                                                </button>
                                                <button type="button"
                                                    class="rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-slate-700/40 dark:text-slate-200"
                                                    :disabled="isPastSlot(day.iso, hora)"
                                                    @click.stop="removeEntryFromSlot(day.iso, hora, entry)">
                                                    Remover
                                                </button>
                                            </div>
                                            <p class="mt-1 text-[10px] font-medium text-amber-700/80 dark:text-amber-300/80"
                                               x-show="entry.faltaData && (entry.status === 'REPOSICAO' || entry.status === 'REPOSICAO_PENDENTE')"
                                               x-text="'Faltou: ' + formatDate(entry.faltaData)"></p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function agendaPage() {
            const currentUserPerfil = @js(auth()->user()->perfil ?? null);
            const currentUserName = @js(auth()->user()->name ?? null);
            return {
                loading: false,
                error: '',
                items: [],
                isProfessorMode: currentUserPerfil === 'Professor',
                currentProfessorName: currentUserName,
                filters: {
                    mes: '',
                    professora: '',
                    periodo: '',
                    weekIndex: 0,
                },
                weekDays: [
                    { id: 'SEG', label: 'Segunda' },
                    { id: 'TER', label: 'Terca' },
                    { id: 'QUA', label: 'Quarta' },
                    { id: 'QUI', label: 'Quinta' },
                    { id: 'SEX', label: 'Sexta' },
                ],
                boardMap: {},
                reposicaoQueue: [],
                draggedEntry: null,
                dragOrigin: null,
                alunosCadastro: [],
                savingReplicar: false,
                poolSearch: '',
                get professoras() {
                    return [...new Set(this.items.map((item) => item.professora?.nome).filter(Boolean))];
                },
                get horarios() {
                    const manha = ['08:00', '09:00', '10:00', '11:00', '12:00'];
                    const tarde = ['14:00', '15:00', '16:00', '17:00', '18:00', '19:00'];
                    const base = this.filters.periodo === 'MANHA'
                        ? manha
                        : this.filters.periodo === 'TARDE'
                            ? tarde
                            : [...manha, ...tarde];
                    const dynamic = this.filteredItems().map((item) => String(item.hora_inicio || '').slice(0, 5)).filter(Boolean);
                    return [...new Set([...base, ...dynamic])].sort();
                },
                init() {
                    // Sempre abre na competencia e semana atuais.
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    this.filters.mes = `${year}-${month}`;
                    if (this.isProfessorMode) {
                        this.filters.periodo = 'TARDE';
                        this.filters.professora = this.currentProfessorName || '';
                    }
                    // Garante que o select da semana esteja montado antes de definir o valor.
                    this.$nextTick(() => {
                        const currentIdx = this.currentWeekIndex(now);
                        const maxIdx = Math.max(this.availableWeeks().length - 1, 0);
                        this.filters.weekIndex = Math.min(Math.max(currentIdx, 0), maxIdx);
                        this.load();
                    });
                },
                async load() {
                    this.loading = true;
                    this.error = '';
                    try {
                        // Busca agenda sem cortar por mes para manter os dias de borda da semana (ex: 29/12 na semana de janeiro).
                        const params = new URLSearchParams();
                        if (this.filters.professora) {
                            params.set('professora', this.filters.professora);
                        }
                        const response = await fetch(`{{ route('api.v1.agenda.index') }}?${params.toString()}`);
                        if (!response.ok) {
                            throw new Error('Nao foi possivel carregar a agenda.');
                        }
                        this.items = await response.json();
                        await this.loadAlunos();
                        this.buildBoardFromItems();
                    } catch (e) {
                        this.error = e.message || 'Erro inesperado ao carregar agenda.';
                    } finally {
                        this.loading = false;
                    }
                },
                filteredItems() {
                    const weekDates = new Set(this.displayedWeekDays().map((d) => d.iso));
                    return this.items.filter((item) => {
                        const byMonth = !this.filters.mes
                            || !item.data
                            || item.data.slice(0, 7) === this.filters.mes
                            || weekDates.has(item.data);
                        const byProfessora = !this.filters.professora || item.professora?.nome === this.filters.professora;
                        const activePeriodo = this.isProfessorMode ? 'TARDE' : this.filters.periodo;
                        const byPeriodo = !activePeriodo || this.matchesPeriodo(item.hora_inicio, activePeriodo);
                        return byMonth && byProfessora && byPeriodo;
                    });
                },
                matchesPeriodo(horaInicio, periodo) {
                    const hora = Number(String(horaInicio || '').slice(0, 2));
                    if (Number.isNaN(hora)) {
                        return true;
                    }
                    if (periodo === 'MANHA') {
                        return hora >= 8 && hora <= 12;
                    }
                    if (periodo === 'TARDE') {
                        return hora >= 14 && hora <= 19;
                    }
                    return true;
                },
                isPastSlot(dateIso, horaInicio) {
                    if (!dateIso) {
                        return false;
                    }
                    const target = new Date(`${dateIso}T12:00:00`);
                    const now = new Date();
                    const startOfWeek = (date) => {
                        const d = new Date(date.getFullYear(), date.getMonth(), date.getDate());
                        const day = d.getDay();
                        const diffToMonday = day === 0 ? -6 : 1 - day;
                        d.setDate(d.getDate() + diffToMonday);
                        d.setHours(0, 0, 0, 0);
                        return d;
                    };
                    return startOfWeek(target).getTime() < startOfWeek(now).getTime();
                },
                async loadAlunos() {
                    try {
                        const response = await fetch('{{ route('api.v1.alunos.catalogo') }}');
                        if (response.ok) {
                            this.alunosCadastro = await response.json();
                        }
                    } catch (e) {
                        /* silencioso: quadro ainda funciona com itens da agenda */
                    }
                },
                displayedWeekDays() {
                    const refMonth = this.filters.mes || new Date().toISOString().slice(0, 7);
                    const [year, month] = refMonth.split('-').map(Number);
                    const firstDay = new Date(year, month - 1, 1);
                    const weekday = firstDay.getDay();
                    const diffToMonday = weekday === 0 ? -6 : 1 - weekday;
                    const monday = new Date(firstDay);
                    monday.setDate(firstDay.getDate() + diffToMonday + (this.filters.weekIndex * 7));
                    return this.weekDays.map((day, index) => {
                        const date = new Date(monday);
                        date.setDate(monday.getDate() + index);
                        const iso = date.toISOString().slice(0, 10);
                        const shortDate = iso.slice(8, 10) + '/' + iso.slice(5, 7);
                        return { ...day, iso, shortDate };
                    });
                },
                currentWeekIndex(refDate) {
                    const date = refDate instanceof Date ? refDate : new Date();
                    const refMonth = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
                    const [year, month] = refMonth.split('-').map(Number);
                    const firstDay = new Date(year, month - 1, 1);
                    const weekday = firstDay.getDay();
                    const diffToMonday = weekday === 0 ? -6 : 1 - weekday;
                    const monday = new Date(firstDay);
                    monday.setDate(firstDay.getDate() + diffToMonday);
                    const target = new Date(date.getFullYear(), date.getMonth(), date.getDate());
                    const diffDays = Math.floor((target - monday) / (1000 * 60 * 60 * 24));
                    const idx = Math.floor(diffDays / 7);
                    return Math.max(0, idx);
                },
                availableWeeks() {
                    const refMonth = this.filters.mes || new Date().toISOString().slice(0, 7);
                    const [year, month] = refMonth.split('-').map(Number);
                    const total = this.weeksInMonthGrid(year, month);
                    return Array.from({ length: Math.max(total, 1) }, (_, i) => ({
                        value: i,
                        label: this.weekLabelWithRange(i),
                    }));
                },
                weeksInMonthGrid(year, month) {
                    const firstDay = new Date(year, month - 1, 1);
                    const lastDay = new Date(year, month, 0);

                    const start = new Date(firstDay);
                    const firstWeekday = start.getDay();
                    const diffToMonday = firstWeekday === 0 ? -6 : 1 - firstWeekday;
                    start.setDate(start.getDate() + diffToMonday);
                    start.setHours(0, 0, 0, 0);

                    const end = new Date(lastDay);
                    const lastWeekday = end.getDay();
                    const diffToFriday = lastWeekday === 0 ? -2 : 5 - lastWeekday;
                    end.setDate(end.getDate() + diffToFriday);
                    end.setHours(0, 0, 0, 0);

                    const diffDays = Math.floor((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24));
                    return Math.floor(diffDays / 7) + 1;
                },
                weekLabelWithRange(weekIndex) {
                    const refMonth = this.filters.mes || new Date().toISOString().slice(0, 7);
                    const [year, month] = refMonth.split('-').map(Number);
                    const firstDay = new Date(year, month - 1, 1);
                    const weekday = firstDay.getDay();
                    const diffToMonday = weekday === 0 ? -6 : 1 - weekday;
                    const monday = new Date(firstDay);
                    monday.setDate(firstDay.getDate() + diffToMonday + (weekIndex * 7));

                    const friday = new Date(monday);
                    friday.setDate(monday.getDate() + 4);

                    const startDay = String(monday.getDate()).padStart(2, '0');
                    const endDay = String(friday.getDate()).padStart(2, '0');
                    return `${weekIndex + 1}a semana (${startDay} à ${endDay})`;
                },
                onMonthChange() {
                    const options = this.availableWeeks();
                    if (this.filters.weekIndex > options.length - 1) {
                        this.filters.weekIndex = 0;
                    }
                    // Recarrega do backend para refletir o mes selecionado.
                    this.load();
                },
                buildBoardFromItems() {
                    // Reconstrucao local do quadro semanal a partir da lista filtrada.
                    this.boardMap = {};
                    const allowedDates = new Set(this.displayedWeekDays().map((d) => d.iso));
                    const currentSlotByAluno = {};
                    // Reposicao deve ser mensal: traz tudo que estiver pendente no mes selecionado.
                    this.reposicaoQueue = this.filteredItems()
                        .filter((item) => item.status === 'REPOSICAO_PENDENTE')
                        .map((item) => ({
                            agendaItemId: item.id,
                            alunoId: item.aluna?.id || null,
                            id: item.id,
                            name: item.aluna?.nome || 'Aluna',
                            slotLabel: (() => {
                                const aid = item.aluna?.id;
                                if (!aid || !allowedDates.has(item.data)) return null;
                                currentSlotByAluno[aid] = (currentSlotByAluno[aid] || 0) + 1;
                                const total = this.planoSemanalByAluno(aid);
                                return `${currentSlotByAluno[aid]}/${total}`;
                            })(),
                            professorAlunoNome: item.aluna?.professor_nome || null,
                            professoraNome: item.professora?.nome || null,
                            status: item.status || 'REPOSICAO_PENDENTE',
                            faltaData: this.faltaDataFromItem(item),
                            horaFim: item.hora_fim || null,
                        }));

                    this.filteredItems().forEach((item) => {
                        if (item.status === 'REPOSICAO_PENDENTE') {
                            return;
                        }
                        if (!allowedDates.has(item.data)) {
                            return;
                        }
                        const hora = String(item.hora_inicio || '').slice(0, 5);
                        const name = item.aluna?.nome;
                        if (!hora || !name) {
                            return;
                        }
                        const key = this.slotKey(item.data, hora);
                        if (!this.boardMap[key]) {
                            this.boardMap[key] = [];
                        }
                        const entry = {
                            agendaItemId: item.id,
                            alunoId: item.aluna?.id || null,
                            id: item.id,
                            name,
                            slotLabel: (() => {
                                const aid = item.aluna?.id;
                                if (!aid) return null;
                                currentSlotByAluno[aid] = (currentSlotByAluno[aid] || 0) + 1;
                                const total = this.planoSemanalByAluno(aid);
                                return `${currentSlotByAluno[aid]}/${total}`;
                            })(),
                            professorAlunoNome: item.aluna?.professor_nome || null,
                            professoraNome: item.professora?.nome || null,
                            status: item.status || 'AGENDADA',
                            faltaData: this.faltaDataFromItem(item),
                            horaFim: item.hora_fim || null,
                        };
                        if (!this.boardMap[key].some((slotEntry) => slotEntry.agendaItemId === entry.agendaItemId)) {
                            this.boardMap[key].push(entry);
                        }
                    });
                },
                slotKey(dayIso, hora) {
                    return `${dayIso}|${hora}`;
                },
                entriesForSlot(dayIso, hora) {
                    const key = this.slotKey(dayIso, hora);
                    return this.boardMap[key] || [];
                },
                allBoardEntryIds() {
                    return Object.values(this.boardMap).flat().map((entry) => entry.agendaItemId);
                },
                boardAndQueueAlunoIds() {
                    const ids = new Set();
                    Object.values(this.boardMap).flat().forEach((e) => {
                        if (e.alunoId) {
                            ids.add(e.alunoId);
                        }
                    });
                    this.reposicaoQueue.forEach((e) => {
                        if (e.alunoId) {
                            ids.add(e.alunoId);
                        }
                    });
                    return ids;
                },
                poolEntries() {
                    const weekDates = new Set(this.displayedWeekDays().map((d) => d.iso));
                    const scheduledCount = {};
                    this.items
                        .filter((item) => item.data && weekDates.has(item.data))
                        .forEach((item) => {
                            const aid = item.aluna?.id;
                            if (!aid) return;
                            scheduledCount[aid] = (scheduledCount[aid] || 0) + 1;
                        });

                    const cadastroPool = [];
                    (this.alunosCadastro || []).forEach((a) => {
                        const totalNaSemana = Number(a.plano_aulas_semana || 2) === 3 ? 3 : 2;
                        const jaAlocado = Number(scheduledCount[a.id] || 0);
                        const restantes = Math.max(0, totalNaSemana - jaAlocado);
                        for (let i = 0; i < restantes; i++) {
                            cadastroPool.push({
                                poolKey: `cad-${a.id}-${i + 1}`,
                                agendaItemId: null,
                                alunoId: a.id,
                                id: null,
                                name: a.nome,
                                professorAlunoNome: a.professor_nome || null,
                                professoraNome: this.filters.professora || this.currentProfessorName || 'Karine',
                                status: 'AGENDADA',
                                horaFim: null,
                                slotLabel: `${jaAlocado + i + 1}/${totalNaSemana}`,
                            });
                        }
                    });
                    return cadastroPool;
                },
                planoSemanalByAluno(alunoId) {
                    const aluno = (this.alunosCadastro || []).find((a) => Number(a.id) === Number(alunoId));
                    return Number(aluno?.plano_aulas_semana || 2) === 3 ? 3 : 2;
                },
                filteredPoolEntries() {
                    const term = String(this.poolSearch || '').toLowerCase();
                    if (!term) {
                        return this.poolEntries();
                    }
                    return this.poolEntries().filter((entry) => String(entry.name || '').toLowerCase().includes(term));
                },
                diasUteisDoMes() {
                    const refMonth = this.filters.mes || new Date().toISOString().slice(0, 7);
                    const [year, month] = refMonth.split('-').map(Number);
                    const lastDay = new Date(year, month, 0).getDate();
                    const byWeekday = { 0: [], 1: [], 2: [], 3: [], 4: [] }; // seg-sex
                    for (let day = 1; day <= lastDay; day++) {
                        const date = new Date(year, month - 1, day);
                        const weekday = date.getDay();
                        if (weekday >= 1 && weekday <= 5) {
                            byWeekday[weekday - 1].push(date.toISOString().slice(0, 10));
                        }
                    }
                    return byWeekday;
                },
                existingAgendaKeys() {
                    const keys = new Set();
                    this.items.forEach((item) => {
                        const aid = item.aluna?.id;
                        const data = item.data;
                        const hora = String(item.hora_inicio || '').slice(0, 5);
                        if (aid && data && hora) {
                            keys.add(`${aid}|${data}|${hora}`);
                        }
                    });
                    return keys;
                },
                async replicarSemanaNoMes() {
                    if (!this.filters.mes) {
                        this.notifyError('Selecione o mes antes de replicar.');
                        return;
                    }

                    this.error = '';
                    this.savingReplicar = true;
                    try {
                        const weekDays = this.displayedWeekDays();
                        const templates = [];
                        weekDays.forEach((day, weekdayIndex) => {
                            this.horarios.forEach((hora) => {
                                this.entriesForSlot(day.iso, hora).forEach((entry) => {
                                    if (!entry.alunoId) {
                                        return;
                                    }
                                    templates.push({
                                        weekdayIndex,
                                        sourceDate: day.iso,
                                        alunoId: entry.alunoId,
                                        professoraNome: entry.professoraNome || this.filters.professora || 'Karine',
                                        horaInicio: hora,
                                        horaFim: entry.horaFim || this.suggestHoraFim(hora),
                                        status: entry.status || 'AGENDADA',
                                    });
                                });
                            });
                        });

                        if (templates.length === 0) {
                            this.notifyError('Nao ha aulas na semana atual para replicar.');
                            return;
                        }

                        const byWeekday = this.diasUteisDoMes();
                        const currentWeekDates = new Set(weekDays.map((d) => d.iso));

                        // Sempre limpa as outras semanas do mesmo mes (mantem apenas semana atual).
                        const toClear = this.items.filter((item) => {
                            if (!item.data || item.data.slice(0, 7) !== this.filters.mes) {
                                return false;
                            }
                            if (currentWeekDates.has(item.data)) {
                                return false;
                            }
                            return true;
                        });

                        await Promise.all(
                            toClear.map((item) => fetch(`{{ url('/api/v1/agenda') }}/${item.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                            }))
                        );

                        const requests = [];
                        const planned = new Set();

                        templates.forEach((tpl) => {
                            (byWeekday[tpl.weekdayIndex] || []).forEach((targetDate) => {
                                if (targetDate === tpl.sourceDate) {
                                    return;
                                }
                                // Bloqueia replicacao para datas/horarios ja passados.
                                if (this.isPastSlot(targetDate, tpl.horaInicio)) {
                                    return;
                                }
                                const key = `${tpl.alunoId}|${targetDate}|${tpl.horaInicio}`;
                                if (planned.has(key)) {
                                    return;
                                }
                                planned.add(key);
                                requests.push({
                                    aluno_id: tpl.alunoId,
                                    data: targetDate,
                                    hora_inicio: tpl.horaInicio,
                                    hora_fim: tpl.horaFim,
                                    professora_nome: tpl.professoraNome,
                                    status: tpl.status,
                                });
                            });
                        });

                        await Promise.all(
                            requests.map((payload) => fetch('{{ route('api.v1.agenda.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(payload),
                            }))
                        );

                        await this.load();
                    } catch (e) {
                        this.notifyError(e.message || 'Erro ao replicar semana no mes.');
                    } finally {
                        this.savingReplicar = false;
                    }
                },
                startDragFromPool(entry) {
                    this.draggedEntry = { ...entry };
                    this.dragOrigin = { type: 'pool' };
                },
                startDragFromSlot(entry, dayIso, hora) {
                    if (this.isPastSlot(dayIso, hora)) {
                        this.notifyError('Nao e permitido mover aulas de semanas passadas.');
                        return;
                    }
                    this.draggedEntry = { ...entry };
                    this.dragOrigin = { type: 'slot', dayIso, hora };
                },
                startDragFromQueue(entry) {
                    this.draggedEntry = { ...entry };
                    this.dragOrigin = { type: 'queue' };
                },
                removeFromOrigin() {
                    if (!this.draggedEntry || !this.dragOrigin) {
                        return;
                    }
                    if (this.dragOrigin.type === 'slot') {
                        const originKey = this.slotKey(this.dragOrigin.dayIso, this.dragOrigin.hora);
                        const aid = this.draggedEntry.agendaItemId ?? this.draggedEntry.id;
                        this.boardMap[originKey] = (this.boardMap[originKey] || []).filter((entry) => (entry.agendaItemId ?? entry.id) !== aid);
                    }
                    if (this.dragOrigin.type === 'queue') {
                        const aid = this.draggedEntry.agendaItemId ?? this.draggedEntry.id;
                        this.reposicaoQueue = this.reposicaoQueue.filter((entry) => (entry.agendaItemId ?? entry.id) !== aid);
                    }
                },
                dropOnSlot(dayIso, hora) {
                    if (!this.draggedEntry) {
                        return;
                    }
                    if (this.isPastSlot(dayIso, hora)) {
                        this.notifyError('Nao e permitido repor em semanas passadas.');
                        this.draggedEntry = null;
                        this.dragOrigin = null;
                        return;
                    }
                    // Guarda referencia antes de limpar origem para persistir no backend.
                    const entryToSave = { ...this.draggedEntry };
                    this.removeFromOrigin();
                    const key = this.slotKey(dayIso, hora);
                    if (!this.boardMap[key]) {
                        this.boardMap[key] = [];
                    }
                    const toSave = { ...entryToSave };
                    if (this.dragOrigin?.type === 'queue') {
                        toSave.status = 'REPOSICAO';
                    }
                    const slotId = toSave.agendaItemId ?? toSave.id;
                    if (!this.boardMap[key].some((entry) => (entry.agendaItemId ?? entry.id) === slotId)) {
                        this.boardMap[key].push(toSave);
                    }
                    this.persistAgenda(toSave, {
                        data: dayIso,
                        hora_inicio: hora,
                        hora_fim: toSave.horaFim || this.suggestHoraFim(hora),
                        status: toSave.status || 'AGENDADA',
                        observacoes: toSave.faltaData ? `FALTA_EM:${toSave.faltaData}` : null,
                    });
                    this.draggedEntry = null;
                    this.dragOrigin = null;
                },
                dropOnPool() {
                    if (!this.draggedEntry) {
                        return;
                    }
                    const entryToSave = { ...this.draggedEntry };
                    this.removeFromOrigin();
                    this.persistAgenda(entryToSave, {
                        data: null,
                        hora_inicio: null,
                        hora_fim: null,
                        status: 'AGENDADA',
                    });
                    this.draggedEntry = null;
                    this.dragOrigin = null;
                },
                dropOnReposicaoQueue() {
                    if (!this.draggedEntry) {
                        return;
                    }
                    const entryToSave = { ...this.draggedEntry };
                    this.removeFromOrigin();
                    const toQueue = { ...entryToSave, status: 'REPOSICAO_PENDENTE' };
                    toQueue.faltaData = toQueue.faltaData || (this.dragOrigin?.type === 'slot' ? this.dragOrigin.dayIso : null);
                    const qid = toQueue.agendaItemId ?? toQueue.id;
                    if (!this.reposicaoQueue.some((entry) => (entry.agendaItemId ?? entry.id) === qid)) {
                        this.reposicaoQueue.push(toQueue);
                    }
                    this.persistAgenda(toQueue, {
                        data: toQueue.faltaData || null,
                        hora_inicio: this.dragOrigin?.type === 'slot' ? this.dragOrigin.hora : null,
                        hora_fim: toQueue.horaFim || null,
                        status: 'REPOSICAO_PENDENTE',
                        observacoes: toQueue.faltaData ? `FALTA_EM:${toQueue.faltaData}` : null,
                    });
                    this.draggedEntry = null;
                    this.dragOrigin = null;
                },
                markAsFaltou(dayIso, hora, entry) {
                    if (this.isPastSlot(dayIso, hora)) {
                        this.notifyError('Nao e permitido alterar aulas de semanas passadas.');
                        return;
                    }
                    const entryId = entry.agendaItemId ?? entry.id;
                    const key = this.slotKey(dayIso, hora);
                    const found = (this.boardMap[key] || []).find((slotEntry) => (slotEntry.agendaItemId ?? slotEntry.id) === entryId);
                    if (!found) {
                        return;
                    }
                    this.boardMap[key] = (this.boardMap[key] || []).filter((slotEntry) => (slotEntry.agendaItemId ?? slotEntry.id) !== entryId);
                    const toQueue = { ...found, status: 'REPOSICAO_PENDENTE', faltaData: dayIso };
                    if (!this.reposicaoQueue.some((queueEntry) => (queueEntry.agendaItemId ?? queueEntry.id) === entryId)) {
                        this.reposicaoQueue.push(toQueue);
                    }
                    this.persistAgenda(found, {
                        data: dayIso,
                        hora_inicio: hora,
                        hora_fim: found.horaFim || this.suggestHoraFim(hora),
                        status: 'REPOSICAO_PENDENTE',
                        observacoes: `FALTA_EM:${dayIso}`,
                    });
                },
                async removeEntryFromSlot(dayIso, hora, entry) {
                    if (this.isPastSlot(dayIso, hora)) {
                        this.notifyError('Nao e permitido alterar aulas de semanas passadas.');
                        return;
                    }
                    const entryId = entry.agendaItemId ?? entry.id;
                    const key = this.slotKey(dayIso, hora);
                    this.boardMap[key] = (this.boardMap[key] || []).filter((slotEntry) => (slotEntry.agendaItemId ?? slotEntry.id) !== entryId);

                    if (entry.agendaItemId || entry.id) {
                        const id = entry.agendaItemId ?? entry.id;
                        await fetch(`{{ url('/api/v1/agenda') }}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        });

                        await this.load();
                        return;
                    }

                    this.persistAgenda(entry, {
                        data: null,
                        hora_inicio: null,
                        hora_fim: null,
                        status: 'AGENDADA',
                    });
                },
                suggestHoraFim(horaInicio) {
                    const [hour, minute] = horaInicio.split(':').map(Number);
                    const date = new Date(2000, 0, 1, hour, minute);
                    date.setMinutes(date.getMinutes() + 50);
                    const hh = String(date.getHours()).padStart(2, '0');
                    const mm = String(date.getMinutes()).padStart(2, '0');
                    return `${hh}:${mm}`;
                },
                formatDate(isoDate) {
                    if (!isoDate || typeof isoDate !== 'string' || isoDate.length < 10) {
                        return '';
                    }
                    return `${isoDate.slice(8, 10)}/${isoDate.slice(5, 7)}`;
                },
                faltaDataFromItem(item) {
                    const obs = String(item.observacoes || '');
                    const match = obs.match(/FALTA_EM:(\d{4}-\d{2}-\d{2})/);
                    if (match) {
                        return match[1];
                    }
                    if (item.status === 'REPOSICAO_PENDENTE' && item.data) {
                        return item.data;
                    }
                    return null;
                },
                async persistAgenda(entry, payload) {
                    const agendaId = entry.agendaItemId ?? entry.id;
                    this.error = '';
                    try {
                        if (agendaId) {
                            const response = await fetch(`{{ url('/api/v1/agenda') }}/${agendaId}`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(payload),
                            });
                            if (!response.ok) {
                                throw new Error('Falha ao salvar alteracao da agenda.');
                            }
                            await this.load();
                            return;
                        }
                        if (entry.alunoId && payload.data && payload.hora_inicio) {
                            const response = await fetch('{{ route('api.v1.agenda.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    aluno_id: entry.alunoId,
                                    data: payload.data,
                                    hora_inicio: payload.hora_inicio,
                                    hora_fim: payload.hora_fim || this.suggestHoraFim(payload.hora_inicio),
                                    professora_nome: this.filters.professora || 'Karine',
                                    status: payload.status || 'AGENDADA',
                                }),
                            });
                            if (!response.ok) {
                                throw new Error('Falha ao criar aula na agenda.');
                            }
                            await this.load();
                            return;
                        }
                        if (!agendaId && payload.data === null) {
                            return;
                        }
                        throw new Error('Nao foi possivel salvar: registro sem id de agenda e sem aluno.');
                    } catch (e) {
                        this.notifyError(e.message || 'Erro ao salvar agenda.');
                    }
                },
                notifyError(message) {
                    if (window.alertify) {
                        alertify.error(message);
                    } else {
                        this.error = message;
                    }
                },
            };
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
@endsection
