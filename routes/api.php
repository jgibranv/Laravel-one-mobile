<?php

use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de la API (prefijo /api)
|--------------------------------------------------------------------------
| Los datos vienen de un repositorio mock (JSON), pero el contrato HTTP es
| el de una API REST real: verbo, recurso y codigo de estado.
*/

Route::get('/tickets', [TicketController::class, 'index']);
Route::post('/tickets', [TicketController::class, 'store']);
Route::get('/tickets/{id}', [TicketController::class, 'show'])->whereNumber('id');
Route::patch('/tickets/{id}/estatus', [TicketController::class, 'updateEstatus'])->whereNumber('id');
