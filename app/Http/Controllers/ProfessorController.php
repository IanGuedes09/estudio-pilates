<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfessorController extends Controller
{
    public function index(): View
    {
        $professores = Professor::query()
            ->with('user')
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->get();

        return view('professores.index', compact('professores'));
    }

    public function create(): View
    {
        return view('professores.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $cpfDigits = preg_replace('/\D/', '', (string) $request->input('cpf', ''));

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:professores,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'telefone' => ['nullable', 'string', 'max:40'],
            'comissao_percentual' => ['required', 'numeric', 'min:0', 'max:100'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        if (strlen($cpfDigits) !== 11) {
            throw ValidationException::withMessages([
                'cpf' => 'Informe um CPF válido com 11 dígitos.',
            ]);
        }

        if (Professor::query()->where('cpf', $cpfDigits)->exists()) {
            throw ValidationException::withMessages([
                'cpf' => 'Este CPF já está cadastrado.',
            ]);
        }

        $data['cpf'] = $cpfDigits;
        $data['ativo'] = $request->boolean('ativo', true);
        $plainPassword = $data['password'];
        unset($data['password'], $data['password_confirmation']);

        DB::transaction(function () use ($data, $plainPassword): void {
            $user = User::query()->create([
                'name' => $data['nome'],
                'email' => $data['email'],
                'password' => $plainPassword,
                'perfil' => 'Professor',
                'email_verified_at' => now(),
            ]);

            Professor::query()->create([
                ...$data,
                'user_id' => $user->id,
            ]);
        });

        return redirect()
            ->route('professores.index')
            ->with('success', 'Professor cadastrado. Ele pode entrar com o e-mail e a senha informados.');
    }

    public function edit(Professor $professor): View
    {
        return view('professores.edit', compact('professor'));
    }

    public function update(Request $request, Professor $professor): RedirectResponse
    {
        $cpfDigits = preg_replace('/\D/', '', (string) $request->input('cpf', ''));

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:professores,email,'.$professor->id, 'unique:users,email,'.($professor->user_id ?? 'NULL')],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'telefone' => ['nullable', 'string', 'max:40'],
            'comissao_percentual' => ['required', 'numeric', 'min:0', 'max:100'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        if (strlen($cpfDigits) !== 11) {
            throw ValidationException::withMessages([
                'cpf' => 'Informe um CPF válido com 11 dígitos.',
            ]);
        }

        $cpfDuplicado = Professor::query()
            ->where('cpf', $cpfDigits)
            ->whereKeyNot($professor->id)
            ->exists();
        if ($cpfDuplicado) {
            throw ValidationException::withMessages([
                'cpf' => 'Este CPF já está cadastrado.',
            ]);
        }

        $data['cpf'] = $cpfDigits;
        $data['ativo'] = $request->boolean('ativo', true);

        DB::transaction(function () use ($professor, $data): void {
            $professor->fill([
                'nome' => $data['nome'],
                'cpf' => $data['cpf'],
                'email' => $data['email'],
                'telefone' => $data['telefone'] ?? null,
                'comissao_percentual' => $data['comissao_percentual'],
                'ativo' => $data['ativo'],
            ]);
            $professor->save();

            if ($professor->user) {
                $userData = [
                    'name' => $data['nome'],
                    'email' => $data['email'],
                ];
                if (!empty($data['password'])) {
                    $userData['password'] = $data['password'];
                }
                $professor->user->update($userData);
            }
        });

        return redirect()
            ->route('professores.index')
            ->with('success', 'Professor atualizado com sucesso.');
    }

    public function destroy(Professor $professor): RedirectResponse
    {
        if ($professor->user && $professor->user->perfil === 'Administrador') {
            return redirect()
                ->route('professores.index')
                ->with('error', 'Não é permitido excluir um professor com perfil Administrador.');
        }

        DB::transaction(function () use ($professor): void {
            $user = $professor->user;
            $professor->delete();
            if ($user) {
                $user->delete();
            }
        });

        return redirect()
            ->route('professores.index')
            ->with('success', 'Professor excluído com sucesso.');
    }
}
