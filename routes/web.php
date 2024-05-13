<?php

use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/download/{document}', [DocumentsController::class, 'download'])
    ->name('download')
    ->missing(function (Request $request) {
        return response()->json(['error' => 'Document not found.'], Response::HTTP_NOT_FOUND);
    }
);
Route::get('/token', [DocumentsController::class, 'showToken']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
