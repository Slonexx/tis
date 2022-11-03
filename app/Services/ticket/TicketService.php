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
        $positions = $data['positions'];
        $money_card = $data['money_card'];
        $money_cash = $data['money_cash'];
        $payType = $data['pay_type'];


        $Setting = new getMainSettingBD($accountId);
        $ClientTIS = new KassClient($Setting->authtoken);
        $Config = new globalObjectController();

        $Body = $this->setBodyToPostClient($Setting, $id_entity, $entity_type, $positions, $money_card, $money_cash, $payType);

        $postTicket = $ClientTIS->POSTClient($Config->apiURL_ukassa.'v2/operation/ticket/', $Body);
    }


    public function setBodyToPostClient($Setting, $id_entity, $entity_type, $positions, $money_card, $money_cash, $payType){


        $operation = $this->getOperation($payType);
        if ($operation == '') return ['Status' => false, 'Message' => 'не выбран тип продажи'];


        $result = [
            'operation' => $operation,
        ];
    }


    public function getOperation($payType): int|string
    {
        return match ($payType) {
            "sell" => 2,
            "return" => 3,
            default => "",
        };
    }
}
