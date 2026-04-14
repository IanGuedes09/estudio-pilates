<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Studio Vitali Pilates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: '#4f46e5',
                    },
                },
            },
        };
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 text-slate-100">
    <div class="mx-auto flex min-h-screen max-w-6xl items-center px-4 py-8">
        <div class="grid w-full gap-8 md:grid-cols-2">
            <div class="hidden rounded-3xl border border-indigo-400/25 bg-slate-900/50 p-8 shadow-2xl backdrop-blur md:block">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-300">Studio Vitali</p>
                <h1 class="mt-3 text-3xl font-bold">Sistema de Gestão</h1>
                <p class="mt-3 text-sm text-slate-300">
                    Agenda, pagamentos e gestão de alunos/professores em um painel único para o Studio Vitali Pilates.
                </p>
                <div class="mt-8 space-y-3 text-sm text-slate-200">
                    <p class="rounded-xl border border-indigo-400/20 bg-indigo-500/10 px-3 py-2">- Agenda semanal com arrastar e soltar</p>
                    <p class="rounded-xl border border-indigo-400/20 bg-indigo-500/10 px-3 py-2">- Controle mensal de pagamentos e comprovantes</p>
                    <p class="rounded-xl border border-indigo-400/20 bg-indigo-500/10 px-3 py-2">- Perfis de acesso por função</p>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-700 bg-slate-900/70 p-6 shadow-2xl backdrop-blur md:p-8">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-300">Acesso seguro</p>
                <h2 class="mt-2 text-2xl font-bold">Entrar no Studio Vitali</h2>
                <p class="mt-1 text-sm text-slate-400">Use seu e-mail e senha cadastrados.</p>

                @if (session('status'))
                    <div class="mt-4 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-400">E-mail</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-400">Senha</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30"
                        >
                    </div>

                    <label for="remember_me" class="flex items-center gap-2 text-sm text-slate-300">
                        <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500">
                        Manter conectado
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                    >
                        Entrar
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
