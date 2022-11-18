<?php

namespace App\Http\Controllers\Web\Setting;

use App\Clients\MsClient;
use App\Http\Controllers\BD\getAccessByAccountId;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getDevices;
use App\Services\workWithBD\DataBaseService;
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

        if ( array_key_exists(0, $Workers->access) ){
            $Workers = null;
        } else $Workers = $Workers->access;

        $Setting = new getSettingVendorController($accountId);
        $tokenMs = $Setting->TokenMoySklad;
        $url_employee = 'https://online.moysklad.ru/api/remap/1.2/entity/employee';
        $Client = new MsClient($tokenMs);
        $Body_employee = $Client->get($url_employee)->rows;
        $security = [];


        $urls = [];
        foreach ($Body_employee as $id=>$item){
            $url_security = $url_employee.'/'.$item->id.'/security';
            $urls [] = $url_security;
        }

        $pools = function (Pool $pool) use ($urls,$tokenMs){
            foreach ($urls as $url){
                $arrPools [] = $pool->withToken($tokenMs)->get($url);
            }
            return $arrPools;
        };

        $responses = Http::pool($pools);
        $count = 0;
        foreach ($Body_employee as $id=>$item){
            if ( isset($responses[$count]->object()->role) ){
                $Body_security = $responses[$count]->object()->role;
                $security[$item->id] = mb_substr ($Body_security->meta->href, 53);
            } else {
                $security[$item->id] = 'cashier';
            }

            $count++;
        }

        return view('setting.access', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'message'=>$message,
            'employee' => $Body_employee,
            'security' => $security,
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
        $message = [
            'alert' => ' alert alert-success alert-dismissible fade show in text-center ',
            'message' => ' Настройки сохранились ',
        ];
        return redirect()->route('getWorker', [ 'accountId' => $accountId, 'isAdmin' => $isAdmin, 'message'=>$message ]);
    }

}
