<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class globalObjectController extends Controller
{
    public string $URL_ukassa;
    public string $apiURL_ukassa;

    /**
     * @param $URL_ukassa
     */
    public function __construct()
    {
        $this->URL_ukassa = 'https://test.ukassa.kz/';
        $this->apiURL_ukassa = 'https://test.ukassa.kz/api/';
    }


}
