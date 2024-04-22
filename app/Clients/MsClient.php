<?php

namespace App\Clients;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;

class MsClient{

    private Client $client;

    public function __construct($apiKey) {
        //$this->apiKey = $apiKey;
        $this->client = new Client([
            'headers' => [
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'gzip',
            ]
        ]);
    }

    public function get($url){
        $res = $this->client->get($url,[
            'Accept' => 'application/json',
        ]);
        return json_decode($res->getBody());
    }

    public function post($url, $body){
        $res = $this->client->post($url,[
            'body' => json_encode($body),
        ]);

        return json_decode($res->getBody());
    }

    public function put($url, $body){
        $res = $this->client->put($url,[
            'Accept' => 'application/json',
            'body' => json_encode($body),
        ]);
        return json_decode($res->getBody());
    }

    public function delete($url, $body){
        $res = $this->client->delete($url,[
            'Accept' => 'application/json',
            'body' => json_encode($body),
        ]);
        return json_decode($res->getBody());
    }

    public function newPost($url, $body): object
    {
        try {
            $answer = $this->client->post($url, [
                'json' => $body,
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
