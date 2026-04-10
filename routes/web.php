<?php

use App\Http\Controllers\Api\MockVitaliApiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\AlunoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
});

// API mockada para desenvolvimento front-first.
Route::middleware('auth')->prefix('api/v1')->name('api.v1.')->group(function () {
    Route::get('/dashboard-resumo', [MockVitaliApiController::class, 'dashboardResumo'])->name('dashboard-resumo');

    Route::get('/agenda', [MockVitaliApiController::class, 'agenda'])->name('agenda.index');
    Route::get('/agenda/{id}', [MockVitaliApiController::class, 'agendaShow'])->name('agenda.show');

    Route::get('/pagamentos', [MockVitaliApiController::class, 'pagamentos'])->name('pagamentos.index');
    Route::get('/pagamentos/{id}', [MockVitaliApiController::class, 'pagamentosShow'])->name('pagamentos.show');

    Route::get('/comprovantes', [MockVitaliApiController::class, 'comprovantes'])->name('comprovantes.index');
    Route::get('/comprovantes/{id}', [MockVitaliApiController::class, 'comprovantesShow'])->name('comprovantes.show');
});
