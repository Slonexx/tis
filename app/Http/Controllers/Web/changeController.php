<?php

namespace App\Http\Controllers\Web;

use App\Clients\KassClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Http\Controllers\globalObjectController;
use Illuminate\Http\Request;

class changeController extends Controller
{
    public function getChange(Request $request, $accountId){
        $isAdmin = $request->isAdmin;

        $SettingBD = new getMainSettingBD($accountId);
        if ($accountId == '1dd5bd55-d141-11ec-0a80-055600047495') $Config = new globalObjectController(false);
        else $Config = new globalObjectController();

        $ClientTIS = new KassClient($SettingBD->authtoken);
        try {
            $get_user = $ClientTIS->GETClient($Config->apiURL_ukassa.'auth/get_user/');
        } catch (\Throwable $e){
            return to_route('errorSetting', ['accountId' => $accountId,  'isAdmin' => $isAdmin, 'error' => $e->getMessage()]);
        }

        $kassa = $get_user->user_kassas->kassa;

        return view('main.change', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,

            'kassa' => $kassa,
        ]);

    }
}
