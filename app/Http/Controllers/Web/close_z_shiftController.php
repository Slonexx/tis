<?php

namespace App\Http\Controllers\Web;

use App\Clients\KassClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Http\Controllers\globalObjectController;
use App\Models\htmlResponce;
use App\Models\zHtmlResponce;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class close_z_shiftController extends Controller
{
    public function getZShift(Request $request, $accountId): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $isAdmin = $request->isAdmin;
        $SettingBD = new getMainSettingBD($accountId);
        if ($accountId = '1dd5bd55-d141-11ec-0a80-055600047495') $Config = new globalObjectController(false);
        else $Config = new globalObjectController();

        $ClientTIS = new KassClient($SettingBD->authtoken);
        try {
            $get_user = $ClientTIS->GETClient($Config->apiURL_ukassa.'auth/get_user/');
        } catch (\Throwable $e){
            return to_route('errorSetting', ['error' => $e->getMessage()]);
        }

        $kassa = $get_user->user_kassas->kassa;

        return view('main.ZCloseShift', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,

            'kassa' => $kassa,
        ]);
    }
    public function postZShift(Request $request, $accountId): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $isAdmin = $request->isAdmin;
        $SettingBD = new getMainSettingBD($accountId);
        $Config = new globalObjectController();
        $ClientTIS = new KassClient($SettingBD->authtoken);

        try {
            //dd($Config->apiURL_ukassa.'kassa/close_z_shift');

            $get_user = $ClientTIS->GETClient($Config->apiURL_ukassa.'auth/get_user/');
            $kassa = $get_user->user_kassas->kassa;

            $body = ['kassa'=> $request->idKassa, 'html_code'=>true];
            $close_shift = $ClientTIS->POSTClient($Config->apiURL_ukassa.'kassa/close_z_shift/', $body);

            zHtmlResponce::create([
                'accountId' => $accountId,
                'html' => $close_shift->html,
            ]);

            return view('main.ZCloseShift', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,

                'kassa' => $kassa,
                'html' => $close_shift->html,
                'message_good' => 'Смена закрыта',
            ]);

        } catch (BadResponseException $e){
            return to_route('errorSetting', ['accountId' => $accountId,  'isAdmin' => $isAdmin, 'error' => json_decode($e->getResponse()->getBody()->getContents())->message ]);
        }


    }
    public function printZShift(Request $request, $accountId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $find = zHtmlResponce::query()->where('accountId', $accountId)->latest()->first();
        $result = $find->getAttributes();

        return view( 'popup.print', [ 'html' => $result['html'] ] );
    }
}
