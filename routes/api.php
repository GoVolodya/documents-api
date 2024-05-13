<?php

use App\Http\Controllers\DocumentsController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/documents')->group(function () {
    Route::get('/', [DocumentsController::class, 'index']);

    Route::middleware('auth:sanctum')->missing(function (Request $request) {
        return response()->json(['error' => 'Document not found.'], Response::HTTP_NOT_FOUND);
    })->group(function () {
        Route::get('/{document}', [DocumentsController::class, 'show']);
        Route::post('/', [DocumentsController::class, 'store']);
        Route::put('/{document}', [DocumentsController::class, 'update']);
        Route::delete('/delete', [DocumentsController::class, 'deleteMultiple']);
        Route::delete('/{document}', [DocumentsController::class, 'destroy']);
    });
});
