<?php

namespace App\Http\Controllers\Web\Setting;

use App\Clients\KassClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Http\Controllers\globalObjectController;
use App\Services\workWithBD\DataBaseService;
use Illuminate\Http\Request;

class KassaController extends Controller
{
    public function getKassa(Request $request, $accountId): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $isAdmin = $request->isAdmin;
        $SettingBD = new getMainSettingBD($accountId);
        $Config = new globalObjectController();

        $ClientTIS = new KassClient($SettingBD->authtoken);
        try {
            $get_user = $ClientTIS->GETClient($Config->apiURL_ukassa.'auth/get_user/');
            $department = $ClientTIS->GETClient($Config->apiURL_ukassa.'department');
        } catch (\Throwable $e){
            return to_route('errorSetting', ['accountId' => $accountId,  'isAdmin' => $isAdmin, 'error' => $e->getMessage()]);
        }

        $kassa = $get_user->user_kassas->kassa;


        return view('setting.kassa', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,

            'kassa' => $kassa,
            'department' => $department
        ]);
    }

    public function postKassa(Request $request, $accountId): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $isAdmin = $request->isAdmin;

        try {
            DataBaseService::createDocumentSetting($accountId, $request->idKassa, $request->idDepartment, null, null, null, null);
            return to_route('getDocument', ['accountId' => $accountId, 'isAdmin' => $isAdmin]);
        } catch (\Throwable $e){
            $SettingBD = new getMainSettingBD($accountId);
            $Config = new globalObjectController();
            $ClientTIS = new KassClient($SettingBD->authtoken);

            $get_user = $ClientTIS->GETClient($Config->apiURL_ukassa.'auth/get_user/');
            $department = $ClientTIS->GETClient($Config->apiURL_ukassa.'department');
            $kassa = $get_user->user_kassas->kassa;

            $message = "Ошибка " . $e->getCode();
            return view('setting.kassa', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,

                'message' => $message,

                'kassa' => $kassa,
                'department' => $department
            ]);
        }
    }

}
