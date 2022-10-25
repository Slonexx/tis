<?php

use App\Http\Controllers\Config\DeleteVendorApiController;
use App\Http\Controllers\Web\indexController;
use Illuminate\Support\Facades\Route;

Route::get('delete/{accountId}/', [DeleteVendorApiController::class, 'delete']);

Route::get('/', [indexController::class, 'index']);
Route::get('/{accountId}/', [indexController::class, 'indexShow'])->name('main');

