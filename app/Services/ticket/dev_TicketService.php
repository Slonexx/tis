<?php

namespace App\Services\ticket;

use App\Clients\KassClient;
use App\Clients\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\globalObjectController;
use App\Models\htmlResponce;
use App\Services\AdditionalServices\DocumentService;
use App\Services\MetaServices\MetaHook\AttributeHook;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

class dev_TicketService
{

    private AttributeHook $attributeHook;
    private DocumentService $documentService;

    /**
     * @param AttributeHook $attributeHook
     * @param DocumentService $documentService
     */
    public function __construct(AttributeHook $attributeHook, DocumentService $documentService)
    {
        $this->attributeHook = $attributeHook;
        $this->documentService = $documentService;
    }

    // Create ticket

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createTicket($data) {
        $accountId = $data['accountId'];
        $id_entity = $data['id_entity'];
        $entity_type = $data['entity_type'];

        $money_card = $data['money_card'];
        $money_cash = $data['money_cash'];
        $payType = $data['pay_type'];
        $total = $data['total'];

        $positions = $data['positions'];

        $Setting = new getMainSettingBD($accountId);

        $ClientTIS = new KassClient($Setting->authtoken);
        $Client = new MsClient($Setting->tokenMs);
        $Config = new globalObjectController();
        $oldBody =  $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/'.$id_entity);

        $Body = $this->setBodyToPostClient($Setting, $id_entity, $entity_type, $money_card, $money_cash, $payType, $total, $positions);

        if (isset($Body['Status'])) {
            return response()->json($Body['Message']);
        }

        try {
            //dd($Body);
            $postTicket = $ClientTIS->POSTClient($Config->apiURL_ukassa.'v2/operation/ticket/', $Body);

            $putBody = $this->putBodyMS($entity_type, $postTicket, $Client, $Setting, $oldBody, $positions);
            if ($putBody != []) {
                $put = $Client->put('https://online.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/'.$id_entity, $putBody);


                //dd($putBody);
                if ($payType == 'return'){$this->createReturnDocument($Setting, $put, $postTicket, $putBody, $entity_type); }

                if ($Setting->paymentDocument != null ){
                    $this->createPaymentDocument($Setting, $put);
                }
            }

            htmlResponce::create([
                'accountId' => $accountId,
                'html' => $postTicket->data->html,
            ]);

            return response()->json([
                'status'    => 'Ticket created',
                'code'      => 200,
                'postTicket' => $postTicket,
            ]);

        } catch (BadResponseException  $e){
            return $e->getResponse()->getBody()->getContents();
            return response()->json([
                'status'    => 'error',
                'code'      => $e->getCode(),
                'errors'    => json_decode($e->getResponse()->getBody()->getContents(), true)
            ]);
        }
        //$postTicket = null;



    }


    private function setBodyToPostClient(getMainSettingBD $Setting, mixed $id_entity, mixed $entity_type, mixed $money_card, mixed $money_cash, mixed $payType, mixed $total, mixed $positions): array
    {

        $operation = $this->getOperation($payType);
        $payments = $this->getPayments($money_card, $money_cash, $total);
        $items = $this->getItems($Setting, $positions, $id_entity, $entity_type);
        $customer = $this->getCustomer($Setting, $id_entity, $entity_type);

        if ($operation == '') return ['Status' => false, 'Message' => 'не выбран тип продажи'];
        if ($Setting->idKassa == null) return ['Status' => false, 'Message' => 'Не были пройдены настройки !'];
        if ($payments == null) return ['Status' => false, 'Message' => 'Не были введены суммы !'];


        return [
            'operation' => (int) $operation,
            'kassa' => (int) $Setting->idKassa,
            'payments' => $payments,
            'items' => $items,
            "total_amount" => (float) $total,
            "customer" => $customer,
            "as_html" => true,
        ];
    }


    private function getOperation($payType): int|string
    {
        return match ($payType) {
            "sell" => 2,
            "return" => 3,
            default => "",
        };
    }

    private function getPayments($card, $cash, $total): array
    {
        //dd($card, $cash, $total);

        $result = null;
        if ( $cash > 0 ) {
            $change = $total - $cash - $card;
            if ($change < 0) $change = $change * (-1);

            $result[] = [
                'payment_type' => 0,
                'total' => (float) $cash,
                'change' => (float) $change,
                'amount' => (float) $cash,
            ];
            if ($result[0]['change'] == 0){
                unset($result[0]['change']);
            }
            //dd($result);
        }
        if ( $card > 0 ) {

            $result[] = [
                'payment_type' => 1,
                'total' => (float) $card,
                'amount' => (float) $card,
            ];
        }

        return $result;
    }

    private function getItems(getMainSettingBD $Setting, $positions, $idObject, $typeObject): array
    {
        $result = null;
        if ($typeObject == 'demand'){
            $Client = new MsClient($Setting->tokenMs);
            $demand = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/' . $typeObject . '/' . $idObject);
            $demandPos = $Client->get($demand->positions->meta->href)->rows;

            foreach ($positions as $id => $item){
                $is_nds = trim($item->is_nds, '%');
                $discount = trim($item->discount, '%');
                if ($is_nds == 'без НДС' or $is_nds == "0%"){$is_nds = false;
                } else $is_nds = true;

                if ($discount > 0){
                    $discount = round(($item->price * $item->quantity * ($discount/100)), 2);
                }

                $result[] = [
                    'name' => (string) $item->name,
                    'price' => (float) $item->price,
                    'quantity' => (float) $item->quantity,
                    'quantity_type' => (int) $item->UOM,
                    'total_amount' => (float) ( round($item->price * $item->quantity - $discount, 2) ) ,
                    'is_nds' => $is_nds,
                    'discount' =>(float) $discount,
                    'section' => (int) $Setting->idDepartment,
                ];


                    foreach ($demandPos as $item_2){
                        if ($item->id == $item_2->id){
                            if (isset($item_2->trackingCodes)){
                                array_pop($result);
                                foreach ($item_2->trackingCodes as $code){
                                    $result[] = [
                                        'name' => (string) $item['name'],
                                        'price' => (float) $item['price'],
                                        'quantity' => 1,
                                        'quantity_type' => 796,
                                        'total_amount' => (float) ($item->price * 1),
                                        'is_nds' => $is_nds,
                                        'discount' =>(float) $discount,
                                        'mark_code' =>(string) $code->cis,
                                        'section' => (int) $Setting->idDepartment,
                                    ];
                                }
                            }
                        }
                    }

            }

        } else {
            foreach ($positions as $id => $item){
                $is_nds = trim($item->is_nds, '%');
                $discount = trim($item->discount, '%');
                if ($is_nds == 'без НДС' or $is_nds == "0%"){$is_nds = false;
                } else $is_nds = true;

                if ($discount > 0){
                    $discount = round(($item->price * $item->quantity * ($discount/100)), 2);
                }

                $result[$id] = [
                    'name' => (string) $item->name,
                    'price' => (float) $item->price,
                    'quantity' => (float) $item->quantity,
                    'quantity_type' => (int) $item->UOM,
                    'total_amount' => (float) ( round($item->price * $item->quantity - $discount,2) ) ,
                    'is_nds' => $is_nds,
                    'discount' =>(float) $discount,
                    'section' => (int) $Setting->idDepartment,
                ];

            }
        }


        foreach ($result as $id => $item){
            if ($item['discount']<= 0) {
                unset($result[$id]['discount']);
            }
        }

        return $result;
    }

    private function getCustomer($Setting, $id_entity, $entity_type): array
    {
        $Client = new MsClient($Setting->tokenMs);
        $body = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/'.$id_entity);
        $agent = $Client->get($body->agent->meta->href);
        $result = [];

        if (property_exists($agent, 'email')) { $result['email'] = $agent->email; }
        if (property_exists($agent, 'phone')) { $result['phone'] = $agent->phone; }
        if (property_exists($agent, 'inn')) { $result['iin'] = $agent->inn; }

        return $result;

    }

    private function putBodyMS($entity_type, mixed $postTicket, MsClient $Client, getMainSettingBD $Setting, mixed $oldBody, mixed $positionsBody): array
    {   $result = null;
        $Result_attributes = null;
        $Resul_positions = null;
        $check_attributes_in_value_name = false;
        if (property_exists($oldBody, 'attributes'))  {
            foreach ($oldBody->attributes as $item){
                if ($item->name == 'Фискальный номер (ТИС)' and $item->name != ''){
                    $check_attributes_in_value_name = false;
                    break;
                } else $check_attributes_in_value_name = true;
            }

            $attributes = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/metadata/attributes/')->rows;
            $Result_attributes = $this->setAttributesToPutBody($postTicket, $check_attributes_in_value_name, $attributes);

            $positions = $Client->get($oldBody->positions->meta->href)->rows;
            $Resul_positions = $this->setPositionsToPutBody($postTicket, $positions, $positionsBody);




            if ($Result_attributes != null){
                $result['attributes'] = $Result_attributes;
            }
            if ($Resul_positions != null){
                $result['positions'] = $Resul_positions;
            }
            return $result;
        }
        else return [];
    }

    private function setAttributesToPutBody(mixed $postTicket, bool $check_attributes, $attributes): array
    {
        $Result_attributes = null;
        foreach ($attributes as $item) {
            if ($item->name == "фискальный номер (ТИС)" and $check_attributes == true) {
                $Result_attributes[] = [
                    "meta"=> [
                        "href"=> $item->meta->href,
                        "type"=> $item->meta->type,
                        "mediaType"=> $item->meta->mediaType,
                    ],
                    "value" => $postTicket->data->fixed_check,
                ];
            }
            if ($item->name == "Ссылка для QR-кода (ТИС)" ) {
                $Result_attributes[] = [
                    "meta"=> [
                        "href"=> $item->meta->href,
                        "type"=> $item->meta->type,
                        "mediaType"=> $item->meta->mediaType,
                    ],
                    "value" => $postTicket->data->link,
                ];
            }
            if ($item->name == "Фискализация (ТИС)" ) {
                $Result_attributes[] = [
                    "meta"=> [
                        "href"=> $item->meta->href,
                        "type"=> $item->meta->type,
                        "mediaType"=> $item->meta->mediaType,
                    ],
                    "value" => true,
                ];
            }
            if ($item->name == "ID (ТИМ)" ) {
                $Result_attributes[] = [
                    "meta"=> [
                        "href"=> $item->meta->href,
                        "type"=> $item->meta->type,
                        "mediaType"=> $item->meta->mediaType,
                    ],
                    "value" => (string) $postTicket->data->id,
                ];
            }
        }
        return $Result_attributes;
    }

    private function setPositionsToPutBody(mixed $postTicket, mixed $positions, mixed $positionsBody): array
    {   $result = null;
        $sort = null;
        foreach ($positionsBody as $id=>$one){
            foreach ($positions as $item_p){
                if ($item_p->id == $one->id){
                    $sort[$id] = $item_p;
                }
            }
        }
        foreach ($positionsBody as $id=>$item){
            $result[$id] = [
                "id" => $item->id,
                "quantity" => (int) $item->quantity,
                "price" => (float) $item->price * 100,
                "discount" => (int) $item->discount,
                "vat" => (int) $item->is_nds,
                "assortment" => ['meta'=>[
                    "href" => $sort[$id]->assortment->meta->href,
                    "type" => $sort[$id]->assortment->meta->type,
                    "mediaType" => $sort[$id]->assortment->meta->mediaType,
                ]],
            ];
        }
        return $result;

    }

    private function createPaymentDocument(getMainSettingBD $Setting, mixed $OldBody)
    {   $client = new MsClient($Setting->tokenMs);
        if ($Setting->paymentDocument == 1){
        $url = 'https://online.moysklad.ru/api/remap/1.2/entity/cashin';
        $body = [
            'organization' => [  'meta' => [
                'href' => $OldBody->organization->meta->href,
                'type' => $OldBody->organization->meta->type,
                'mediaType' => $OldBody->organization->meta->mediaType,
            ] ],
            'agent' => [ 'meta'=> [
                'href' => $OldBody->agent->meta->href,
                'type' => $OldBody->agent->meta->type,
                'mediaType' => $OldBody->agent->meta->mediaType,
            ] ],
            'sum' => $OldBody->sum,
            'operations' => [
                0 => [
                    'meta'=> [
                        'href' => $OldBody->meta->href,
                        'metadataHref' => $OldBody->meta->metadataHref,
                        'type' => $OldBody->meta->type,
                        'mediaType' => $OldBody->meta->mediaType,
                        'uuidHref' => $OldBody->meta->uuidHref,
                    ],
                    'linkedSum' => 0
                ], ]
        ];
        $postBodyCreateCashin = $client->post($url, $body);
        }
        if ($Setting->paymentDocument == 2){
            $url = 'https://online.moysklad.ru/api/remap/1.2/entity/paymentin';

            $body = [
                'organization' => [  'meta' => [
                    'href' => $OldBody->organization->meta->href,
                    'type' => $OldBody->organization->meta->type,
                    'mediaType' => $OldBody->organization->meta->mediaType,
                ] ],
                'agent' => [ 'meta'=> [
                    'href' => $OldBody->agent->meta->href,
                    'type' => $OldBody->agent->meta->type,
                    'mediaType' => $OldBody->agent->meta->mediaType,
                ] ],
                'sum' => $OldBody->sum,
                'operations' => [
                    0 => [
                        'meta'=> [
                            'href' => $OldBody->meta->href,
                            'metadataHref' => $OldBody->meta->metadataHref,
                            'type' => $OldBody->meta->type,
                            'mediaType' => $OldBody->meta->mediaType,
                            'uuidHref' => $OldBody->meta->uuidHref,
                        ],
                        'linkedSum' => 0
                    ], ]
            ];
            $postBodyCreatePaymentin = $client->post($url, $body);
        }

    }

    private function createReturnDocument(getMainSettingBD $Setting, mixed $newBody, mixed $putBody, mixed $oldBody, mixed $entity_type)
    {
        if ($entity_type != 'salesreturn') {
            $client = new MsClient($Setting->tokenMs);

            $attributes_item = $client->get('https://online.moysklad.ru/api/remap/1.2/entity/salesreturn/metadata/attributes/')->rows;
            $attributes = null;
            $positions = null;
            foreach ($attributes_item as $item){
                if ($item->name == 'фискальный номер (ТИС)'){
                    $attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => $putBody->data->fixed_check,
                    ];
                }
                if ($item->name == 'Ссылка для QR-кода (ТИС)'){
                    $attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => $putBody->data->link,
                    ];
                }
                if ($item->name == 'Фискализация (ТИС)'){
                    $attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => true,
                    ];
                }

            }

            foreach ($oldBody['positions'] as $item) {
                unset($item['id']);
                $positions[] = $item;
            }

            $url = 'https://online.moysklad.ru/api/remap/1.2/entity/salesreturn';

            $body = [
                'organization' => [
                    'meta' => [
                        'href' => $newBody->organization->meta->href,
                        'metadataHref' => $newBody->organization->meta->metadataHref,
                        'type' => $newBody->organization->meta->type,
                        'mediaType' => $newBody->organization->meta->mediaType,
                    ]
                ],
                'agent' =>[
                    'meta' => [
                        'href' => $newBody->agent->meta->href,
                        'metadataHref' => $newBody->agent->meta->metadataHref,
                        'type' => $newBody->agent->meta->type,
                        'mediaType' => $newBody->agent->meta->mediaType,
                    ]
                ],
                'attributes' => $attributes,
                'positions' => $positions,
                'description' => 'Созданный документ возврата с ',
                'organizationAccount' => null,
                'demand' => null,
                'store' => null,
            ];

            if (isset($newBody->organizationAccount)){
                $body['organizationAccount'] = [
                    'meta' => [
                        'href' => $newBody->organizationAccount->meta->href,
                        'type' => $newBody->organizationAccount->meta->type,
                        'mediaType' => $newBody->organizationAccount->meta->mediaType,
                    ]
                ];
            } else { unlink($body['organizationAccount']); }

            if (isset($newBody->store)){
                $body['store'] = [
                    'meta' => [
                        'href' => $newBody->store->meta->href,
                        'metadataHref' => $newBody->store->meta->metadataHref,
                        'type' => $newBody->store->meta->type,
                        'mediaType' => $newBody->store->meta->mediaType,
                    ]
                ];
            } else { $store = $client->get('https://online.moysklad.ru/api/remap/1.2/entity/store')->rows[0];
                $body['store'] = [
                    'meta' => [
                        'href' => $store->meta->href,
                        'metadataHref' => $store->meta->metadataHref,
                        'type' => $store->meta->type,
                        'mediaType' => $store->meta->mediaType,
                    ]
                ];
            }




            if ($entity_type == 'customerorder'){
                $body['description'] = $body['description'].'заказа покупателя, его номер:'. $newBody->name;
                unset($body['demand']);
            }

            if ($entity_type == 'demand'){
                $body['description'] = $body['description'].'отгрузка, его номер:'. $newBody->name;
                $body['demand'] = [
                    'meta' => [
                        'href' => $newBody->meta->href,
                        'metadataHref' => $newBody->meta->metadataHref,
                        'type' => $newBody->meta->type,
                        'mediaType' => $newBody->meta->mediaType,
                    ]
                ];
            }
            try {
                $post = $client->post($url, $body);
            } catch (BadResponseException $exception){

            }
        }


    }


}
