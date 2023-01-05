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

class dev_CreateTicketService
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

        $money_card = $data['money_card'];
        $money_cash = $data['money_cash'];
        $payType = $data['pay_type'];
        $total = $data['total'];

        $positions = $data['positions'];

        $Setting = new getMainSettingBD($accountId);

        $ClientTIS = new KassClient($Setting->authtoken);
        $Client = new MsClient($Setting->tokenMs);
        $Config = new globalObjectController();

        $Body = $this->setBodyToPostClient($Setting, $money_card, $money_cash, $payType, $total, $positions);

        if (isset($Body['Status'])) {
            return response()->json($Body['Message']);
        }

        try {
            $postTicket = $ClientTIS->POSTClient($Config->apiURL_ukassa.'v2/operation/ticket/', $Body);
            //  dd($postTicket);

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
            return response()->json([
                'status'    => 'error',
                'code'      => $e->getCode(),
                'errors'    => json_decode($e->getResponse()->getBody()->getContents(), true)
            ]);
        }

    }


    private function setBodyToPostClient(getMainSettingBD $Setting, mixed $money_card, mixed $money_cash, mixed $payType, mixed $total, mixed $positions): array
    {

        $operation = $this->getOperation($payType);
        $payments = $this->getPayments($money_card, $money_cash, $total);
        $items = $this->getItems($Setting, $positions);

        if ($operation == '') return ['Status' => false, 'Message' => 'не выбран тип продажи'];
        if ($Setting->idKassa == null) return ['Status' => false, 'Message' => 'Не были пройдены настройки !'];
        if ($payments == null) return ['Status' => false, 'Message' => 'Не были введены суммы !'];


        return [
            'operation' => (int) $operation,
            'kassa' => (int) $Setting->idKassa,
            'payments' => $payments,
            'items' => $items,
            "total_amount" => (float) $total,
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

    private function getItems(getMainSettingBD $Setting, $positions): array
    {
        $result = null;
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



        foreach ($result as $id => $item){
            if ($item['discount']<= 0) {
                unset($result[$id]['discount']);
            }
        }

        return $result;
    }

}
