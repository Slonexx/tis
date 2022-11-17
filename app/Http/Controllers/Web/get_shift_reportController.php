<?php

namespace App\Http\Controllers\Web;

use App\Clients\KassClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Http\Controllers\globalObjectController;
use App\Models\XhtmlResponce;
use App\Models\zHtmlResponce;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class get_shift_reportController extends Controller
{
    public function getXShift(Request $request, $accountId): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $isAdmin = $request->isAdmin;
        $SettingBD = new getMainSettingBD($accountId);
        $Config = new globalObjectController();

        $Config = new globalObjectController();

        $ClientTIS = new KassClient($SettingBD->authtoken);
        try {
            $get_user = $ClientTIS->GETClient($Config->apiURL_ukassa.'auth/get_user/');
        } catch (\Throwable $e){
            return to_route('errorSetting', ['error' => $e->getMessage()]);
        }

        $kassa = $get_user->user_kassas->kassa;

        return view('main.xShift', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,

            'kassa' => $kassa,
        ]);
    }
    public function postXShift(Request $request, $accountId): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
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
            $close_shift = $ClientTIS->POSTClient($Config->apiURL_ukassa.'kassa/get_shift_report/', $body);

            XhtmlResponce::create([
                'accountId' => $accountId,
                'html' => $close_shift->html,
            ]);

            return view('main.xShift', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,

                'kassa' => $kassa,
                'html' => $close_shift->html,
                'message_good' => 'X-отчёт сформирован',
            ]);

        } catch (BadResponseException $e){
            return to_route('errorSetting', ['accountId' => $accountId,  'isAdmin' => $isAdmin, 'error' => json_decode($e->getResponse()->getBody()->getContents())->message ]);
        }
    }
    public function printXShift(Request $request, $accountId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $find = XhtmlResponce::query()->where('accountId', $accountId)->latest()->first();
        $result = $find->getAttributes();

        return view( 'popup.print', [ 'html' => $result['html'] ] );
    }
    public function infoXShift(Request $request, $accountId){
        $SettingBD = new getMainSettingBD($accountId);
        $Config = new globalObjectController();
        $ClientTIS = new KassClient($SettingBD->authtoken);
        try {
            $body = ['kassa'=> $request->idKassa, 'html_code'=>false];
            $close_shift = $ClientTIS->POSTClient($Config->apiURL_ukassa.'kassa/get_shift_report/', $body);

            return response()->json([
                'status' => true,
                'shift' => $close_shift,
            ]);

        } catch (BadResponseException $e){
            return response()->json([
                'status' => false,
                'error' => json_decode($e->getResponse()->getBody()->getContents())->message,
            ]);
        }
    }
}
