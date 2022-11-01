<?php

use App\Http\Controllers\Config\collectionOfPersonalController;
use App\Http\Controllers\Config\DeleteVendorApiController;
use App\Http\Controllers\Web\indexController;
use App\Http\Controllers\Web\Setting\documentController;
use App\Http\Controllers\Web\Setting\mainController;
use Illuminate\Support\Facades\Route;

Route::get('delete/{accountId}/', [DeleteVendorApiController::class, 'delete']);
//для админа
Route::get('/web/getPersonalInformation/', [collectionOfPersonalController::class, 'getPersonal']);
Route::get('/collectionOfPersonalInformation/{accountId}/', [collectionOfPersonalController::class, 'getCollection']);



Route::get('/', [indexController::class, 'index']);
Route::get('/{accountId}/', [indexController::class, 'indexShow'])->name('main');


Route::get('/Setting/createAuthToken/{accountId}', [mainController::class, 'getMain']);
Route::post('/Setting/createAuthToken/{accountId}', [mainController::class, 'postMain']);

Route::get('/Setting/Document/{accountId}', [documentController::class, 'getDocument'])->name('getDocument');
Route::post('/Setting/Document/{accountId}', [documentController::class, 'postDocument']);



Route::get('/get/createAuthToken/{accountId}', [mainController::class, 'createAuthToken']);



