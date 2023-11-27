<?php

namespace App\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

class TestKassClient
{
    private Client $client;

    public function __construct($authToken)
    {
        $this->client = new Client([
            'base_uri' => 'https://test.ukassa.kz/api/',
            'headers' => [
                'Authorization' => 'Token '.$authToken,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function GETClient($url){
        $res = $this->client->get($url);
        return json_decode($res->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function POSTClient($url, $body){
        $res = $this->client->post($url,[
            'body' => json_encode($body),
        ]);

        return json_decode($res->getBody());
    }

}
