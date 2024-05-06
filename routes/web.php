<?php

use App\Http\Controllers\DocumentsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/download/{id}', [DocumentsController::class, 'download']);
