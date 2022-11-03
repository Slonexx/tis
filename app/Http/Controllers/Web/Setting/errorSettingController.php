<?php

namespace App\Http\Controllers\Web\Setting;

use App\Clients\KassClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Http\Controllers\globalObjectController;
use Illuminate\Http\Request;

class errorSettingController extends Controller
{
    public function getError(Request $request, $accountId){
        $isAdmin = $request->isAdmin;
        $error = $request->error;


        return view('setting.error', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'message' => $error,

        ]);
    }
}
