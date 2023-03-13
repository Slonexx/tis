<?php

use App\Http\Controllers\installOrDeleteController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/ticket',[TicketController::class,'initTicket']);

Route::post('/installOfDelete',[installOrDeleteController::class,'insert']);
