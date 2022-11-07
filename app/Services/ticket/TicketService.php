<?php

namespace App\Services\ticket;

use App\Clients\KassClient;
use App\Clients\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\globalObjectController;
use App\Services\AdditionalServices\DocumentService;
use App\Services\MetaServices\MetaHook\AttributeHook;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

class TicketService
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
            $postTicket = $ClientTIS->POSTClient($Config->apiURL_ukassa.'v2/operation/ticket/', $Body);
            $putBody = $this->putBodyMS($postTicket, $Client, $Setting, $oldBody, $positions);
            $put = $Client->put('https://online.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/'.$id_entity, $putBody);

            return response()->json([
                'status'    => 'Ticket created',
                'code'      => 200,
            ]);

        } catch (BadResponseException  $e){
            return response()->json([
                'status'    => 'error',
                'code'      => $e->getCode(),
                'errors'    => json_decode($e->getResponse()->getBody()->getContents(), true)
            ]);
        }
        //$postTicket = null;



    }


    public function setBodyToPostClient($Setting, $id_entity, $entity_type, $money_card, $money_cash, $payType, $total, $positions): array
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
            //"as_html" => true,
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
            $change = $total - $cash;

            $result[] = [
                'payment_type' => 0,
                'total' => (float) $cash+$change,
                'change' => (float) $change,
                'amount' => (float) $cash,
            ];
        }
        if ( $card > 0 ) {
            $change = $total - $card;

            $result[] = [
                'payment_type' => 1,
                'total' => (float) $card + $change,
                'amount' => (float) $card,
            ];
        }

        return $result;
    }

    private function getItems($Setting, $positions, $idObject, $typeObject): array
    {
        $result = null;
        foreach ($positions as $id => $item){
            $is_nds = trim($item['is_nds'], '%');
            $discount = trim($item['discount'], '%');
            if ($is_nds == 'без НДС' or $is_nds == "0%"){$is_nds = false;
            } else $is_nds = true;


            $result[$id] = [
                'name' => (string) $item['name'],
                'price' => (float) $item['price'],
                'quantity' => (float) $item['quantity'],
                'quantity_type' => (int) $item['UOM'],
                'total_amount' => (float) ($item['price'] * $item['quantity']),
                'is_nds' => $is_nds,
                'discount' =>(float) $discount,
                'section' => (int) $Setting->idDepartment,
            ];

            if ($discount == 0 or $discount < 0 ) {
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
        $result = null;

        if (property_exists($agent, 'email')) { $result['email'] = $agent->email; }
        if (property_exists($agent, 'phone')) { $result['phone'] = $agent->phone; }
        if (property_exists($agent, 'inn')) { $result['iin'] = $agent->inn; }

        return $result;

    }

    private function putBodyMS(mixed $postTicket, MsClient $Client, getMainSettingBD $Setting, mixed $oldBody, mixed $positionsBody)
    {   $result = null;
        $Result_attributes = null;
        $Resul_positions = null;
        $attributes = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes/')->rows;
        $Result_attributes = $this->setAttributesToPutBody($postTicket, $attributes);
        if ($attributes != null){ $result[0] = [ 'attributes' => $attributes ]; }

        $positions = $Client->get($oldBody->positions->meta->href)->rows;
        $Resul_positions = $this->setPositionsToPutBody($postTicket, $positions, $positionsBody);

        if ($Result_attributes != null){
            $result[0] = [ 'attributes' => $Result_attributes, ];
        }
        if ($Resul_positions != null){
            $result[0] = [ 'positions' => $Resul_positions, ];
        }
        dd($result);
        return $result;
    }

    private function setAttributesToPutBody(mixed $postTicket, $attributes): array
    {
        $Result_attributes = null;
        foreach ($attributes as $item) {
            if ($item->name == "фискальный номер (ukassa)" ) {
                $Result_attributes[] = [
                    "meta"=> [
                        "href"=> $item->meta->href,
                        "type"=> $item->meta->type,
                        "mediaType"=> $item->meta->mediaType,
                    ],
                    "value" => $postTicket->data->fixed_check,
                ];
            }
            if ($item->name == "Ссылка для QR-кода" ) {
                $Result_attributes[] = [
                    "meta"=> [
                        "href"=> $item->meta->href,
                        "type"=> $item->meta->type,
                        "mediaType"=> $item->meta->mediaType,
                    ],
                    "value" => $postTicket->data->link,
                ];
            }
            if ($item->name == "Фискализация (ukassa)" ) {
                $Result_attributes[] = [
                    "meta"=> [
                        "href"=> $item->meta->href,
                        "type"=> $item->meta->type,
                        "mediaType"=> $item->meta->mediaType,
                    ],
                    "value" => true,
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
                if ($item_p->id == $one['id']){
                    $sort[$id] = $item_p;
                }
            }
        }
        foreach ($positionsBody as $id=>$item){
            $result[$id] = [
                "id" => $item['id'],
                "quantity" => (int) $item['quantity'],
                "price" => (float) $item['price'],
                "discount" => (int) $item['discount'],
                "vat" => (int) $item['is_nds'],
                "assortment" => ['meta'=>[
                    "href" => $sort[$id]->assortment->meta->href,
                    "type" => $sort[$id]->assortment->meta->type,
                    "mediaType" => $sort[$id]->assortment->meta->mediaType,
                ]],
            ];
        }
        return $result;

    }
}
