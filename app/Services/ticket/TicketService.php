<?php

namespace App\Services\ticket;

use App\Clients\KassClient;
use App\Clients\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\globalObjectController;
use App\Models\htmlResponce;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;

class TicketService
{

    private getMainSettingBD $Setting;
    private MsClient $msClient;
    private string $accountId;


    /**
     * @throws GuzzleException
     */
    public function createTicket($data): JsonResponse
    {
        $this->accountId =  $data['accountId'];

        $accountId = $data['accountId'];
        $id_entity = $data['id_entity'];
        $entity_type = $data['entity_type'];

        $money_card = $data['money_card'];
        $money_cash = $data['money_cash'];
        $payType = $data['pay_type'];
        $total = $data['total'];

        $positions = $data['positions'];

        $Setting = new getMainSettingBD($accountId);
        $this->Setting = new getMainSettingBD($accountId);

        $ClientTIS = new KassClient($Setting->authtoken);
        $Client = new MsClient($Setting->tokenMs);
        $this->msClient = new MsClient($Setting->tokenMs);

        if ($accountId == '1dd5bd55-d141-11ec-0a80-055600047495') $Config = new globalObjectController(false);
        else $Config = new globalObjectController();
        $oldBody = $Client->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/' . $id_entity);

        $Body = $this->setBodyToPostClient($Setting, $id_entity, $entity_type, $money_card, $money_cash, $payType, $total, $positions);

        if (isset($Body['Status'])) {
            return response()->json($Body['Message']);
        }

        try {
            $postTicket = $ClientTIS->POSTClient($Config->apiURL_ukassa . 'v2/operation/ticket/', $Body);

            $putBody = $this->putBodyMS($entity_type, $postTicket, $Client, $oldBody, $positions);
            $put = $Client->put('https://api.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/' . $id_entity, $putBody);

            if ($Setting->accountId != "f0eb536d-d41f-11e6-7a69-971100005224") {
                if ($payType == 'return') {
                    $this->createReturnDocument($Setting, $put, $postTicket, $putBody, $entity_type);
                    $put = $Client->put('https://api.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/' . $id_entity, [
                        'description' => $this->descriptionToCreate($oldBody, $postTicket, 'Возврат, фискальный номер: '),
                    ]);
                }
            }


            if ($Setting->paymentDocument != null) {
                $this->createPaymentDocument($Setting, $Client, $entity_type, $put, $Body['payments']);
            }

            htmlResponce::create([
                'accountId' => $accountId,
                'html' => $postTicket->data->html,
            ]);

            return response()->json([
                'status' => 'Ticket created',
                'code' => 200,
                'postTicket' => $postTicket,
            ]);

        } catch (BadResponseException  $e) {
            return response()->json([
                'status' => 'error',
                'code' => $e->getCode(),
                'errors' => json_decode($e->getResponse()->getBody()->getContents(), true),
                'errors_' => $e->getMessage(),
                'Body' => $Body,
                'JSON_Body' => json_encode($Body)
            ]);
        }

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


        $result = [
            'operation' => (int)$operation,
            'kassa' => (int)$Setting->idKassa,
            'payments' => $payments,
            'items' => $items,
            "total_amount" => (float)$total,
            "customer" => $customer,
            'need_mark_code' => true,
            "as_html" => true,
        ];

        if ($result['customer'] == []) {
            unset($result['customer']);
        }

        return $result;
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
        if ($cash > 0) {
            $change = number_format($total - $cash - $card, 2, '.', '');
            if ($change < 0) $change = $change * (-1);

            $result[] = [
                'payment_type' => 0,
                'total' => (float)$cash,
                'change' => (float)$change,
                'amount' => (float)$cash,
            ];
            if ($result[0]['change'] == 0) {
                unset($result[0]['change']);
            }
            //dd($result);
        }
        if ($card > 0) {

            $result[] = [
                'payment_type' => 1,
                'total' => (float)$card,
                'amount' => (float)$card,
            ];
        }

        return $result;
    }

    private function getItems(getMainSettingBD $Setting, $positions, $idObject, $typeObject): array
    {
        $msClient = new MsClient($Setting->tokenMs);
        $result = null;

        if ($typeObject == 'demand') {
            $demandPos = $msClient->get(($msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $typeObject . '/' . $idObject))->positions->meta->href)->rows;
        }


        foreach ($positions as $id => $item) {
            $is_nds = trim($item->is_nds, '%');
            $discount = trim($item->discount, '%');
            if ($is_nds == 'без НДС' or $is_nds == "0%") { $is_nds = false; } else $is_nds = true;
            if ($discount > 0) { $discount = round(($item->price * $item->quantity * ($discount / 100)), 2); }

            if ($typeObject == 'demand'){

                if (isset($demandPos[$id]->trackingCodes)){
                    foreach ($demandPos[$id]->trackingCodes as $code){ $result[] = $this->itemPosition($item->name, $item->price, 1, $discount, $item->UOM, $is_nds, $Setting->idDepartment, $code->cis) ; }
                } else { $result[] = $this->itemPosition($item->name, $item->price, $item->quantity, $discount, $item->UOM, $is_nds, $Setting->idDepartment, '') ; }

            } else { $result[] = $this->itemPosition($item->name, $item->price, $item->quantity, $discount, $item->UOM, $is_nds, $Setting->idDepartment, '') ; }
        }

        return $result;
    }

    private function getCustomer($Setting, $id_entity, $entity_type): array
    {
        $Client = new MsClient($Setting->tokenMs);
        $body = $Client->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/' . $id_entity);
        $agent = $Client->get($body->agent->meta->href);
        $result = [];

        if (property_exists($agent, 'email')) {
            if (strpos($agent->email, '@') !== false) {
                $result['email'] = $agent->email;
            }
        }
        if (property_exists($agent, 'phone')) {
            $phone = "7" . mb_substr(str_replace('+7', '',
                    str_replace(" ", '',
                        str_replace('(', '',
                            str_replace(')', '',
                                str_replace('-', '', $agent->phone))))), -10);
            $result['phone'] = $phone;
        }
        if (property_exists($agent, 'inn')) {
            $result['iin'] = $agent->inn;
        }

        return $result;

    }

    private function putBodyMS($entity_type, mixed $postTicket, MsClient $Client, mixed $oldBody, mixed $positionsBody): array
    {
        $result = null;
        $check_attributes_in_value_name = false;
        foreach ($oldBody->attributes as $item) {
            if ($item->name == 'Фискальный номер (ТИС)' and $item->name != '') {
                $check_attributes_in_value_name = false;
                break;
            } else $check_attributes_in_value_name = true;
        }

        $attributes = $Client->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/metadata/attributes/')->rows;
        $Result_attributes = $this->setAttributesToPutBody($postTicket, $check_attributes_in_value_name, $attributes);

        $positions = $Client->get($oldBody->positions->meta->href)->rows;
        $Resul_positions = $this->setPositionsToPutBody($positions, $positionsBody);

        $result['description'] = $this->descriptionToCreate($oldBody, $postTicket, 'Продажа, Фискальный номер: ');

        if ($Result_attributes != null) {
            $result['attributes'] = $Result_attributes;
        }
        if ($Resul_positions != null) {
            $result['positions'] = $Resul_positions;
        }
        return $result;
    }

    private function setAttributesToPutBody(mixed $postTicket, bool $check_attributes, $attributes): array
    {
        $time = $check_attributes;
        $att = null;
        foreach ($attributes as $row) {
            $name = $row->name; // Получаем имя объекта из второго массива

            if ($name == "фискальный номер (ТИС)") $att[] = ['meta'=>$row->meta, 'value' => "" . $postTicket->data->fixed_check];

            if ($this->accountId == '975f9e6b-5d22-11ee-0a80-01fa00004c07') {
                if ($name == "Ссылка для QR-кода (ТИС)") $att[] = ['meta'=>$row->meta, 'value' => $postTicket->data->link];
            } else {
                if ($name == "Ссылка на чек") $att[] = ['meta'=>$row->meta, 'value' => $postTicket->data->link];
            }


            if ($name == "Фискализация (ТИС)") $att[] = ['meta'=>$row->meta, 'value' => true];
            if ($name == "ID (ТИС)") $att[] = ['meta'=>$row->meta, 'value' => "".$postTicket->data->id];
            if ($name == "Тип Оплаты (ТИС)") $att[] = ['meta'=>$row->meta, 'value' => $this->sumAmountMeta($postTicket)];
            if ($name == "Тип оплаты (Онлайн ККМ)") {
                $att[] = ['meta'=>$row->meta, 'value' => $this->typePaymentMC($postTicket, $row)];
            }
        }
        return $att;
    }

    private function setPositionsToPutBody(mixed $positions, mixed $positionsBody): array
    {
        $result = null;
        $sort = null;
        foreach ($positionsBody as $id => $one) {
            foreach ($positions as $item_p) {
                if ($item_p->id == $one->id) {
                    $sort[$id] = $item_p;
                }
            }
        }
        foreach ($positionsBody as $id => $item) {
            $result[$id] = [
                "id" => $item->id,
                "quantity" => (int)$item->quantity,
                "price" => (float)$item->price * 100,
                "discount" => (int)$item->discount,
                "vat" => (int)$item->is_nds,
                "assortment" => ['meta' => [
                    "href" => $sort[$id]->assortment->meta->href,
                    "type" => $sort[$id]->assortment->meta->type,
                    "mediaType" => $sort[$id]->assortment->meta->mediaType,
                ]],
            ];
        }
        return $result;

    }

    private function createPaymentDocument(getMainSettingBD $Setting, MsClient $client, string $entity_type, mixed $OldBody, mixed $payments): void
    {
        switch ($Setting->paymentDocument) {
            case "1":
            {
                $url = 'https://api.moysklad.ru/api/remap/1.2/entity/';
                if ($entity_type != 'salesreturn') {
                    $url = $url . 'cashin';
                } else {
                    //$url = $url . 'cashout';
                    break;
                }
                $body = [
                    'organization' => ['meta' => [
                        'href' => $OldBody->organization->meta->href,
                        'type' => $OldBody->organization->meta->type,
                        'mediaType' => $OldBody->organization->meta->mediaType,
                    ]],
                    'agent' => ['meta' => [
                        'href' => $OldBody->agent->meta->href,
                        'type' => $OldBody->agent->meta->type,
                        'mediaType' => $OldBody->agent->meta->mediaType,
                    ]],
                    'sum' => $OldBody->sum,
                    'operations' => [
                        0 => [
                            'meta' => [
                                'href' => $OldBody->meta->href,
                                'metadataHref' => $OldBody->meta->metadataHref,
                                'type' => $OldBody->meta->type,
                                'mediaType' => $OldBody->meta->mediaType,
                                'uuidHref' => $OldBody->meta->uuidHref,
                            ],
                            'linkedSum' => $OldBody->sum
                        ],]
                ];
                $client->post($url, $body);
                break;
            }
            case "2":
            {
                $url = 'https://api.moysklad.ru/api/remap/1.2/entity/';
                if ($entity_type != 'salesreturn') {
                    $url = $url . 'paymentin';
                } else {
                    //$url = $url . 'paymentout';
                    break;
                }

                $rate_body = $client->get("https://api.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
                $rate = null;
                foreach ($rate_body as $item) {
                    if ($item->name == "тенге" or $item->fullName == "Казахстанский тенге") {
                        $rate =
                            ['meta' => [
                                'href' => $item->meta->href,
                                'metadataHref' => $item->meta->metadataHref,
                                'type' => $item->meta->type,
                                'mediaType' => $item->meta->mediaType,
                            ],
                            ];
                    }
                }

                $body = [
                    'organization' => ['meta' => [
                        'href' => $OldBody->organization->meta->href,
                        'type' => $OldBody->organization->meta->type,
                        'mediaType' => $OldBody->organization->meta->mediaType,
                    ]],
                    'agent' => ['meta' => [
                        'href' => $OldBody->agent->meta->href,
                        'type' => $OldBody->agent->meta->type,
                        'mediaType' => $OldBody->agent->meta->mediaType,
                    ]],
                    'sum' => $OldBody->sum,
                    'operations' => [
                        0 => [
                            'meta' => [
                                'href' => $OldBody->meta->href,
                                'metadataHref' => $OldBody->meta->metadataHref,
                                'type' => $OldBody->meta->type,
                                'mediaType' => $OldBody->meta->mediaType,
                                'uuidHref' => $OldBody->meta->uuidHref,
                            ],
                            'linkedSum' => $OldBody->sum
                        ],],
                    'rate' => $rate
                ];
                if ($body['rate'] == null) unset($body['rate']);
                $client->post($url, $body);
                break;
            }
            case "3":
            {
                $url = 'https://api.moysklad.ru/api/remap/1.2/entity/';
                foreach ($payments as $item) {
                    $change = 0;
                    if ($item['payment_type'] == 0) {
                        if ($entity_type != 'salesreturn') {
                            $url_to_body = $url . 'cashin';
                        } else {
                            //$url_to_body = $url . 'cashout';
                            break;
                        }
                        if (isset($item['change'])) $change = $item['change'];
                    } else {
                        if ($entity_type != 'salesreturn') {
                            $url_to_body = $url . 'paymentin';
                        } else {
                            //$url_to_body = $url . 'paymentout';
                            break;
                        }
                    }

                    $rate_body = $client->get("https://api.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
                    $rate = null;
                    foreach ($rate_body as $item_rate) {
                        if ($item_rate->name == "тенге" or $item_rate->fullName == "Казахстанский тенге") {
                            $rate =
                                ['meta' => [
                                    'href' => $item_rate->meta->href,
                                    'metadataHref' => $item_rate->meta->metadataHref,
                                    'type' => $item_rate->meta->type,
                                    'mediaType' => $item_rate->meta->mediaType,
                                ],
                                ];
                        }
                    }

                    $body = [
                        'organization' => ['meta' => [
                            'href' => $OldBody->organization->meta->href,
                            'type' => $OldBody->organization->meta->type,
                            'mediaType' => $OldBody->organization->meta->mediaType,
                        ]],
                        'agent' => ['meta' => [
                            'href' => $OldBody->agent->meta->href,
                            'type' => $OldBody->agent->meta->type,
                            'mediaType' => $OldBody->agent->meta->mediaType,
                        ]],
                        'sum' => ($item['total'] - $change) * 100,
                        'operations' => [
                            0 => [
                                'meta' => [
                                    'href' => $OldBody->meta->href,
                                    'metadataHref' => $OldBody->meta->metadataHref,
                                    'type' => $OldBody->meta->type,
                                    'mediaType' => $OldBody->meta->mediaType,
                                    'uuidHref' => $OldBody->meta->uuidHref,
                                ],
                                'linkedSum' => ($item['total'] - $change) * 100
                            ],],
                        'rate' => $rate
                    ];
                    if ($body['rate'] == null) unset($body['rate']);
                    $client->post($url_to_body, $body);
                }
                break;
            }
            case "4":
            {
                $url = 'https://api.moysklad.ru/api/remap/1.2/entity/';
                $url_to_body = null;
                foreach ($payments as $item) {
                    $change = 0;
                    if ($item['payment_type'] == 0) {
                        if ($entity_type != 'salesreturn') {
                            if ($Setting->OperationCash == 1) {
                                $url_to_body = $url . 'cashin';
                            }
                            if ($Setting->OperationCash == 2) {
                                $url_to_body = $url . 'paymentin';
                            }
                            if ($Setting->OperationCash == 0) {
                                continue;
                            }
                        }
                        if (isset($item['change'])) $change = $item['change'];
                    } else {
                        if ($entity_type != 'salesreturn') {
                            if ($Setting->OperationCard == 1) {
                                $url_to_body = $url . 'cashin';
                            }
                            if ($Setting->OperationCard == 2) {
                                $url_to_body = $url . 'paymentin';
                            }
                            if ($Setting->OperationCard == 0) {
                                continue;
                            }
                        }
                    }

                    $rate_body = $client->get("https://api.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
                    $rate = null;
                    foreach ($rate_body as $item_rate) {
                        if ($item_rate->name == "тенге" or $item_rate->fullName == "Казахстанский тенге") {
                            $rate =
                                ['meta' => [
                                    'href' => $item_rate->meta->href,
                                    'metadataHref' => $item_rate->meta->metadataHref,
                                    'type' => $item_rate->meta->type,
                                    'mediaType' => $item_rate->meta->mediaType,
                                ],
                                ];
                        }
                    }

                    $body = [
                        'organization' => ['meta' => [
                            'href' => $OldBody->organization->meta->href,
                            'type' => $OldBody->organization->meta->type,
                            'mediaType' => $OldBody->organization->meta->mediaType,
                        ]],
                        'agent' => ['meta' => [
                            'href' => $OldBody->agent->meta->href,
                            'type' => $OldBody->agent->meta->type,
                            'mediaType' => $OldBody->agent->meta->mediaType,
                        ]],
                        'sum' => ($item['total'] - $change) * 100,
                        'operations' => [
                            0 => [
                                'meta' => [
                                    'href' => $OldBody->meta->href,
                                    'metadataHref' => $OldBody->meta->metadataHref,
                                    'type' => $OldBody->meta->type,
                                    'mediaType' => $OldBody->meta->mediaType,
                                    'uuidHref' => $OldBody->meta->uuidHref,
                                ],
                                'linkedSum' => ($item['total'] - $change) * 100,
                            ],],
                        'rate' => $rate
                    ];
                    if ($body['rate'] == null) unset($body['rate']);
                    $client->post($url_to_body, $body);
                }
                break;
            }
            default:
            {
                break;
            }
        }

    }

    private function createReturnDocument(getMainSettingBD $Setting, mixed $newBody, mixed $putBody, mixed $oldBody, mixed $entity_type): void
    {
        if ($entity_type != 'salesreturn') {
            $client = new MsClient($Setting->tokenMs);

            $attributes_item = $client->get('https://api.moysklad.ru/api/remap/1.2/entity/salesreturn/metadata/attributes/')->rows;
            $attributes = null;
            $positions = null;
            foreach ($attributes_item as $item) {
                if ($item->name == 'фискальный номер (ТИС)') {
                    $attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => $putBody->data->fixed_check,
                    ];
                }

                if ($this->accountId == '975f9e6b-5d22-11ee-0a80-01fa00004c07') {
                    if ($item->name == 'Ссылка на чек') {
                        $attributes[] = [
                            'meta' => [
                                'href' => $item->meta->href,
                                'type' => $item->meta->type,
                                'mediaType' => $item->meta->mediaType,
                            ],
                            'value' => $putBody->data->link,
                        ];
                    }
                } else {
                    if ($item->name == 'Ссылка для QR-кода (ТИС)') {
                        $attributes[] = [
                            'meta' => [
                                'href' => $item->meta->href,
                                'type' => $item->meta->type,
                                'mediaType' => $item->meta->mediaType,
                            ],
                            'value' => $putBody->data->link,
                        ];
                    }
                }


                if ($item->name == 'Фискализация (ТИС)') {
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

            $url = 'https://api.moysklad.ru/api/remap/1.2/entity/salesreturn';

            $body = [
                'organization' => [
                    'meta' => [
                        'href' => $newBody->organization->meta->href,
                        'metadataHref' => $newBody->organization->meta->metadataHref,
                        'type' => $newBody->organization->meta->type,
                        'mediaType' => $newBody->organization->meta->mediaType,
                    ]
                ],
                'agent' => [
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
            ];

            if (isset($newBody->organizationAccount)) {
                $body['organizationAccount'] = [
                    'meta' => [
                        'href' => $newBody->organizationAccount->meta->href,
                        'type' => $newBody->organizationAccount->meta->type,
                        'mediaType' => $newBody->organizationAccount->meta->mediaType,
                    ]
                ];
            } else {
                unset($body['organizationAccount']);
            }

            if (isset($newBody->store)) {
                $body['store'] = [
                    'meta' => [
                        'href' => $newBody->store->meta->href,
                        'metadataHref' => $newBody->store->meta->metadataHref,
                        'type' => $newBody->store->meta->type,
                        'mediaType' => $newBody->store->meta->mediaType,
                    ]
                ];
            } else {
                $store = $client->get('https://api.moysklad.ru/api/remap/1.2/entity/store')->rows[0];
                $body['store'] = [
                    'meta' => [
                        'href' => $store->meta->href,
                        'metadataHref' => $store->meta->metadataHref,
                        'type' => $store->meta->type,
                        'mediaType' => $store->meta->mediaType,
                    ]
                ];
            }


            if ($entity_type == 'customerorder') {
                $body['description'] = $body['description'] . 'заказа покупателя, его номер:' . $newBody->name;
                unset($body['demand']);
            }

            if ($entity_type == 'demand') {
                $body['description'] = $body['description'] . 'отгрузка, его номер:' . $newBody->name;
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
                $client->post($url, $body);
            } catch (BadResponseException) {

            }
        }


    }

    private function descriptionToCreate(mixed $oldBody, mixed $postTicket, $message): string
    {
        $OldMessage = '';
        if (property_exists($oldBody, 'description')) {
            $OldMessage = $oldBody->description . PHP_EOL;
        }

        return $OldMessage . '[' . ((int)date('H') + 6) . date(':i:s') . ' ' . date('Y-m-d') . '] ' . $message . $postTicket->data->fixed_check;
    }

    private function itemPosition(string $name, float $price, float $quantity, float $discount, int $UOM, $is_nds, int $section, string $code): array
    {
        $item =  [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
            'total_amount' => (round($price * $quantity - $discount, 2)),
            'section' => $section,
            'quantity_type' => $UOM,
            'is_nds' => $is_nds,
            'discount' => $discount,
            'mark_code' => $code,
        ];

        if ($discount <= 0) {
            unset($item['discount']);
        }
        if ($code == "") {
            unset($item['mark_code']);
        }
        return $item;
    }





    private function sumAmountMeta($postTicket): string
    {
        $value5 = '';
        foreach ($postTicket->data->transaction_payments as $item_) {
            $amount = 'на сумму: ' . $item_->amount;
            if ($this->accountId == "f0eb536d-d41f-11e6-7a69-971100005224") $amount = '';
            switch ($item_->payment_type) {
                case 0 :
                {
                    $value5 .= "Оплата Наличными " . $amount . " ";
                    break;
                }
                case 1 :
                {
                    $value5 .= "Оплата Картой " . $amount . " ";
                    break;
                }
                case 2 :
                {
                    $value5 .= "Оплата Смешанный " . $amount . " ";
                    break;
                }
                case 3 :
                {
                    $value5 .= "Оплата Мобильный " . $amount . " ";
                    break;
                }
                default:
                {
                    $value5 .= "";
                    break;
                }
            }
        }
        return $value5;
    }

    private function typePaymentMC($postTicket, mixed $row): array
    {
        $name = 'Наличные';
        $value = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/customentity/'.basename($row->customEntityMeta->href));
        $id = $postTicket->data->transaction_payments[0]->payment_type ?? 0;
        if ($id === 1) $name = 'Картой';
        if ($id === 4) $name = 'Мобильная';

        foreach ($value->rows as $item){
            if ($item->name == $name) return ['meta'=>$item->meta, 'name'=>$item->name];
        }

        return ['meta'=>$value[0]->meta, 'name'=>$value[0]->name];

    }
}
