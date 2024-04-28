<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mainSetting extends Model
{

    protected $fillable = [
        'accountId',
        'tokenMs',
        'authtoken',
    ];

    public static function accId($accountId): object
    {
        $model = mainSetting::where('accountId',  $accountId )->get()->first();
        if ($model != null) {
            return (object) [
                'query' => $model,
                'toArray' => $model->toArray(),
            ];
        } else {
            return (object) [
                'query' => $model,
                'toArray' => null,
            ];
        }
    }

}
