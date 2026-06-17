<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JuegoController;

// El chatbot del juego es la pagina principal del proyecto.
Route::get('/', [JuegoController::class, 'index'])->name('juego.index');
Route::post('/consultar', [JuegoController::class, 'consultar'])->name('juego.consultar');
Route::post('/limpiar', [JuegoController::class, 'limpiar'])->name('juego.limpiar');
