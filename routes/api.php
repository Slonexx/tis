<?php

use Illuminate\Http\Request;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('ticket',[TicketController::class,'initTicket']);
