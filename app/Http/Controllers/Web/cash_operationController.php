<?php

namespace App\Http\Controllers\Web;

use App\Clients\KassClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Http\Controllers\globalObjectController;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class cash_operationController extends Controller
{
    public function getCash(Request $request, $accountId): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $isAdmin = $request->isAdmin;
        $SettingBD = new getMainSettingBD($accountId);
        $Config = new globalObjectController();

        $ClientTIS = new KassClient($SettingBD->authtoken);
        try {
            $get_user = $ClientTIS->GETClient($Config->apiURL_ukassa.'auth/get_user/');
        } catch (\Throwable $e){
            return to_route('errorSetting', ['accountId' => $accountId,  'isAdmin' => $isAdmin, 'error' => $e->getMessage()]);
        }

        $kassa = $get_user->user_kassas->kassa;

        return view('main.cash', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,

            'kassa' => $kassa,
        ]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postCash(Request $request, $accountId): \Illuminate\Http\JsonResponse
    {


        $isAdmin = $request->isAdmin;
        $SettingBD = new getMainSettingBD($accountId);
        $Config = new globalObjectController();
        $url = (string) $Config->apiURL_ukassa.'operation/cash_operation/';
        $Client = new KassClient($SettingBD->authtoken);
        try {
            $get_user = $Client->GETClient($Config->apiURL_ukassa.'auth/get_user/');
            $kassa = $get_user->user_kassas->kassa;
        } catch (BadResponseException  $e){

        }



        $Body = [
            'kassa' => $request->idKassa,
            'operation_type' => $request->operation_type,
            'amount' => $request->amount,
        ];

        try {
            $postBody = $Client->POSTClient($url, $Body);
            return response()->json(
                [
                    'status' => true,
                    'accountId' => $accountId,
                    'isAdmin' => $isAdmin,

                    'kassa' => $kassa,
                    'message_good' => $postBody->message,
                ]
            );
        } catch (BadResponseException  $e){

            return response()->json([
                'status' => false,
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,

                'kassa' => $kassa,
                'message' => json_decode($e->getResponse()->getBody()->getContents(), true),
            ]);
        }

    }
}
