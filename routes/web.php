<?php

use App\Http\Controllers\Web\indexController;
use Illuminate\Support\Facades\Route;



Route::get('/', [indexController::class, 'index']);
Route::get('/{accountId}/', [indexController::class, 'indexShow'])->name('main');
