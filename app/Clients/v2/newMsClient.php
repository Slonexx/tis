<?php

namespace App\Clients\v2;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class newMsClient{

    private Client $client;

    public function __construct($apiKey) {
        $this->client = new Client([
            'headers' => [
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'gzip',
            ]
        ]);
    }

    public function newGet($url): object
    {
        try {
            $answer = $this->client->get($url);
            return  $this->ResponseHandler($answer);
        } catch (BadResponseException $e) {
            return $this->ResponseHandlerField($e);
        }
    }


    /**
     * @throws GuzzleException
     */
    public function getForToken(string $url, $token)
    {
        $client = new Client([
            'headers' => [
                'Authorization' =>  $token,
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'gzip',
            ]
        ]);

        $res =$client->get($url,[
            'Accept' => 'application/json',
        ]);

        return json_decode($res->getBody());
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
