<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;

class getSettingVendorController extends Controller
{
    var $appId;
    var $accountId;
    var $TokenMoySklad;
    var $status;


    public function __construct($accountId)
    {

        $json = Lib::loadApp($accountId);

        $this->appId = $json->appId;
        $this->accountId = $json->accountId;
        $this->TokenMoySklad = $json->TokenMoySklad;
        $this->status = $json->status;

        return $json;

    }



}
