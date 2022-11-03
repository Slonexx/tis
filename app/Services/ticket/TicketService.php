<?php

namespace App\Services\ticket;

use App\Clients\KassClient;
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

        if (array_key_exists($Body['Status'])) {
            return response()->json($Body['Message']);
        }

        try {
            $postTicket = $ClientTIS->POSTClient($Config->apiURL_ukassa.'v2/operation/ticket/', $Body);
        } catch (\Throwable $e){
            return response()->json(['code' => $e->getCode(), 'message'=> $e->getMessage()]);
        }
        return response()->json($postTicket);
    }


    public function setBodyToPostClient($Setting, $id_entity, $entity_type, $money_card, $money_cash, $payType, $total, $positions): array
    {

        $operation = $this->getOperation($payType);
        $payments = $this->getPayments($money_card, $money_cash, $total);
        $items = $this->getItems($Setting, $positions, $id_entity, $entity_type);

        if ($operation == '') return ['Status' => false, 'Message' => 'не выбран тип продажи'];
        if ($Setting->idKassa == null) return ['Status' => false, 'Message' => 'Не были пройдены настройки !'];
        if ($payments == null) return ['Status' => false, 'Message' => 'Не были введены суммы !'];


        return [
            'operation' => $operation,
            'kassa' => (int) $Setting->idKassa,
            'payments' => $payments,
            'items' => $items,
            "total_amount" => $total,
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
        $result = null;
        if ( $cash > 0 ) {
            $change = $total - $cash;

            $result[] = [
                'payment_type' => 0,
                'total' => $cash+$change,
                'change' => $change,
                'amount' => $cash,
            ];
        }
        if ( $card > 0 ) {
            $change = $total - $card;

            $result[] = [
                'payment_type' => 1,
                'total' => $cash + $change,
                'amount' => $cash,
            ];
        }

        return $result;
    }

    private function getItems($Setting, $positions, $idObject, $typeObject): array
    {
        $result = null;
        foreach ($positions as $item){
            $is_nds = trim($item->is_nds, '%');
            $discount = trim($item->discount, '%');
            if ($is_nds == 'без НДС' or $is_nds == "0%"){$is_nds = false;
            } else $is_nds = true;


            $result[] = [
                'name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'total_amount' => ($item->price * $item->quantity),
                'is_nds' => $is_nds,
                'discount' => $discount,
            ];
        }
        return $result;
    }
}
