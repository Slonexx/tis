<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Config\Lib\AppInstanceContoller;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class getSettingVendorController extends Controller
{
    var $appId;
    var $accountId;
    var $TokenMoySklad;


    public function __construct($accountId)
    {

        $cfg = new cfg();

        $appId = $cfg->appId;
        $json = AppInstanceContoller::loadApp($appId, $accountId);

        $this->appId = $json->appId;
        $this->accountId = $json->accountId;
        $this->TokenMoySklad = $json->TokenMoySklad;


        return $json;

    }



}
