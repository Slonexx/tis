<?php

namespace App\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

class KassClient
{


    private $authtoken;

    private Client $client;

    public function __construct($authtoken)
    {
        $this->authtoken = $authtoken;

        $this->client = new Client([
            'base_uri' => 'https://test.ukassa.kz/api/',
            'headers' => [
                'Authorization' => $authtoken,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

}
