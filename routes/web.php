<?php

use App\Http\Controllers\Api\AgendaApiController;
use App\Http\Controllers\Api\AlunoApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\PagamentoApiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\AlunoController;
use App\Http\Controllers\ProfessorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/agenda', [AgendaController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('agenda.index');

Route::get('/pagamentos', [PagamentoController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('pagamentos.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

//Rotas Ian 

// Agrupamos dentro do 'auth' para que só quem estiver logado consiga acessar
Route::middleware('auth')->group(function () {
    Route::get('/alunos', [AlunoController::class, 'index'])->name('alunos.index');
    Route::get('/alunos/create', [AlunoController::class, 'create'])->name('alunos.create');
    Route::post('/alunos', [AlunoController::class, 'store'])->name('alunos.store');
    Route::get('/alunos/{aluno}/edit', [AlunoController::class, 'edit'])->name('alunos.edit');
    Route::put('/alunos/{aluno}', [AlunoController::class, 'update'])->name('alunos.update');
    Route::delete('/alunos/{aluno}', [AlunoController::class, 'destroy'])->name('alunos.destroy');
});

Route::middleware(['auth', 'perfil:Administrador'])->group(function () {
    Route::get('/professores', [ProfessorController::class, 'index'])->name('professores.index');
    Route::get('/professores/create', [ProfessorController::class, 'create'])->name('professores.create');
    Route::post('/professores', [ProfessorController::class, 'store'])->name('professores.store');
    Route::get('/professores/{professor}/edit', [ProfessorController::class, 'edit'])->name('professores.edit');
    Route::put('/professores/{professor}', [ProfessorController::class, 'update'])->name('professores.update');
    Route::delete('/professores/{professor}', [ProfessorController::class, 'destroy'])->name('professores.destroy');
});

// API protegida (dados reais do banco).
Route::middleware('auth')->prefix('api/v1')->name('api.v1.')->group(function () {
    Route::get('/dashboard-resumo', [DashboardApiController::class, 'resumo'])->name('dashboard-resumo');

    Route::get('/alunos', [AlunoApiController::class, 'index'])->name('alunos.catalogo');

    Route::get('/agenda', [AgendaApiController::class, 'index'])->name('agenda.index');
    Route::post('/agenda', [AgendaApiController::class, 'store'])->name('agenda.store');
    Route::patch('/agenda/{agendaItem}', [AgendaApiController::class, 'update'])->name('agenda.update');
    Route::delete('/agenda/{agendaItem}', [AgendaApiController::class, 'destroy'])->name('agenda.destroy');

    Route::get('/pagamentos', [PagamentoApiController::class, 'index'])->name('pagamentos.index');
    Route::patch('/pagamentos/{pagamento}', [PagamentoApiController::class, 'update'])->name('pagamentos.update');
    Route::post('/pagamentos/{pagamento}/comprovantes', [PagamentoApiController::class, 'storeComprovante'])->name('pagamentos.comprovantes.store');
    Route::delete('/pagamentos/{pagamento}/comprovantes/{comprovante}', [PagamentoApiController::class, 'destroyComprovante'])->name('pagamentos.comprovantes.destroy');
});
