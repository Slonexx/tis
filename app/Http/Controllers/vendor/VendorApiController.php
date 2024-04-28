<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

require_once 'jwt.lib.php';

class VendorApiController extends Controller
{
    function context(string $contextKey)
    {
        return $this->request('POST', '/context/' . $contextKey);
    }

    function updateAppStatus(string $accountId, string $status)
    {
        $appId = Config::get("Global.appId");

        return $this->request('PUT',
            "/apps/$appId/$accountId/status",
            ["status" => $status]);
    }

    private function request(string $method, $path, $body = null)
    {
        $url = Config::get("Global.moyskladVendorApiEndpointUrl") . $path;
        $bearerToken = buildJWT();

        $client = new Client();

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $bearerToken,
                'Accept-Encoding' => 'gzip',
                'Content-type' => 'application/json'
            ]
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        //dd($method, $url, $options);

        try {
            $response = $client->request($method, $url, $options);
            $data = [
                'status' => true,
                'data' => json_decode($response->getBody()->getContents()),
            ];
            $convertedData = new Collection($data);
            return json_decode($convertedData->toJson());
        } catch (BadResponseException $e){

            $data = [
                'status' => false,
                'data' => json_decode($e->getResponse()->getBody()->getContents()),
                'message' => $e->getMessage(),
                'request' => $e->getRequest(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
            ];

            $convertedData = new Collection($data);

            return json_decode($convertedData->toJson());
        }


    }
}

function buildJWT(): string
{
    $token = array(
        "sub" =>  Config::get("Global.appUid"),
        "iat" => time(),
        "exp" => time() + 300,
        "jti" => bin2hex(random_bytes(32))
    );
    return JWT::encode($token,  Config::get("Global.secretKey"));
}
