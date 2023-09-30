<?php

namespace App\Http\Controllers\Config\Lib;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class cfg extends Controller
{
    public $appId;
    public $appUid;
    public $secretKey;
    public $appBaseUrl;
    public $moyskladVendorApiEndpointUrl;
    public $moyskladJsonApiEndpointUrl;


    public function __construct()
    {
        $this->appId = '88d2dc2f-130e-412d-a7a4-c662195c1a12';
        $this->appUid = 'tus.smartinnovations';
        $this->secretKey = "c2bPgCSvr7lnbs1GYsMbhbfl5jbMJLdk7lu75cvGW9y34BEDd4F6QVnGy30zoSRU5huqkGZTiOM8uWqYuOM234SvNdoPZxlMPYs0Hll9MboxFbQ6hLP8x61b6vT6m5n3";
        $this->appBaseUrl = 'https://smartuchettis.bitrix24.site';
        $this->moyskladVendorApiEndpointUrl = 'https://apps-api.moysklad.ru/api/vendor/1.0';
        $this->moyskladJsonApiEndpointUrl = 'https://api.moysklad.ru/api/remap/1.2';
    }


}
