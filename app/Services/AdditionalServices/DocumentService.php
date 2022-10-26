<?php

namespace App\Services\AdditionalServices;

use App\Clients\MsClient;
use App\Services\MetaServices\MetaHook\AttributeHook;
use App\Services\MetaServices\MetaHook\ExpenseItemHook;

class DocumentService
{

    private AttributeHook $attributeHook;
    private ExpenseItemHook $expenseItemHook;

    /**
     * @param AttributeHook $attributeHook
     * @param ExpenseItemHook $expenseItemHook
     */
    public function __construct(AttributeHook $attributeHook, ExpenseItemHook $expenseItemHook)
    {
        $this->attributeHook = $attributeHook;
        $this->expenseItemHook = $expenseItemHook;
    }

    public function initPayDocument($paymentOption,$formattedOrder,$apiKey)
    {

        $sum = $formattedOrder->sum;
        $metaOrder = $formattedOrder->meta;

        if($paymentOption > 0){
            $this->createPayInDocument($apiKey,$metaOrder,$paymentOption,$formattedOrder,$sum);
        }

    }

    public function initPayReturnDocument($paymentOption,$isReturn,$formattedEntity,$apiKey){
        $sum = $formattedEntity->sum;
        if ($isReturn){
            $metaReturn = $formattedEntity->meta;
        } else {
            $metaReturn = null;
        }

        if($paymentOption > 0){
            $this->createPayOutDocument($apiKey,$metaReturn,$paymentOption,$formattedEntity,$sum);
        }
    }

    private function createPayInDocument($apiKey,$meta,$isPayment,$formattedOrder,$sum)
    {
        $uri = null;
        if ($isPayment == 2) {
            $uri = "https://online.moysklad.ru/api/remap/1.2/entity/paymentin";
        } elseif($isPayment == 1) {
            $uri = "https://online.moysklad.ru/api/remap/1.2/entity/cashin";
        }


        //dd($metaOrder);

        $client = new MsClient($apiKey);
        $docBody = [
            "agent" => $formattedOrder->agent,
            "organization" => $formattedOrder->organization,
            "rate" => $formattedOrder->rate,
            "sum" => $sum,
            "operations" => [
                0=> [
                    "meta" => $meta,
                ],
            ],
        ];

        foreach ($formattedOrder->attributes as $attribute){
            if ($isPayment == 1){
                $meta = $this->attributeHook->getCashInAttribute($attribute->name,$apiKey);
                if (!is_null($meta))
                $docBody["attributes"][] = [
                    "meta" => $meta,
                    "value" => $attribute->value,
                ];
            }elseif ($isPayment ==2){
                $meta = $this->attributeHook->getPaymentInAttribute($attribute->name,$apiKey);
                if (!is_null($meta))
                $docBody["attributes"][] = [
                    "meta" => $meta,
                    "value" => $attribute->value,
                ];
            }
        }

        //dd($docBody);

        if(property_exists($formattedOrder,"salesChannel")){
            $docBody["salesChannel"] = $formattedOrder->salesChannel;
        }

        if(property_exists($formattedOrder,"project")){
            $docBody["project"] = $formattedOrder->project;
        }

        if(property_exists($formattedOrder,"organizationAccount")){
            $docBody["organizationAccount"] = $formattedOrder->organizationAccount;
        }
        $client->post($uri,$docBody);
    }

    private function createPayOutDocument($apiKey, $metaReturn, $isPayment, $formattedEntity, $sum)
    {
        $uri = null;
        if ($isPayment == 2) {
            $uri = "https://online.moysklad.ru/api/remap/1.2/entity/paymentout";
        } elseif($isPayment == 1) {
            $uri = "https://online.moysklad.ru/api/remap/1.2/entity/cashout";
        }

        $client = new MsClient($apiKey);
        $docBody = [
            "agent" => $formattedEntity->agent,
            "organization" => $formattedEntity->organization,
            "expenseItem" => [
                "meta" => $this->expenseItemHook->getExpenseItem('Возврат',$apiKey),
            ],
            "sum" => $sum,
        ];

        if ($metaReturn != null){
            $docBody["operations"] = [
                0=> [
                    "meta" => $metaReturn,
                ],
            ];
        }

        foreach ($formattedEntity->attributes as $attribute){
            if ($isPayment == 1){
                $meta = $this->attributeHook->getCashOutAttribute($attribute->name,$apiKey);
                if (!is_null($meta))
                $docBody["attributes"][] = [
                    "meta" => $meta,
                    "value" => $attribute->value,
                ];
            }elseif ($isPayment ==2){
                $meta = $this->attributeHook->getPaymentOutAttribute($attribute->name,$apiKey);
                if (!is_null($meta))
                $docBody["attributes"][] = [
                    "meta" => $meta,
                    "value" => $attribute->value,
                ];
            }
        }

        if(property_exists($formattedEntity,"salesChannel")){
            $docBody["salesChannel"] = $formattedEntity->salesChannel;
        }

        if(property_exists($formattedEntity,"project")){
            $docBody["project"] = $formattedEntity->project;
        }

        if(property_exists($formattedEntity,"organizationAccount")){
            $docBody["organizationAccount"] = $formattedEntity->organizationAccount;
        }
        $client->post($uri,$docBody);
    }

}
