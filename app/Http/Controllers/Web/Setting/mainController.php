<?php

namespace App\Http\Controllers\Web\Setting;

use App\Clients\KassClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\AppInstanceContoller;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\globalObjectController;
use App\Services\workWithBD\DataBaseService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class mainController extends Controller
{
    public function getMain(Request $request, $accountId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $isAdmin = $request->isAdmin;

        $SettingBD = new getMainSettingBD($accountId);


        return view('setting.authToken', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,

            'token' => $SettingBD->authtoken,
        ]);
    }


    public function postMain(Request $request, $accountId): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $setting = new getSettingVendorController($accountId);
        $SettingBD = new getMainSettingBD($accountId);
        $isAdmin = $request->isAdmin;
        if ($accountId == '1dd5bd55-d141-11ec-0a80-055600047495') $Config = new globalObjectController(false);
        else $Config = new globalObjectController();

        $token = $request->token;
        if ($token == null) {
            return view('setting.authToken', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,

                'message' => "Отсутствует токен",
                'token' => null,
            ]);
        }

        try {
            $Client = new KassClient($token);
            $get_check = $Client->GETClient($Config->apiURL_ukassa.'auth/get_user/');

            if ($SettingBD->tokenMs == null){
                DataBaseService::createMainSetting($accountId, $setting->TokenMoySklad, $token);
            } else {
                DataBaseService::updateMainSetting($accountId, $setting->TokenMoySklad, $token);
            }
            $cfg = new cfg();
            $app = AppInstanceContoller::loadApp($cfg->appId, $accountId);
            $app->status = AppInstanceContoller::ACTIVATED;
            $vendorAPI = new VendorApiController();
            $vendorAPI->updateAppStatus($cfg->appId, $accountId, $app->getStatusName());
            $app->persist();
            return to_route('getKassa', ['accountId' => $accountId, 'isAdmin' => $isAdmin]);
        } catch (\Throwable $e){
            return view('setting.authToken', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,

                'message' => 'ошибка: ' . $e->getMessage(),
                'token' => null,
            ]);
        }


    }


    public function createAuthToken(Request $request, $accountId): JsonResponse
    {
        if ($accountId == '1dd5bd55-d141-11ec-0a80-055600047495') $config = new globalObjectController(false);
        else $config = new globalObjectController();
        $url = $config->apiURL_ukassa.'auth/login/';

        $client = new Client();
        try {
            $post = $client->post($url, [
                'form_params' => [
                    'email' => $request->email,
                    'password' => $request->password,
                ],
            ]);
            return response()->json([
                'status' => true,
                'full_name' => json_decode($post->getBody())->full_name,
                'auth_token' => json_decode($post->getBody())->auth_token,
            ]);
        } catch (BadResponseException $e){
            return response()->json([
                'status' => false,
                'content' => $e->getResponse()->getBody()->getContents(),
            ]);
        }



    }
}
