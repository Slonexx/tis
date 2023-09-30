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
        $this->appId = '7bd5e7ce-8d4b-4225-9d8a-4a2568690121';
        $this->appUid = 'ukassa.smartinnovations';
        $this->secretKey = "kh4M8ESbQmphrLVsYDqHO3qPVGi6MQexOWmu20QQVZia854ExZzcLdfTWuwiI3n0Ca3MoaFcF7HGPQWgn4DgMwjVz6JaisTsQ23xf56FSc3G1tE8oooTPiRjmrSZTKGn";
        $this->appBaseUrl ='https://smarttis.kz/';
        $this->moyskladVendorApiEndpointUrl = 'https://apps-api.moysklad.ru/api/vendor/1.0';
        $this->moyskladJsonApiEndpointUrl = 'https://api.moysklad.ru/api/remap/1.2';
    }


}
