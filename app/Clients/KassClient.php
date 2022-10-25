<?php

namespace App\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

class KassClient
{

    private $apiKey;
    private $password;
    private $kassaNumber;

    private Client $client;

    public function __construct($kassaNumber,$password,$apiKey)
    {
        $this->apiKey = $apiKey;
        $this->kassaNumber = $kassaNumber;
        $this->password = $password;

        $this->client = new Client([
            'base_uri' => 'https://test.ukassa.kz/api/auth/login/',
            'headers' => [
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

}
