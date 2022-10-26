<?php

namespace App\Services\MetaServices\MetaHook;

use App\Clients\MsClient;

class ExpenseItemHook
{

    public function getExpenseItem($expenseItemName,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/expenseitem";
        $client = new MsClient($apiKey);
        $jsonStates = $client->get($uri);
        $foundedMeta = null;
        foreach($jsonStates->rows as $row) {
            if($row->name == $expenseItemName){
                $foundedMeta= $row->meta;
                break;
            }
        }
        return $foundedMeta;
    }

}
