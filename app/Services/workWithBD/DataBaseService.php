<?php

namespace App\Services\workWithBD;


use App\Models\mainSetting;
use App\Models\userLoadModel;

class DataBaseService
{
    public static function createPersonal($accountId, $email, $name, $status){
        userLoadModel::create([
            'accountId' => $accountId,
            'email' => $email,
            'name' => $name,
            'status' => $status,
        ]);
    }
    public static function showPersonal($accountId){
        $find = userLoadModel::query()->where('accountId', $accountId)->first();
        try {
            $result = $find->getAttributes();
        } catch (\Throwable $e) {
            $result = [
                'accountId' => $accountId,
                'email' => null,
                'name' => null,
                'status' => null,
            ];
        }
        return $result;
    }

    public static function createMainSetting($accountId, $tokenMs, $authtoken){
        mainSetting::create([
            'accountId' => $accountId,
            'tokenMs' => $tokenMs,
            'authtoken' => $authtoken,
        ]);
    }
    public static function showMainSetting($accountId){
        $find = mainSetting::query()->where('accountId', $accountId)->first();
        try {
            $result = $find->getAttributes();
        } catch (\Throwable $e) {
            $result = [
                'accountId' => $accountId,
                'tokenMs' => null,
                'authtoken' => null,
            ];
        }
        return $result;
    }
    public static function updateMainSetting($accountId, $tokenMs, $authtoken){
        $find = mainSetting::query()->where('accountId', $accountId);
        $find->update([
            'tokenMs' => $tokenMs,
            'authtoken' => $authtoken,
        ]);
    }
}
