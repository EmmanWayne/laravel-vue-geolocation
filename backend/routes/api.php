<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecordatorioController;


Route::get('/reminders', [RecordatorioController::class, 'index']);
Route::post('/reminders', [RecordatorioController::class, 'store']);
Route::get('/reminders/{id}', [RecordatorioController::class, 'show']);
Route::put('/reminders/{id}', [RecordatorioController::class, 'update']);
Route::delete('/reminders/{id}', [RecordatorioController::class, 'destroy']);


// Ruta de prueba sin autenticación
Route::get('/test', function () {
    return response()->json(['message' => 'API funcionando correctamente'], 200);
});
