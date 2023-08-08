<?php

namespace App\Http\Controllers\Web\Setting;

use App\Clients\MsClient;
use App\Http\Controllers\BD\getAccessByAccountId;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getDevices;
use App\Services\workWithBD\DataBaseService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AccessController extends Controller
{
    public function getWorker($accountId, Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $isAdmin = $request->isAdmin;
        $message = $request->message;

        $Workers = new getAccessByAccountId($accountId);

        $Setting = new getMainSettingBD($accountId);
        $tokenMs = $Setting->tokenMs;
        if ($Setting->tokenMs == null){
            return view('setting.no', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
            ]);
        }

        if ( array_key_exists(0, $Workers->access) ){ $Workers = '';
        } else $Workers = $Workers->access;


        $url_employee = 'https://online.moysklad.ru/api/remap/1.2/entity/employee';
        try {
            $Client = new MsClient($tokenMs);
            $Body_employee = $Client->get($url_employee)->rows;
        } catch (BadResponseException $e) {
            return view('setting.error', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'message' => $e->getResponse()->getBody()->getContents()
            ]);
        }



        foreach ($Body_employee as $id=>$item){
            $json = $Client->get( $url_employee.'/'.$item->id.'/security');
            if (property_exists($json, 'role')) {
                if (mb_substr ($json->role->meta->href, 53)== "cashier") {
                    unset($Body_employee[$id]);
                }
            }
        }


        return view('setting.access', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'message'=>$message,
            'employee' => $Body_employee,
            'workers' => $Workers,
        ]);
    }

    public function postWorker(Request $request, $accountId): \Illuminate\Http\RedirectResponse
    {
        $isAdmin = $request->isAdmin;
        $allRequest = $request->request;

        $workers = [];
        foreach ($allRequest as $id=>$item){
            if ($id == '_token') continue;
            if ($item == "0") $access = false;
            else $access = true;
            $workers[] = [
                'id' => $id,
                'accountId' => $accountId,
                'access' => $access,
            ];
        }

        foreach ($workers as $item){
            $First = DataBaseService::showWorkerFirst($item['id']);
            if ($First['accountId'] == null) DataBaseService::createWorker($item['id'], $accountId, $item['access']);
            else DataBaseService::updateWorker($item['id'], $item['access']);
        }
        $message = ' Настройки сохранились ';
        return redirect()->route('getWorker', [ 'accountId' => $accountId, 'isAdmin' => $isAdmin, 'message'=>$message ]);
    }

}
