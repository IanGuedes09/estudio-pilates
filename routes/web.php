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

// API protegida (dados reais do banco).
Route::middleware('auth')->prefix('api/v1')->name('api.v1.')->group(function () {
    Route::get('/dashboard-resumo', [DashboardApiController::class, 'resumo'])->name('dashboard-resumo');

    Route::get('/alunos', [AlunoApiController::class, 'index'])->name('alunos.catalogo');

    Route::get('/agenda', [AgendaApiController::class, 'index'])->name('agenda.index');
    Route::post('/agenda', [AgendaApiController::class, 'store'])->name('agenda.store');
    Route::patch('/agenda/{agendaItem}', [AgendaApiController::class, 'update'])->name('agenda.update');

    Route::get('/pagamentos', [PagamentoApiController::class, 'index'])->name('pagamentos.index');
    Route::post('/pagamentos/{pagamento}/comprovantes', [PagamentoApiController::class, 'storeComprovante'])->name('pagamentos.comprovantes.store');
    Route::delete('/pagamentos/{pagamento}/comprovantes/{comprovante}', [PagamentoApiController::class, 'destroyComprovante'])->name('pagamentos.comprovantes.destroy');
});
