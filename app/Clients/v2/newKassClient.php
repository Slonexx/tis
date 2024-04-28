<?php

namespace App\Clients\v2;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

class newKassClient
{
    private Client $client;

    public function __construct($authToken = '')
    {
        $base_uri = Config::get("global.url_");
        $headers = ['Content-Type' => 'application/json' ];


        if ($authToken == '633a2f44115714594b99fcc948d54efa8c489b85') $base_uri = 'https://test.ukassa.kz/api/';

        if ($authToken = '') $headers = [
            'Authorization' => 'Token '.$authToken,
            'Content-Type' => 'application/json',
        ];


        $this->client = new Client([
            'base_uri' => $base_uri,
            'headers' => $headers
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function newGet($url): object
    {
        try {
            $answer = $this->client->get($url);
            return  $this->ResponseHandler($answer);
        } catch (BadResponseException $e) {
            return $this->ResponseHandlerField($e);
        }
    }

    public function newPost($url, $data): object
    {
        try {
            $answer = $this->client->post($url, [
                'json' => $data
            ]);
            return  $this->ResponseHandler($answer);
        } catch (BadResponseException $e) {
            return $this->ResponseHandlerField($e);
        }
    }

    private function ResponseHandler(ResponseInterface $post): object
    {
        return (object) [
            'status' => true,
            'body' => $post->getBody(),
            'data' => json_decode($post->getBody()->getContents()),
        ];
    }
    private function ResponseHandlerField(BadResponseException|\Exception $e): object
    {
        return (object) [
            'status' => false,
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'body' => $e->getResponse()->getBody(),
            'data' => json_decode($e->getResponse()->getBody()->getContents()),
        ];
    }

}
