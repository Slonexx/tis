<?php

namespace App\Services\AdditionalServices;

use App\Clients\MsClient;
use GuzzleHttp\Exception\ClientException;

class AttributeService
{
    public function setAllAttributesMs($data): void
    {
        $apiKeyMs = $data['tokenMs'];
        $accountId = $data['accountId'];

        try {
            $this->createOrderAttributes($apiKeyMs);
            $this->createDemandAttributes($apiKeyMs);
            $this->createSalesReturn($apiKeyMs);

            $this->createPaymentInAttributes($apiKeyMs);
            $this->createPaymentOutAttributes($apiKeyMs);
            $this->createCashInAttributes($apiKeyMs);
            $this->createCashOutAttributes($apiKeyMs);
        } catch (ClientException $e){

        }
    }

    private function createOrderAttributes($apiKeyMs): void
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createDemandAttributes($apiKeyMs): void
    {
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createSalesReturn($apiKeyMs){
        $bodyAttributes = $this->getDocAttributes();
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/salesreturn/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createPaymentInAttributes($apiKeyMs):void
    {
        $bodyAttributes = $this->getPayDocAttributes();
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/paymentin/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createPaymentOutAttributes($apiKeyMs):void
    {
        $bodyAttributes = $this->getPayDocAttributes();
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/paymentout/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    private function createCashInAttributes($apiKeyMs):void
    {
        $bodyAttributes = $this->getPayDocAttributes();
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/cashin/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    public function createCashOutAttributes($apiKeyMs)
    {
        $bodyAttributes = $this->getPayDocAttributes();
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/cashout/metadata/attributes";
        $client = new MsClient($apiKeyMs);
        $this->getBodyToAdd($client, $url, $bodyAttributes);
    }

    public function getDocAttributes(): array
    {
        return [
            0 => [
                "name" => "???????????????????? ?????????? (??????)",
                "type" => "string",
                "required" => false,
                "description" => "???????????? ???????????????????????????? ???????? ???????????????? ???? ???????????????????? ?????????? ???????? (??????)",
            ],
            1 => [
                "name" => "???????????? ?????? QR-???????? (??????)",
                "type" => "link",
                "required" => false,
                "description" => "???????????? ???????????????????????????? ???????? ???????????????? ???? ???????????? ???? QR-?????? ???????? (??????)",
            ],
            2 => [
                "name" => "???????????????????????? (??????)",
                "type" => "boolean",
                "required" => false,
                "description" => "???????????? ???????????????????????????? ???????? ???????????????? ???? ???????????????????? ????????????????????????, ???????? ?????????? ?????????????? ???? ???????????????????????? ???????? (??????)",
            ],
            3 => [
                "name" => "ID (??????)",
                "type" => "string",
                "required" => false,
                "show" => false,
                "description" => "???????????????????? ?????????????????????????? ???? ?????????????? ?????????????????????????????? ???????? ???????? ?????????????????????????? ?? ?????? (??????)",
            ],
        ];
    }

    public function getPayDocAttributes(){
        return [
            0 => [
                "name" => "???????????????????????? (??????)",
                "type" => "boolean",
                "required" => false,
                "description" => "???????????? ???????????????????????????? ???????? ???????????????? ???? ???????????????????? ????????????????????????, ???????? ?????????? ?????????????? ???? ???????????????????????? ???????? (??????)",
            ],
        ];
    }

    /**
     * @param MsClient $client
     * @param string $url
     * @param array $bodyAttributes
     * @return void
     */
    private function getBodyToAdd(MsClient $client, string $url, array $bodyAttributes): void
    {
        $json = $client->get($url);
        //$bodyToAdd = [];

        foreach ($bodyAttributes as $body) {
            $foundedAttrib = false;
            foreach ($json->rows as $row) {
                if ($body["name"] == $row->name) {
                    $foundedAttrib = true;
                    break;
                }
            }
            if (!$foundedAttrib) {
                $client->post($url,$body);
                //array_push($bodyToAdd, $body);
            }
        }

        //dd($bodyToAdd);

//        if (count($bodyToAdd) > 0) {
//            $client->multiPost($url, $bodyToAdd);
//        }
        //return $bodyToAdd;
    }

}
