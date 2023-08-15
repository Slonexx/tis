<?php

namespace App\Services\webhook;

use App\Clients\KassClient;
use App\Clients\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\globalObjectController;
use App\Services\MetaServices\MetaHook\AttributeHook;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;


class AutomatingServices
{

    private MsClient $msClient;
    private KassClient $kassClient;
    private getMainSettingBD $setting;
    private mixed $settingAutomation;
    private mixed $msOldBodyEntity;
    private AttributeHook $attributeHook;
    private  globalObjectController $Config;

    public function initialization(mixed $ObjectBODY, mixed $BDFFirst): array
    {
        $accountId = $BDFFirst['accountId'];
        $this->attributeHook = new AttributeHook();
        $this->setting = new getMainSettingBD($BDFFirst['accountId']);
        $this->msClient = new MsClient($this->setting->tokenMs);

        $this->kassClient = new KassClient($BDFFirst['accountId']);

        $this->msOldBodyEntity = $ObjectBODY;
        $this->settingAutomation = json_decode(json_encode($BDFFirst));
        $this->Config = new globalObjectController();


        return $this->createAutomating();
    }

    public function createAutomating(): array
    {
        $body = $this->createBody();

        if ($body != []) {
            try {
                $response = $this->kassClient->POSTClient($this->Config->apiURL_ukassa.'v2/operation/ticket/', $body);
            } catch (ClientException $exception) {
                return [
                    "ERROR",
                    "Ошибка при отправки",
                    "==========================================",
                    $exception->getResponse()->getBody()->getContents(),
                    "BODY",
                    "==========================================",
                    $body,
                ];
            }

            try {
                $this->writeToAttrib($body, $response);
            } catch (ClientException $exception) {
                return [
                    "ERROR",
                    "Ошибка при сохранении",
                    "==========================================",
                    json_decode($exception->getResponse()->getBody()->getContents()),
                    "BODY",
                    "==========================================",
                    $body,
                    "response",
                    "==========================================",
                    $response,
                ];
            }

            try {
                if ($this->setting->paymentDocument != null ){
                    $this->createPaymentDocument($body['payments']);
                }
            } catch (ClientException $exception) {
                return [
                    "ERROR",
                    "Ошибка при сохранении",
                    "==========================================",
                    json_decode($exception->getResponse()->getBody()->getContents()),
                    "BODY",
                    "==========================================",
                    $body,
                    "response",
                    "==========================================",
                    $response,
                ];
            }



            return [
                "SUCCESS",
                "Успешно отправилось и записалось",
                "==========================================",
                "BODY",
                "==========================================",
                $body,
                "response",
                "==========================================",
                $response,
            ];
        } else return [
            "ERROR",
            "Ошибка при создании тело запроса",
            "==========================================",
            "BODY",
            "==========================================",
            $body,
        ];

    }

    private function createBody(): array
    {
        $operation = $this->operation();

        if ($this->msOldBodyEntity->positions->meta->size === 0) {
            return [];
        }

        $items = $this->items();
        $payments = $this->payments();

        if ($items === null || $payments === null) {
            return [];
        }


        return [
            'operation' => (int) $operation,
            'kassa' => (int) $this->setting->idKassa,
            'payments' => $payments,
            'items' => $items,
            "total_amount" => (float) $this->msOldBodyEntity->sum / 100,
            'need_mark_code' => true,
            "as_html" => true,
        ];
    }

    private function createPaymentDocument(mixed $payments): void
    {
        $entity_type = null;
        match ($this->settingAutomation->entity) {
            0, "0" => $entity_type = 'customerorder',
            1, "1" => $entity_type = 'demand',
            2, "2" => $entity_type = 'salesreturn',
            default => null,
        };

        switch ($this->setting->paymentDocument){
            case "1": {
                $url = 'https://online.moysklad.ru/api/remap/1.2/entity/';
                if ($entity_type != 'salesreturn') {
                    $url = $url . 'cashin';
                } else {
                    //$url = $url . 'cashout';
                    break;
                }
                $body = [
                    'organization' => [  'meta' => [
                        'href' => $this->msOldBodyEntity->organization->meta->href,
                        'type' => $this->msOldBodyEntity->organization->meta->type,
                        'mediaType' => $this->msOldBodyEntity->organization->meta->mediaType,
                    ] ],
                    'agent' => [ 'meta'=> [
                        'href' => $this->msOldBodyEntity->agent->meta->href,
                        'type' => $this->msOldBodyEntity->agent->meta->type,
                        'mediaType' => $this->msOldBodyEntity->agent->meta->mediaType,
                    ] ],
                    'sum' => $this->msOldBodyEntity->sum,
                    'operations' => [
                        0 => [
                            'meta'=> [
                                'href' => $this->msOldBodyEntity->meta->href,
                                'metadataHref' => $this->msOldBodyEntity->meta->metadataHref,
                                'type' => $this->msOldBodyEntity->meta->type,
                                'mediaType' => $this->msOldBodyEntity->meta->mediaType,
                                'uuidHref' => $this->msOldBodyEntity->meta->uuidHref,
                            ],
                            'linkedSum' => $this->msOldBodyEntity->sum
                        ], ]
                ];
                $this->msClient->post($url, $body);
                break;
            }
            case "2": {
                $url = 'https://online.moysklad.ru/api/remap/1.2/entity/';
                if ($entity_type != 'salesreturn') {
                    $url = $url . 'paymentin';
                } else {
                    //$url = $url . 'paymentout';
                    break;
                }

                $rate_body = $this->msClient->get("https://online.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
                $rate = null;
                foreach ($rate_body as $item){
                    if ($item->name == "тенге" or $item->fullName == "Казахстанский тенге"){
                        $rate =
                            ['meta'=> [
                                'href' => $item->meta->href,
                                'metadataHref' => $item->meta->metadataHref,
                                'type' => $item->meta->type,
                                'mediaType' => $item->meta->mediaType,
                            ],
                            ];
                    }
                }

                $body = [
                    'organization' => [  'meta' => [
                        'href' => $this->msOldBodyEntity->organization->meta->href,
                        'type' => $this->msOldBodyEntity->organization->meta->type,
                        'mediaType' => $this->msOldBodyEntity->organization->meta->mediaType,
                    ] ],
                    'agent' => [ 'meta'=> [
                        'href' => $this->msOldBodyEntity->agent->meta->href,
                        'type' => $this->msOldBodyEntity->agent->meta->type,
                        'mediaType' => $this->msOldBodyEntity->agent->meta->mediaType,
                    ] ],
                    'sum' => $this->msOldBodyEntity->sum,
                    'operations' => [
                        0 => [
                            'meta'=> [
                                'href' => $this->msOldBodyEntity->meta->href,
                                'metadataHref' => $this->msOldBodyEntity->meta->metadataHref,
                                'type' => $this->msOldBodyEntity->meta->type,
                                'mediaType' => $this->msOldBodyEntity->meta->mediaType,
                                'uuidHref' => $this->msOldBodyEntity->meta->uuidHref,
                            ],
                            'linkedSum' => $this->msOldBodyEntity->sum
                        ], ],
                    'rate' => $rate
                ];
                if ($body['rate'] == null) unlink($body['rate']);
                $this->msClient->post($url, $body);
                break;
            }
            case "3": {
                $url = 'https://online.moysklad.ru/api/remap/1.2/entity/';
                $url_to_body = null;
                foreach ($payments as $item){
                    $change = 0;
                    if ($item['PaymentType'] == 0){
                        if ($entity_type != 'salesreturn') { $url_to_body = $url . 'cashin'; } else { break; }
                        if (isset($item['change'])) $change = $item['change'];
                    } else {
                        if ($entity_type != 'salesreturn') {
                            $url_to_body = $url . 'paymentin';
                        }
                    }

                    $rate_body =  $this->msClient->get("https://online.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
                    $rate = null;
                    foreach ($rate_body as $item_rate){
                        if ($item_rate->name == "тенге" or $item_rate->fullName == "Казахстанский тенге"){
                            $rate =
                                ['meta'=> [
                                    'href' => $item_rate->meta->href,
                                    'metadataHref' => $item_rate->meta->metadataHref,
                                    'type' => $item_rate->meta->type,
                                    'mediaType' => $item_rate->meta->mediaType,
                                ],
                                ];
                        }
                    }

                    $body = [
                        'organization' => [  'meta' => [
                            'href' => $this->msOldBodyEntity->organization->meta->href,
                            'type' => $this->msOldBodyEntity->organization->meta->type,
                            'mediaType' => $this->msOldBodyEntity->organization->meta->mediaType,
                        ] ],
                        'agent' => [ 'meta'=> [
                            'href' => $this->msOldBodyEntity->agent->meta->href,
                            'type' => $this->msOldBodyEntity->agent->meta->type,
                            'mediaType' => $this->msOldBodyEntity->agent->meta->mediaType,
                        ] ],
                        'sum' => ($item['Sum']-$change) * 100,
                        'operations' => [
                            0 => [
                                'meta'=> [
                                    'href' => $this->msOldBodyEntity->meta->href,
                                    'metadataHref' => $this->msOldBodyEntity->meta->metadataHref,
                                    'type' => $this->msOldBodyEntity->meta->type,
                                    'mediaType' => $this->msOldBodyEntity->meta->mediaType,
                                    'uuidHref' => $this->msOldBodyEntity->meta->uuidHref,
                                ],
                                'linkedSum' => $this->msOldBodyEntity->sum
                            ], ],
                        'rate' => $rate
                    ];
                    if ($body['rate'] == null) unlink($body['rate']);
                    $this->msClient->post($url_to_body, $body);
                }
                break;
            }
            case "4":{
                $url = 'https://online.moysklad.ru/api/remap/1.2/entity/';
                $url_to_body = null;
                foreach ($payments as $item){
                    $change = 0;
                    if ($item['PaymentType'] == 0){
                        if ($entity_type != 'salesreturn') {
                            if ($this->setting->OperationCash == 1) {
                                $url_to_body = $url . 'cashin';
                            }
                            if ($this->setting->OperationCash == 2) {
                                $url_to_body = $url . 'paymentin';
                            }
                            if ($this->setting->OperationCash == 0) {
                                continue;
                            }
                        }
                        if (isset($item['change'])) $change = $item['change'];
                    } else {
                        if ($entity_type != 'salesreturn') {
                            if ( $this->setting->OperationCard == 1) {
                                $url_to_body = $url . 'cashin';
                            }
                            if ($this->setting->OperationCard == 2) {
                                $url_to_body = $url . 'paymentin';
                            }
                            if ($this->setting->OperationCard == 0) {
                                continue;
                            }
                        }
                    }

                    $rate_body = $this->msClient->get("https://online.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
                    $rate = null;
                    foreach ($rate_body as $item_rate){
                        if ($item_rate->name == "тенге" or $item_rate->fullName == "Казахстанский тенге"){
                            $rate =
                                ['meta'=> [
                                    'href' => $item_rate->meta->href,
                                    'metadataHref' => $item_rate->meta->metadataHref,
                                    'type' => $item_rate->meta->type,
                                    'mediaType' => $item_rate->meta->mediaType,
                                ],
                                ];
                        }
                    }

                    $body = [
                        'organization' => [  'meta' => [
                            'href' => $this->msOldBodyEntity->organization->meta->href,
                            'type' => $this->msOldBodyEntity->organization->meta->type,
                            'mediaType' => $this->msOldBodyEntity->organization->meta->mediaType,
                        ] ],
                        'agent' => [ 'meta'=> [
                            'href' => $this->msOldBodyEntity->agent->meta->href,
                            'type' => $this->msOldBodyEntity->agent->meta->type,
                            'mediaType' => $this->msOldBodyEntity->agent->meta->mediaType,
                        ] ],
                        'sum' => ($item['total']-$change) * 100,
                        'operations' => [
                            0 => [
                                'meta'=> [
                                    'href' => $this->msOldBodyEntity->meta->href,
                                    'metadataHref' => $this->msOldBodyEntity->meta->metadataHref,
                                    'type' => $this->msOldBodyEntity->meta->type,
                                    'mediaType' => $this->msOldBodyEntity->meta->mediaType,
                                    'uuidHref' => $this->msOldBodyEntity->meta->uuidHref,
                                ],
                                'linkedSum' => 0
                            ], ],
                        'rate' => $rate
                    ];
                    if ($body['rate'] == null) unset($body['rate']);
                    $this->msClient->post($url_to_body, $body);
                }
                break;
            }
            default:{
                break;
            }
        }

    }


    private function items(): ?array
    {
        $positions = null;
        $jsonPositions = $this->msClient->get($this->msOldBodyEntity->positions->meta->href);

        foreach ($jsonPositions->rows as $row) {
            $discount = $row->discount;
            if ($discount > 0){ $discount = round((($row->price/100) * $row->quantity * ($discount/100)), 2); }
            $product = $this->msClient->get($row->assortment->meta->href);

            if (property_exists($row, 'vat') && property_exists($this->msOldBodyEntity, 'vatIncluded') and $row->vatEnabled) { $is_nds = true;
            } else $is_nds = false;

            if (property_exists($row, 'trackingCodes') or isset($item_2->trackingCodes) ){
                foreach ($jsonPositions->trackingCodes as $code){
                    $positions[] = [
                        'name' => (string) $row->name,
                        'price' => (float) $row->price,
                        'quantity' =>  1,
                        'quantity_type' => (int) $this->getUnitCode($product),
                        'total_amount' => (float) ( round($row->price * 1 - $discount, 2) ) ,
                        'is_nds' => $is_nds,
                        'discount' =>(float) $discount,
                        'section' => (int) $this->setting->idDepartment,
                        'mark_code' => (string) $code->cis,
                    ];
                }
            }
            else {
                $positions[] = [
                    'name' => (string) $row->name,
                    'price' => (float) $row->price,
                    'quantity' => (float)  $row->quantity,
                    'quantity_type' => (int) $this->getUnitCode($product),
                    'total_amount' => (float) ( round($row->price * 1 - $discount, 2) ) ,
                    'is_nds' => $is_nds,
                    'discount' =>(float) $discount,
                    'section' => (int) $this->setting->idDepartment,
                ];
            }


        }

        return $positions;
    }
    private function payments(): ?array
    {
        $Bills = $this->msOldBodyEntity->sum / 100;
        $type = $this->getMoneyType($this->settingAutomation->payment);
        if ($type == "") {
            return null;
        }

        $payments[] = [
            'payment_type' => $type,
            'total' => (float) $Bills,
            'amount' => (float) $Bills,
        ];

        return $payments;
    }

    private function operation(): string
    {
        return match ($this->settingAutomation->entity) {
            0, "0", 1, "1"  => 2,
            2, "2" => 3,
            default => "",
        };
    }


    private function getMoneyType($moneyType): string
    {

        switch ($moneyType) {
            case "Наличные":
            case "0" :
                return 0;
            case "Картой":
            case "1" :
                return 1;
            case "Мобильная":
            case "2" :
                return 4;
            case "3" :
            {
                $attributes = null;
                if (property_exists($this->msOldBodyEntity, 'attributes')) {
                    foreach ($this->msOldBodyEntity->attributes as $id => $item) {
                        if ($item->name == 'Тип оплаты (Онлайн ККМ)') $attributes = $id;
                    }
                }

                if ($attributes == null) {
                    $description = 'Сбой автоматизации, проблема в отсутствии типа оплаты.';
                    if (property_exists($this->msOldBodyEntity, 'description')) $description = $description . ' ' . $this->msOldBodyEntity->description;
                    $this->msClient->put($this->msOldBodyEntity->meta->href, ['description' => $description]);
                } else {
                    return $this->getMoneyType($this->msOldBodyEntity->attributes[$attributes]->value->name);
                }

            }
            default:
                return "";
        }
    }


    private function getUnitCode(mixed $product)
    {
        $uomCode = 796;

        if (property_exists($product, 'uom')) {
            $uom = $this->msClient->get($product->uom->meta->href);
            if (isset($uom->code) && isset($uom->name)) {
                $uomCode = $uom->code;
            }
        } else {
            if (property_exists($product, 'characteristics')) {
                $checkUom = $this->msClient->get($product->product->meta->href);
                if (property_exists($checkUom, 'uom')) {
                    $uom = $this->msClient->get($checkUom->uom->meta->href);
                    $uomCode = $uom->code;
                }
            }
        }

        return $uomCode;
    }


    public function writeToAttrib(mixed $createBody, mixed $postTicket)
    {

        $meta1 = $this->getMeta("фискальный номер (ТИС)");
        $meta2 = $this->getMeta("Ссылка для QR-кода (ТИС)");
        $meta3 = $this->getMeta("Фискализация (ТИС)");
        $meta4 = $this->getMeta("ID (ТИС)");

        $body = [
            "attributes" => [
                0 => [
                    "meta" => $meta1,
                    "value" => "" . $postTicket->data->fixed_check,
                ],
                1 => [
                    "meta" => $meta2,
                    "value" => $postTicket->data->link,
                ],
                2 => [
                    "meta" => $meta3,
                    "value" => true,
                ],
                3 => [
                    "meta" => $meta4,
                    "value" => (string) $postTicket->data->id,
                ],
            ],
        ];

        return $this->msClient->put($this->msOldBodyEntity->meta->href, $body);
    }


    private function getMeta($attribName): array
    {
        switch ($this->settingAutomation->entity){
            case '0': { $uri = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes"; break;}
            case '1': { $uri = "https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes"; break;}
            case '2': { $uri = "https://online.moysklad.ru/api/remap/1.2/entity/salesreturn/metadata/attributes"; break;}
            default: { $uri = ""; break;}
        }

        $json = $this->msClient->get($uri);
        foreach($json->rows as $row){
            if($row->name == $attribName){
                return [
                    'href' => $row->meta->href,
                    'type' => $row->meta->type,
                    'mediaType' => $row->meta->mediaType,
                ];
            }
        }
        return [];
    }
}
