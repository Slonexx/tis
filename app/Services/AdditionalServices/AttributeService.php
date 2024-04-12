<?php

namespace App\Services\AdditionalServices;

use App\Clients\MsClient;
use GuzzleHttp\Exception\ClientException;

class AttributeService
{
    public function setAllAttributesMs($data): void
    {
        $apiKeyMs = $data['tokenMs'];
        $client = new MsClient($apiKeyMs);
        //$accountId = $data['accountId'];

        try {
            $docAttributes = $this->getDocAttributes();
            $payDocAttributes = $this->getPayDocAttributes();

            //dd($docAttributes, $payDocAttributes);

            $this->createAttributes($client, 'customerorder', $docAttributes);
            $this->createAttributes($client, 'demand', $docAttributes);
            $this->createAttributes($client, 'salesreturn', $docAttributes);
            $this->createAttributesCustomentity($client);

            $this->createAttributes($client, 'paymentin', $payDocAttributes);
            $this->createAttributes($client, 'paymentout', $payDocAttributes);
            $this->createAttributes($client, 'cashin', $payDocAttributes);
            $this->createAttributes($client, 'cashout', $payDocAttributes);
        } catch (ClientException $e) {
            dd($e, $e->getCode(), $e->getResponse(), $e->getResponse()->getBody()->getContents(), $e->getMessage());
        }
    }

    private function createAttributes(MsClient $client, $entityType, $attributes): void
    {
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/" . $entityType . "/metadata/attributes";
        $json = $client->get($url);


        foreach ($attributes as $attribute) {
            if ($this->isAttributeExists($json, $attribute['name'])) {
                $client->post($url, $attribute);
            }
        }
    }

    private function isAttributeExists($json, $attributeName): bool
    {
        if ($json->meta->size > 0)
        foreach ($json->rows as $row) {
            if ($attributeName == $row->name){

                //dd($attributeName, $row);
                return false; break;
            }
        }
        return true;
    }

    public function getDocAttributes(): array
    {
        return [
            0 => [
                "name" => "фискальный номер (ТИС)",
                "type" => "string",
                "required" => false,
                "show" => false,
                "description" => "данное дополнительнее поле отвечает за фискальный номер чека (Онлайн ккм)",
            ],
            1 => [
                "name" => "Ссылка для QR-кода (ТИС)",
                "type" => "link",
                "required" => false,
                "description" => "данное дополнительнее поле отвечает за ссылку на QR-код чека (Онлайн ккм)",
            ],
            2 => [
                "name" => "Фискализация (ТИС)",
                "type" => "boolean",
                "required" => false,
                "show" => false,
                "description" => "данное дополнительное поле отвечает за проведения фискализации, если стоит галочка то фискализация была (Онлайн ккм)",
            ],
            3 => [
                "name" => "ID (ТИС)",
                "type" => "string",
                "required" => false,
                "show" => false,
                "description" => "уникальный идентификатор по данному дополнительному полю идёт синхронизация с ТИС (Онлайн ккм)",
            ],
        ];
    }

    public function getPayDocAttributes(): array
    {
        return [
            0 => [
                "name" => "Фискализация (ТИС)",
                "type" => "boolean",
                "required" => false,
                "description" => "Данное дополнительное поле отвечает за проведения фискализации, если стоит галочка то фискализация была (Онлайн ККМ)",
            ],
        ];
    }

    private function createAttributesCustomentity(MsClient $client): void
    {
        $json = $client->post("https://api.moysklad.ru/api/remap/1.2/entity/customentity/", ['name' => 'Тип оплаты (Онлайн ККМ)']);
        $client->post("https://api.moysklad.ru/api/remap/1.2/entity/customentity/" . $json->id, [
            ['name' => "Наличные"],
            ['name' => "Картой"],
            ['name' => "Мобильная"],
        ]);



        $client->post("https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes",
            [
                "customEntityMeta" => [
                    "href" => 'https://api.moysklad.ru/api/remap/1.2/context/companysettings/metadata/customEntities/'. $json->id,
                    "type" => "customentitymetadata",
                    "mediaType" => "application/json",
                ],
                "name" => "Тип оплаты (Онлайн ККМ)",
                "type" => "customentity",
                "required" => false,
            ]
        );
        $client->post("https://api.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes",
            [
                "customEntityMeta" => [
                    "href" => 'https://api.moysklad.ru/api/remap/1.2/context/companysettings/metadata/customEntities/'. $json->id,
                    "type" => "customentitymetadata",
                    "mediaType" => "application/json",
                ],
                "name" => "Тип оплаты (Онлайн ККМ)",
                "type" => "customentity",
                "required" => false,
            ]
        );
        $client->post("https://api.moysklad.ru/api/remap/1.2/entity/salesreturn/metadata/attributes",
            [
                "customEntityMeta" => [
                    "href" => 'https://api.moysklad.ru/api/remap/1.2/context/companysettings/metadata/customEntities/'. $json->id,
                    "type" => "customentitymetadata",
                    "mediaType" => "application/json",
                ],
                "name" => "Тип оплаты (Онлайн ККМ)",
                "type" => "customentity",
                "required" => false,
            ]
        );

    }

}
