<?php

use App\Http\Controllers\installOrDeleteController;
use App\Http\Controllers\integration\connectController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Web\Setting\mainController;
use App\Http\Controllers\WebhookMSController;
use Illuminate\Support\Facades\Route;

Route::post('/ticket',[TicketController::class,'initTicket']);

Route::post('/installOfDelete',[installOrDeleteController::class,'insert']);
Route::get('/get/createAuthToken/{accountId}', [mainController::class, 'createAuthToken']);


Route::post('/webhook/customerorder/',[WebhookMSController::class, 'customerorder']);
Route::post('/webhook/demand/',[WebhookMSController::class, 'customerorder']);
Route::post('/webhook/salesreturn/',[WebhookMSController::class, 'customerorder']);


Route::group(["prefix" => "integration"], function () {
    Route::get('client/connect/{accountId}', [connectController::class, 'connectClient']);
    Route::get('client/department/{accountId}', [connectController::class, 'getUserAndDepartment']);

    Route::get('client/get/ticket/{accountId}/{kkm_id}', [connectController::class, 'getUrlTicket']);
    Route::post('client/send/ticket/', [connectController::class, 'sendTicket']);
});
