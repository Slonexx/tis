<?php

namespace App\Services\Settings;

use App\Clients\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\BD\getPersonal;
use App\Http\Controllers\Config\collectionOfPersonalController;
use App\Services\workWithBD\DataBaseService;
use Illuminate\Support\Facades\File;
use function Psy\sh;

class InstallOrDeleteService
{
    public function insert($data)
    {
        return $this->check(
            $data['tokenMs'],
            $data['accountId']
        );
    }

    private function check(mixed $tokenMs, mixed $accountId)
    {

        $show = new getPersonal($accountId);
        if ($show->name == null){
            try {
                $collection = new collectionOfPersonalController();
                $collection->getCollection(null, $accountId);
                $show = new getPersonal($accountId);
            } catch (\Throwable $e){
                return response('',205);
            }
        }

        $url = 'https://online.moysklad.ru/api/remap/1.2/entity/store';
        $Client = new MsClient($tokenMs);


        $Setting = new getMainSettingBD($accountId);
        if ($Setting->tokenMs == null){
            DataBaseService::updatePersonal($accountId, $show->email, $show->name, "не прошел настройки" );
            return response('',201);
        }


        try {
            $response = $Client->get($url);
        } catch (\Throwable $e){
            if ($e->getCode() == 401){
                DataBaseService::updatePersonal($accountId, $show->email, $show->name, "деактивированный" );
                return response('',201);
            }
        }
        DataBaseService::updatePersonal($accountId, $show->email, $show->name, "активированный" );
        return response('',200);
    }
}
