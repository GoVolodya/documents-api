<?php

use App\Http\Controllers\DocumentsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/documents')->group(function () {
    Route::get('/', [DocumentsController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/{id}', [DocumentsController::class, 'show']);
        Route::post('/', [DocumentsController::class, 'store']);
        Route::put('/{id}', [DocumentsController::class, 'update']);
        Route::post('/delete', [DocumentsController::class, 'deleteMultiple']);
        Route::delete('/{id}', [DocumentsController::class, 'destroy']);
    });
});
