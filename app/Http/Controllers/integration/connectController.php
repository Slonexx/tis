<?php

namespace App\Http\Controllers\integration;

use App\Clients\KassClient;
use App\Http\Controllers\Controller;
use App\Http\Controllers\globalObjectController;
use App\Models\htmlResponce;
use App\Services\ticket\integrationTicketService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class connectController extends Controller
{
    public function connectClient(Request $request, $accountId): JsonResponse
    {
        $config = new globalObjectController();


        $data = (object) [
            'email' => $request->email ?? '',
            'password' => $request->password ?? '',
        ];

        $client = new Client();
        if ($accountId == '1dd5bd55-d141-11ec-0a80-055600047495') {
            $url = $config->test_apiURL_ukassa.'auth/login/';
        } else {
            $url = $config->apiURL_ukassa.'auth/login/';
        }


        try {
            $post = $client->post($url, [
                'form_params' => $data,
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
    public function getUserAndDepartment(Request $request, $accountId): JsonResponse
    {
        $config = new globalObjectController();

        $ClientTIS = new KassClient($request->authtoken ?? '');
        if ($accountId == '1dd5bd55-d141-11ec-0a80-055600047495') {
            $url = $config->test_apiURL_ukassa;
        } else {
            $url = $config->apiURL_ukassa;
        }


        try {
            $get_user = $ClientTIS->GETClient($url.'auth/get_user/');
            $department = $ClientTIS->GETClient($url.'department');
            return response()->json([
                'status' => true,
                'get_user' => $get_user,
                'department' => $department,
            ]);
        } catch (BadResponseException $e){
            return response()->json([
                'status' => false,
                'content' => $e->getResponse()->getBody()->getContents(),
            ]);
        }



    }
    public function getUrlTicket($kkm_id, $accountId): Factory|View|Application
    {
        $find = htmlResponce::query()->where('accountId', $accountId)->where('kkm_id', $kkm_id)->latest()->first();
            if ($find != null) {
                return view( 'popup.print', [ 'html' => $find->toArray()['html'] ] );
            } else {
                return view( 'popup.print', [ 'html' => $find->toArray()['html'], 'message'=>'Чек не найден' ] );
            }
    }
    public function sendTicket(Request $request): JsonResponse
    {
        return (new integrationTicketService())->createTicket(json_decode(json_encode($request->all())));
    }

}
