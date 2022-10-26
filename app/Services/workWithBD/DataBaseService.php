<?php

namespace App\Services\workWithBD;


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

}
