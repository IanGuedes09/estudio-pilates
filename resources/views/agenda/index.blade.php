@extends('layouts.vitali-dashboard')

@section('title', 'Agenda - Studio Vitali')

@section('content')
    <div x-data="agendaPage()" x-init="load()">
        <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Agenda</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300">Quadro manual para organizar alunas por horario, no estilo da folha.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Voltar ao Dashboard
            </a>
        </div>

        <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Mes</label>
                <input type="month" x-model="filters.mes" @change="onMonthChange()" class="w-full rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Professora</label>
                <select x-model="filters.professora" @change="buildBoardFromItems()" class="w-full rounded-xl border border-slate-300 bg-white/80 px-3 py-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200">
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
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Acoes</label>
                <button type="button" @click="resetBoard()" class="w-full rounded-xl border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300 dark:hover:bg-amber-900/30">
                    Recarregar quadro
                </button>
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
                <div class="mt-3 min-h-40 rounded-xl border border-dashed border-slate-300 p-2 dark:border-slate-700"
                     @dragover.prevent
                     @drop.prevent="dropOnPool()">
                    <template x-if="poolEntries().length === 0">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Sem nomes na fila.</p>
                    </template>
                    <template x-for="entry in poolEntries()" :key="entry.poolKey">
                        <div class="mb-2 cursor-move rounded-lg bg-indigo-100 px-2 py-1 text-sm font-medium text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300"
                             draggable="true"
                             @dragstart="startDragFromPool(entry)"
                             x-text="entry.name"></div>
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
                             x-text="entry.name"></div>
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
                                        <div class="mb-1 cursor-move rounded-lg bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300"
                                             draggable="true"
                                             @dragstart="startDragFromSlot(entry, day.iso, hora)">
                                            <div class="flex items-center justify-between gap-1">
                                                <span x-text="entry.name"></span>
                                                <span class="rounded-full bg-white/70 px-1.5 py-0.5 text-[10px] font-bold text-slate-700 dark:bg-slate-900/70 dark:text-slate-200" x-text="entry.status"></span>
                                            </div>
                                            <div class="mt-1 flex gap-1">
                                                <button type="button" class="rounded bg-amber-200 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-600/20 dark:text-amber-300" @click.stop="markAsFaltou(day.iso, hora, entry)">Faltou</button>
                                                <button type="button" class="rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-700 dark:bg-slate-700/40 dark:text-slate-200" @click.stop="removeEntryFromSlot(day.iso, hora, entry)">Remover</button>
                                            </div>
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
            return {
                loading: false,
                error: '',
                items: [],
                filters: {
                    mes: '',
                    professora: '',
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
                get professoras() {
                    return [...new Set(this.items.map((item) => item.professora?.nome).filter(Boolean))];
                },
                get horarios() {
                    const base = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00'];
                    const dynamic = this.filteredItems().map((item) => String(item.hora_inicio || '').slice(0, 5)).filter(Boolean);
                    return [...new Set([...base, ...dynamic])].sort();
                },
                async load() {
                    this.loading = true;
                    this.error = '';
                    try {
                        // Busca dados ja filtrados no backend para manter desempenho.
                        const params = new URLSearchParams();
                        if (this.filters.mes) {
                            params.set('mes', this.filters.mes);
                        }
                        if (this.filters.professora) {
                            params.set('professora', this.filters.professora);
                        }
                        const response = await fetch(`{{ route('api.v1.agenda.index') }}?${params.toString()}`);
                        if (!response.ok) {
                            throw new Error('Nao foi possivel carregar a agenda.');
                        }
                        this.items = await response.json();
                        if (!this.filters.mes) {
                            this.filters.mes = this.items[0]?.data?.slice(0, 7) || new Date().toISOString().slice(0, 7);
                        }
                        await this.loadAlunos();
                        this.buildBoardFromItems();
                    } catch (e) {
                        this.error = e.message || 'Erro inesperado ao carregar agenda.';
                    } finally {
                        this.loading = false;
                    }
                },
                filteredItems() {
                    return this.items.filter((item) => {
                        const byMonth = !this.filters.mes || !item.data || item.data.slice(0, 7) === this.filters.mes;
                        const byProfessora = !this.filters.professora || item.professora?.nome === this.filters.professora;
                        return byMonth && byProfessora;
                    });
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
                availableWeeks() {
                    const refMonth = this.filters.mes || new Date().toISOString().slice(0, 7);
                    const [year, month] = refMonth.split('-').map(Number);
                    const lastDay = new Date(year, month, 0).getDate();
                    const total = Math.ceil(lastDay / 7);
                    return Array.from({ length: Math.max(total, 1) }, (_, i) => ({
                        value: i,
                        label: `${i + 1}a semana`,
                    }));
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
                    this.reposicaoQueue = [];
                    const allowedDates = new Set(this.displayedWeekDays().map((d) => d.iso));
                    this.filteredItems().forEach((item) => {
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
                            status: item.status || 'AGENDADA',
                            horaFim: item.hora_fim || null,
                        };
                        if (!this.boardMap[key].some((slotEntry) => slotEntry.agendaItemId === entry.agendaItemId)) {
                            this.boardMap[key].push(entry);
                        }
                    });
                },
                resetBoard() {
                    this.buildBoardFromItems();
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
                    const usedBoardIds = new Set(this.allBoardEntryIds());
                    const queueAgendaIds = new Set(this.reposicaoQueue.map((e) => e.agendaItemId || e.id));
                    const agendaPool = this.filteredItems()
                        .filter((item) => !usedBoardIds.has(item.id) && !queueAgendaIds.has(item.id))
                        .map((item) => ({
                            poolKey: `ag-${item.id}`,
                            agendaItemId: item.id,
                            alunoId: item.aluna?.id || null,
                            id: item.id,
                            name: item.aluna?.nome || 'Aluna',
                            status: item.status || 'AGENDADA',
                            horaFim: item.hora_fim || null,
                        }))
                        .filter((e) => Boolean(e.name));
                    const alunoIdsComLinhaNaAgendaDoMes = new Set(this.filteredItems().map((i) => i.aluna?.id).filter(Boolean));
                    const cadastroPool = (this.alunosCadastro || [])
                        .filter((a) => !this.boardAndQueueAlunoIds().has(a.id) && !alunoIdsComLinhaNaAgendaDoMes.has(a.id))
                        .map((a) => ({
                            poolKey: `cad-${a.id}`,
                            agendaItemId: null,
                            alunoId: a.id,
                            id: null,
                            name: a.nome,
                            status: 'AGENDADA',
                            horaFim: null,
                        }));
                    return [...cadastroPool, ...agendaPool];
                },
                startDragFromPool(entry) {
                    this.draggedEntry = { ...entry };
                    this.dragOrigin = { type: 'pool' };
                },
                startDragFromSlot(entry, dayIso, hora) {
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
                    const qid = toQueue.agendaItemId ?? toQueue.id;
                    if (!this.reposicaoQueue.some((entry) => (entry.agendaItemId ?? entry.id) === qid)) {
                        this.reposicaoQueue.push(toQueue);
                    }
                    this.persistAgenda(toQueue, {
                        data: null,
                        hora_inicio: null,
                        hora_fim: null,
                        status: 'REPOSICAO_PENDENTE',
                    });
                    this.draggedEntry = null;
                    this.dragOrigin = null;
                },
                markAsFaltou(dayIso, hora, entry) {
                    const entryId = entry.agendaItemId ?? entry.id;
                    const key = this.slotKey(dayIso, hora);
                    const found = (this.boardMap[key] || []).find((slotEntry) => (slotEntry.agendaItemId ?? slotEntry.id) === entryId);
                    if (!found) {
                        return;
                    }
                    this.boardMap[key] = (this.boardMap[key] || []).filter((slotEntry) => (slotEntry.agendaItemId ?? slotEntry.id) !== entryId);
                    const toQueue = { ...found, status: 'REPOSICAO_PENDENTE' };
                    if (!this.reposicaoQueue.some((queueEntry) => (queueEntry.agendaItemId ?? queueEntry.id) === entryId)) {
                        this.reposicaoQueue.push(toQueue);
                    }
                    this.persistAgenda(found, {
                        data: null,
                        hora_inicio: null,
                        hora_fim: null,
                        status: 'REPOSICAO_PENDENTE',
                    });
                },
                removeEntryFromSlot(dayIso, hora, entry) {
                    const entryId = entry.agendaItemId ?? entry.id;
                    const key = this.slotKey(dayIso, hora);
                    this.boardMap[key] = (this.boardMap[key] || []).filter((slotEntry) => (slotEntry.agendaItemId ?? slotEntry.id) !== entryId);
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
                        this.error = e.message || 'Erro ao salvar agenda.';
                    }
                },
            };
        }
    </script>
@endsection
