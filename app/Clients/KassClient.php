<?php

namespace App\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

class KassClient
{

    private $email;
    private $password;
    private $hashline;

    private Client $client;

    public function __construct($email,$password,$hashline)
    {
        $this->email = $email;
        $this->password = $password;
        $this->hashline = $hashline;

        $this->client = new Client([
            'base_uri' => 'https://test.ukassa.kz/api/auth/login/',
            'headers' => [
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

}
