<?php

namespace App\Services\ticket;


use App\Clients\KassClient;
use App\Clients\MsClient;
use App\Clients\TestKassClient;
use App\Http\Controllers\globalObjectController;
use App\Models\html_integration;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;


class integrationTicketService
{
    private mixed $data;
    private MsClient $msClient;

    public function createTicket($data): JsonResponse
    {
        $this->data = $data;
        $this->msClient = new MsClient($data->connection->ms_token);

        $ClientTIS = new KassClient($this->data->setting_main->kassa_token);

        $id_entity = $data->object_Id;
        $entity_type = $data->entity_type;

        $money_card = $data->data->money_card;
        $money_cash = $data->data->money_cash;
        $money_mobile = $data->data->money_mobile ?? 0;

        $total = $data->data->total;
        $payType = $data->data->pay_type;

        $positions = $data->data->position;


        if ($data->accountId == '1dd5bd55-d141-11ec-0a80-055600047495') $Config = new globalObjectController(false);
        else $Config = new globalObjectController();
        $oldBody = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/' . $id_entity);
        $Body = $this->setBodyToPostClient($id_entity, $entity_type, $money_card, $money_cash, $money_mobile, $payType, $total, $positions);


        if (isset($Body['Status'])) return response()->json([
            'status' => false,
            'message' => $Body['Message'],
        ]);


        //dd(1, $Body);

        try {
            if ($data->accountId == '1dd5bd55-d141-11ec-0a80-055600047495') $postTicket = $ClientTIS->POSTClient($Config->test_apiURL_ukassa . 'v2/operation/ticket/', $Body);
            else $postTicket = $ClientTIS->POSTClient($Config->apiURL_ukassa . 'v2/operation/ticket/', $Body);
        } catch (BadResponseException  $e) {
            return response()->json([
                'status' => false,
                'code' => $e->getCode(),
                'message' => json_decode($e->getResponse()->getBody()->getContents(), true),
                'errors_' => $e->getMessage()
            ]);
        }

        try {
            $putBody = $this->putBodyMS($entity_type, $Body, $postTicket, $oldBody, $positions);
            if ($putBody == null) return response()->json([
                'status' => false,
                'code' => 300,
                'message' => "Чек отправился в Учёт.ТИС, но нет изменения в МоемСкладе, просьба обратитесь к разработчикам",
                'errors_' => "Чек отправился в Учёт.ТИС, но нет изменения в МоемСкладе, просьба обратитесь к разработчикам",
            ]);
            $put = $this->msClient->put('https://api.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/' . $id_entity, $putBody);
        } catch (BadResponseException $e) {
            return response()->json([
                'info' => 'При сохранении данных в МС',
                'status' => false,
                'code' => $e->getCode(),
                'message' => json_decode($e->getResponse()->getBody()->getContents(), true),
                'postTicket' => $postTicket,
            ]);
        }

        try {
            if ($this->data->accountId != "f0eb536d-d41f-11e6-7a69-971100005224")
                if ($payType == 'return') {
                    $this->createReturnDocument($put, $postTicket, $putBody, $entity_type);
                    $put = $this->msClient->put('https://api.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/' . $id_entity, [
                        'description' => $this->descriptionToCreate($oldBody, $postTicket, 'Возврат, фискальный номер: '),
                    ]);
                }
        } catch (BadResponseException $e) {
            return response()->json([
                'info' => 'При сохранении данных в МС return',
                'status' => false,
                'code' => $e->getCode(),
                'message' => json_decode($e->getResponse()->getBody()->getContents(), true),
                'postTicket' => $postTicket,
            ]);
        }


        try {
            if ($this->data->setting_document->paymentDocument != null) $this->createPaymentDocument($entity_type, $put, $Body['payments']);
        } catch (BadResponseException $e) {
            return response()->json([
                'info' => 'При сохранении данных в МС createPaymentDocument',
                'status' => false,
                'code' => $e->getCode(),
                'message' => json_decode($e->getResponse()->getBody()->getContents(), true),
                'postTicket' => $postTicket,
            ]);
        }


        try {
            $model = new html_integration();

            $model->accountId = $this->data->accountId;
            $model->kkm_id = $postTicket->data->fixed_check;
            $model->html = $postTicket->data->html;

            $model->save();
        } catch (BadResponseException $e) {
            return response()->json([
                'info' => 'При сохранении данных в model',
                'status' => false,
                'code' => $e->getCode(),
                'message' => json_decode($e->getResponse()->getBody()->getContents(), true),
                'postTicket' => $postTicket,
            ]);
        }


        return response()->json([
            'status' => true,
            'message' => 'Чек создан',
            'code' => 200,
            'postTicket' => $postTicket,
        ]);


    }


    private function setBodyToPostClient(mixed $id_entity, mixed $entity_type, mixed $money_card, mixed $money_cash, $money_mobile, mixed $payType, mixed $total, mixed $positions): array
    {

        $operation = $this->getOperation($payType);
        $payments = $this->getPayments($money_card, $money_cash,$money_mobile, $total);
        $items = $this->getItems($positions, $id_entity, $entity_type);
        $customer = $this->getCustomer($id_entity, $entity_type);

        if ($operation == '') return ['Status' => false, 'Message' => 'не выбран тип продажи'];
        if ($this->data->setting_document->idKassa == null) return ['Status' => false, 'Message' => 'Не были пройдены настройки !'];
        if ($payments == null) return ['Status' => false, 'Message' => 'Не были введены суммы !'];


        return [
            'operation' => (int)$operation,
            'kassa' => (int)$this->data->setting_document->idKassa,
            'payments' => $payments,
            'items' => $items,
            "total_amount" => (float)$total,
            "customer" => $customer,
            'need_mark_code' => true,
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

    private function getPayments($card, $cash, $mobile, $total): array
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
        if ($mobile > 0) {

            $result[] = [
                'payment_type' => 4,
                'total' => (float)$mobile,
                'amount' => (float)$mobile,
            ];
        }
        return $result;
    }

    private function getItems($positions, $idObject, $typeObject): array
    {
        $result = null;

        if ($typeObject == 'demand') {
            $demandPos = $this->msClient->get(($this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $typeObject . '/' . $idObject))->positions->meta->href)->rows;
        }


        foreach ($positions as $id => $item) {
            $is_nds = trim($item->is_nds, '%');
            $discount = trim($item->discount, '%');
            if ($is_nds == 'без НДС' or $is_nds == "0%") {
                $is_nds = false;
            } else $is_nds = true;
            if ($discount > 0) {
                $discount = round(($item->price * $item->quantity * ($discount / 100)), 2);
            }

            if ($typeObject == 'demand') {

                if (isset($demandPos[$id]->trackingCodes)) {
                    foreach ($demandPos[$id]->trackingCodes as $code) {
                        $result[] = $this->itemPosition($item->name, $item->price, 1, $discount, $item->UOM, $is_nds, $this->data->setting_document->idDepartment, $code->cis);
                    }
                } else {
                    $result[] = $this->itemPosition($item->name, $item->price, $item->quantity, $discount, $item->UOM, $is_nds, $this->data->setting_document->idDepartment, '');
                }

            } else {
                $result[] = $this->itemPosition($item->name, $item->price, $item->quantity, $discount, $item->UOM, $is_nds, $this->data->setting_document->idDepartment, '');
            }
        }

        return $result;
    }

    private function getCustomer($id_entity, $entity_type): array
    {
        $body = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/' . $id_entity);
        $agent = $this->msClient->get($body->agent->meta->href);
        $result = [];

        if (property_exists($agent, 'email')) {
            $result['email'] = $agent->email;
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

    private function putBodyMS($entity_type, mixed $Body, mixed $postTicket, mixed $oldBody, mixed $positionsBody): ?array
    {
        $result = null;
        $Result_attributes = $this->setAttributesToPutBody($Body, $postTicket, $entity_type);
        if ($Result_attributes == null) return null;

        if ($this->data->accountId != '686ca08f-eb47-11e8-9109-f8fc00009aa4') {
            if ($this->data->data->pay_type == 'sell') $result['description'] = $this->descriptionToCreate($oldBody, $postTicket, 'Продажа, Фискальный номер: ');
            else  $result['description'] = $this->descriptionToCreate($oldBody, $postTicket, 'Возврат продажи, Фискальный номер: ');
        }

        $result['attributes'] = $Result_attributes;

        return $result;
    }

    private function setAttributesToPutBody(mixed $Body, mixed $postTicket, string $entityType)
    {
        $body = null;
        $meta = $this->getMeta($entityType);

        if ($meta['fiscal_number'] != null) $body["attributes"][] = ["meta" => $meta['fiscal_number'], "value" => "" . $postTicket->data->fixed_check,];
        if ($meta['link_to_check'] != null) $body["attributes"][] = ["meta" => $meta['link_to_check'], "value" => $postTicket->data->link,];
        if ($meta['fiscalization'] != null) $body["attributes"][] = ["meta" => $meta['fiscalization'], "value" => true];
        if ($meta['kkm_ID'] != null) $body["attributes"][] = ["meta" => $meta['kkm_ID'], "value" => "" . $postTicket->data->fixed_check];


        return $body["attributes"];
    }

    private function getMeta($entityType): array
    {

        $url = match ($entityType) {
            "demand" => "https://api.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes",
            "salesreturn" => "https://api.moysklad.ru/api/remap/1.2/entity/salesreturn/metadata/attributes",
            default => "https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes",
        };

        $json = $this->msClient->get($url);
        $meta = null;
        foreach ($json->rows as $row) {
            if ($row->name == "фискальный номер") {
                $meta['fiscal_number'] = $row->meta;
            } elseif ($row->name == "Ссылка на чек") {
                $meta['link_to_check'] = $row->meta;
            } elseif ($row->name == "Фискализация") {
                $meta['fiscalization'] = $row->meta;
            } elseif ($row->name == "kkm_ID") {
                $meta['kkm_ID'] = $row->meta;
            }
        }

        return [
            'fiscal_number' => $meta['fiscal_number'] ?? '',
            'link_to_check' => $meta['link_to_check'] ?? '',
            'fiscalization' => $meta['fiscalization'] ?? '',
            'kkm_ID' => $meta['kkm_ID'] ?? '',
        ];
    }

    private function createPaymentDocument(string $entity_type, mixed $OldBody, mixed $payments): void
    {
        switch ($this->data->setting_document->paymentDocument) {
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
                $this->msClient->post($url, $body);
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

                $rate_body = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
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
                $this->msClient->post($url, $body);
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

                    $rate_body = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
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
                    $this->msClient->post($url_to_body, $body);
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
                            if ($this->data->setting_document->OperationCash == 1) {
                                $url_to_body = $url . 'cashin';
                            }
                            if ($this->data->setting_document->OperationCash == 2) {
                                $url_to_body = $url . 'paymentin';
                            }
                            if ($this->data->setting_document->OperationCash == 0) {
                                continue;
                            }
                        } else {
                            if ($this->data->setting_document->OperationCash == 1) {
                                $url_to_body = $url . 'cashout';
                            }
                            if ($this->data->setting_document->OperationCash == 2) {
                                $url_to_body = $url . 'paymentout';
                            }
                            if ($this->data->setting_document->OperationCash == 0) {
                                continue;
                            }
                        }
                        if (isset($item['change'])) $change = $item['change'];
                    }
                    else {
                        if ($entity_type != 'salesreturn') {
                            if ($this->data->setting_document->OperationCard == 1) {
                                $url_to_body = $url . 'cashin';
                            }
                            if ($this->data->setting_document->OperationCard == 2) {
                                $url_to_body = $url . 'paymentin';
                            }
                            if ($this->data->setting_document->OperationCard == 0) {
                                continue;
                            }
                        } else {
                            if ($this->data->setting_document->OperationCard == 1) {
                                $url_to_body = $url . 'cashout';
                            }
                            if ($this->data->setting_document->OperationCard == 2) {
                                $url_to_body = $url . 'paymentout';
                            }
                            if ($this->data->setting_document->OperationCard == 0) {
                                continue;
                            }
                        }
                    }

                    if (isset($item['change'])) {
                        $change = $item['change'];
                    }

                    if ($url_to_body != null) {
                        $rate_body = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/currency/")->rows;
                        $rate = null;
                        foreach ($rate_body as $item_rate) {
                            if (property_exists($item_rate, 'fullName'))
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
                                ],
                            ],
                            'rate' => $rate
                        ];

                        if ($body['rate'] == null) {
                            unset($body['rate']);
                        }

                        if ($url_to_body == 'https://api.moysklad.ru/api/remap/1.2/entity/paymentout' or $url_to_body == 'https://api.moysklad.ru/api/remap/1.2/entity/cashout') {
                            $UUID = Uuid::uuid4();
                            $expenseItem = $this->msClient->newPost('https://api.moysklad.ru/api/remap/1.2/entity/expenseitem', [
                                'name' => 'Возврат ' . Uuid::uuid4()->toString(),
                                'description' => 'Возврат ' . Uuid::uuid4()->toString(),
                                'code' => 'Возврат ' . Uuid::uuid4()->toString(),
                                'externalCode' => 'Возврат ' . Uuid::uuid4()->toString(),
                            ]);
                            $body['expenseItem']['meta'] = $expenseItem->data->meta;
                        }

                        //if ($this->data->accountId == '1dd5bd55-d141-11ec-0a80-055600047495') dd($url_to_body, $body, $payments, $change);

                        $this->msClient->newPost($url_to_body, $body);
                    }
                }
                break;
            }
            default:
            {
                break;
            }
        }

    }

    private function createReturnDocument(mixed $newBody, mixed $putBody, mixed $oldBody, mixed $entity_type): void
    {
        if ($entity_type != 'salesreturn') {

            $attributes_item = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/salesreturn/metadata/attributes/')->rows;
            $attributes = null;
            $positions = null;
            foreach ($attributes_item as $item) {
                if ($item->name == 'фискальный номер') {
                    $attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => $putBody->data->fixed_check,
                    ];
                }
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
                if ($item->name == 'Фискализация') {
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
                $store = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/store')->rows[0];
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
                $this->$this->msClient->post($url, $body);
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

        return $OldMessage . '[' . ((int)date('H') + 6) . date(':i:s') . ' ' . date('Y-m-d') . '] ' . $message . $postTicket->data->check_number;
    }


    private function itemPosition(string $name, float $price, float $quantity, float $discount, int $UOM, $is_nds, int $section, string $code): array
    {
        $item = [
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

}
