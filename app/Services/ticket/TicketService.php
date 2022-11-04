<?php

namespace App\Services\ticket;

use App\Clients\KassClient;
use App\Clients\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\globalObjectController;
use App\Services\AdditionalServices\DocumentService;
use App\Services\MetaServices\MetaHook\AttributeHook;

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
        $Config = new globalObjectController();

        $Body = $this->setBodyToPostClient($Setting, $id_entity, $entity_type, $money_card, $money_cash, $payType, $total, $positions);



        if (isset($Body['Status'])) {
            return response()->json($Body['Message']);
        }

        try {
            $postTicket = $ClientTIS->POSTClient($Config->apiURL_ukassa.'v2/operation/ticket/', $Body);
        } catch (\Throwable $e){
           return response()->json(['code' => $e->getCode(), 'message'=> $e->getMessage()]);
        }
        dd($postTicket);
        $Client = new MsClient($Setting->tokenMs);
        $putBody = $this->putBodyMS($postTicket, $Client, $Setting);
        $put = $Client->put('https://online.moysklad.ru/api/remap/1.2/entity/'.$entity_type.'/'.$id_entity, $putBody);

        return response()->json($postTicket);
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

    private function getCustomer($Setting, $id_entity, $entity_type)
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

    private function putBodyMS(mixed $postTicket, MsClient $Client, getMainSettingBD $Setting)
    {
    }
}
