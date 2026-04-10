<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Studio Vitali Dashboard')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#1e293b',
                        dark: '#0f172a',
                    },
                },
                fontFamily: {
                    sans: ['Segoe UI', 'Tahoma', 'Geneva', 'Verdana', 'sans-serif'],
                },
            },
            darkMode: 'class',
        };
    </script>

    <style>
        [x-cloak] { display: none !important; }

        .fade-in-up {
            animation: fadeInUp 0.5s ease-out both;
        }

        .fade-in-up-delay {
            animation: fadeInUp 0.7s ease-out both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body
    x-data="{ sidebarOpen: false, darkMode: false }"
    x-init="
        darkMode = localStorage.getItem('theme') === 'dark';
        document.documentElement.classList.toggle('dark', darkMode);
    "
    :class="darkMode
        ? 'bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-950 text-slate-100'
        : 'bg-gradient-to-br from-slate-100 via-indigo-100 to-cyan-100 text-slate-800'"
    class="min-h-screen transition-colors duration-300"
>
    <header
        :class="darkMode
            ? 'border-indigo-400/20 bg-slate-950/90'
            : 'border-slate-200 bg-white/85'"
        class="fixed left-0 right-0 top-0 z-50 border-b px-6 py-4 shadow-lg backdrop-blur transition-colors duration-300"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button @click="sidebarOpen = !sidebarOpen" :class="darkMode ? 'text-white' : 'text-slate-700'" class="rounded-md p-1 md:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 :class="darkMode ? 'text-white' : 'text-slate-900'" class="text-xl font-bold tracking-wide transition-colors duration-300">Studio Vitali Pilates</h1>
            </div>

            <div class="flex items-center space-x-3">
                <button
                    @click="
                        darkMode = !darkMode;
                        localStorage.setItem('theme', darkMode ? 'dark' : 'light');
                        document.documentElement.classList.toggle('dark', darkMode);
                    "
                    :class="darkMode
                        ? 'border-slate-700 text-slate-200 hover:border-yellow-300 hover:text-yellow-300'
                        : 'border-slate-300 text-slate-600 hover:border-indigo-400 hover:text-indigo-600'"
                    class="rounded-lg border p-2 transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 2a.75.75 0 01.75.75v1a.75.75 0 01-1.5 0v-1A.75.75 0 0110 2zM4.22 4.22a.75.75 0 011.06 0l.7.7a.75.75 0 11-1.06 1.06l-.7-.7a.75.75 0 010-1.06zM2 10a.75.75 0 01.75-.75h1a.75.75 0 010 1.5h-1A.75.75 0 012 10zm8 6a.75.75 0 01.75.75v1a.75.75 0 01-1.5 0v-1A.75.75 0 0110 16zm7.03-6.75a.75.75 0 100 1.5h1a.75.75 0 100-1.5h-1zm-2.81 5.53a.75.75 0 010 1.06l-.7.7a.75.75 0 11-1.06-1.06l.7-.7a.75.75 0 011.06 0zM15.78 4.22a.75.75 0 00-1.06 0l-.7.7a.75.75 0 001.06 1.06l.7-.7a.75.75 0 000-1.06zM10 5a5 5 0 100 10A5 5 0 0010 5z" />
                    </svg>
                </button>

                <span :class="darkMode ? 'text-slate-300' : 'text-slate-600'" class="hidden text-sm sm:inline">
                    {{ auth()->user()->name }} ({{ auth()->user()->perfil ?? 'Usuário' }})
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="flex">
        <div
            x-cloak
            x-show="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-black/50 md:hidden"
        ></div>

        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="fixed left-0 top-0 z-40 min-h-screen w-64 border-r p-6 pt-24 shadow-xl transition-transform duration-300"
            :style="darkMode
                ? 'border-color: rgba(129,140,248,0.2); background: rgba(15,23,42,0.95); color: #f8fafc;'
                : 'border-color: #e2e8f0; background: rgba(255,255,255,0.96); color: #0f172a;'"
        >
            <nav>
                <ul class="space-y-3">
                    <li>
                        <a
                            href="{{ route('dashboard') }}"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="{
                                'bg-indigo-600 text-white': {{ request()->routeIs('dashboard') ? 'true' : 'false' }},
                                'hover:bg-indigo-600/60 text-slate-200': darkMode && !{{ request()->routeIs('dashboard') ? 'true' : 'false' }},
                                'hover:bg-indigo-100 text-slate-700': !darkMode && !{{ request()->routeIs('dashboard') ? 'true' : 'false' }}
                            }"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l9-7 9 7v10a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1V10z" />
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('agenda.index') }}"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="{
                                'bg-indigo-600 text-white': {{ request()->routeIs('agenda.*') ? 'true' : 'false' }},
                                'hover:bg-indigo-600/60 text-slate-200': darkMode && !{{ request()->routeIs('agenda.*') ? 'true' : 'false' }},
                                'hover:bg-indigo-100 text-slate-700': !darkMode && !{{ request()->routeIs('agenda.*') ? 'true' : 'false' }}
                            }"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Agenda
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('pagamentos.index') }}"
                            class="flex items-center gap-3 rounded-lg px-4 py-2 transition"
                            :class="{
                                'bg-indigo-600 text-white': {{ request()->routeIs('pagamentos.*') ? 'true' : 'false' }},
                                'hover:bg-indigo-600/60 text-slate-200': darkMode && !{{ request()->routeIs('pagamentos.*') ? 'true' : 'false' }},
                                'hover:bg-indigo-100 text-slate-700': !darkMode && !{{ request()->routeIs('pagamentos.*') ? 'true' : 'false' }}
                            }"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 .895-4 2s1.79 2 4 2 4 .895 4 2-1.79 2-4 2m0-10v10m0-10c1.11 0 2.08.228 2.8.58M12 8c-1.11 0-2.08.228-2.8.58M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Pagamentos
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="mt-20 min-h-screen w-full p-6 md:ml-64 md:p-8">
            <div
                :class="darkMode
                    ? 'border-indigo-400/20 bg-slate-900/70'
                    : 'border-white/70 bg-white/80'"
                class="fade-in-up rounded-3xl border p-6 shadow-2xl backdrop-blur transition-colors duration-300 md:p-8"
            >
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/alpinejs" defer></script>
</body>

</html>
