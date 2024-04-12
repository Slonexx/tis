<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class globalObjectController extends Controller
{
    public string $URL_ukassa;
    public string $apiURL_ukassa;

    public string $test_URL_ukassa;
    public string $test_apiURL_ukassa;

    /**
     * @param $URL_ukassa
     */
    public function __construct()
    {
        $this->URL_ukassa = 'https://ukassa.kz/';
        //$this->apiURL_ukassa = 'https://test.ukassa.kz/api/';
        $this->apiURL_ukassa = 'https://ukassa.kz/api/';

        $this->test_URL_ukassa = 'https://test.ukassa.kz/';
        $this->test_apiURL_ukassa = 'https://test.ukassa.kz/api/';

    }


}
