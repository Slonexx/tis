<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Services\workWithBD\DataBaseService;
use Illuminate\Http\Request;

class getMainSettingBD extends Controller
{
    var $accountId;
    var $tokenMs;
    var $authtoken;

    /**
     * @param $accountId
     */
    public function __construct($accountId)
    {
        $this->accountId = $accountId;

        $BD = DataBaseService::showMainSetting($accountId);
        $this->accountId = $BD['accountId'];
        $this->tokenMs = $BD['tokenMs'];
        $this->authtoken = $BD['authtoken'];

    }


}
