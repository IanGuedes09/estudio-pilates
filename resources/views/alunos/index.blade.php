<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Alunos') }}
            </h2>
            <a href="{{ route('alunos.create') }}" class="rounded-md bg-gray-800 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                {{ __('Novo aluno') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($alunos->isEmpty())
                        <p class="text-sm text-gray-600">{{ __('Nenhum aluno cadastrado ainda.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="border-b text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-3 py-2">{{ __('Nome') }}</th>
                                        <th class="px-3 py-2">{{ __('E-mail') }}</th>
                                        <th class="px-3 py-2">{{ __('Telefone') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($alunos as $aluno)
                                        <tr class="border-b border-gray-100">
                                            <td class="px-3 py-2 font-medium">{{ $aluno->nome }}</td>
                                            <td class="px-3 py-2">{{ $aluno->email ?? '—' }}</td>
                                            <td class="px-3 py-2">{{ $aluno->telefone }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
