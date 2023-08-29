<?php

namespace App\Services\ticket;

use App\Clients\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use Illuminate\Http\JsonResponse;

class TestTicketService
{
    public function createTicket($data): JsonResponse
    {
        $accountId = $data['accountId'];
        $id_entity = $data['id_entity'];
        $entity_type = $data['entity_type'];

        $money_card = $data['money_card'];
        $money_cash = $data['money_cash'];
        $payType = $data['pay_type'];
        $total = $data['total'];

        $positions = $data['positions'];

        $Setting = new getMainSettingBD($accountId);

        $Body = $this->setBodyToPostClient($Setting, $id_entity, $entity_type, $money_card, $money_cash, $payType, $total, $positions);

        if (isset($Body['Status'])) {
            return response()->json($Body['Message']);
        }

        dd($Body);

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
            'operation' => (int)$operation,
            'kassa' => (int)$Setting->idKassa,
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

    private function getPayments($card, $cash, $total): array
    {
        //dd($card, $cash, $total);

        $result = null;
        if ($cash > 0) {
            $change = $total - $cash - $card;
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
                $demand = $msClient->get('https://online.moysklad.ru/api/remap/1.2/entity/' . $typeObject . '/' . $idObject);
                $demandPos = $msClient->get($demand->positions->meta->href)->rows;

                foreach ($demandPos as $item_2) {
                    /* if ($item->id == $item_2->id) {
                         if (isset($item_2->trackingCodes) or property_exists($item_2, 'trackingCodes')) {
                             foreach ($item_2->trackingCodes as $code) {
                                 $result[] = [
                                     'name' => (string)$item->name,
                                     'price' => (float)$item->price,
                                     'quantity' => 1,
                                     'quantity_type' => (int)$item->UOM,
                                     'total_amount' => (round($item->price * 1 - $discount, 2)),
                                     'is_nds' => $is_nds,
                                     'discount' => (float)$discount,
                                     'section' => (int)$Setting->idDepartment,
                                     'mark_code' => (string)$code->cis,
                                 ];
                             }
                         } else {
                             $result[$id] = [
                                 'name' => (string)$item->name,
                                 'price' => (float)$item->price,
                                 'quantity' => (float)$item->quantity,
                                 'quantity_type' => (int)$item->UOM,
                                 'total_amount' => (round($item->price * $item->quantity - $discount, 2)),
                                 'is_nds' => $is_nds,
                                 'discount' => (float)$discount,
                                 'section' => (int)$Setting->idDepartment,
                             ];
                         }
                     }*/


                    if ( $item->id == $item_2->id and isset($item_2->trackingCodes) ){
                        foreach ($item_2->trackingCodes as $code){
                            $result[] = [
                                'name' => (string)$item->name,
                                'price' => (float)$item->price,
                                'quantity' => 1,
                                'quantity_type' => (int)$item->UOM,
                                'total_amount' => (round($item->price * 1 - $discount, 2)),
                                'is_nds' => $is_nds,
                                'discount' => (float)$discount,
                                'section' => (int)$Setting->idDepartment,
                                'mark_code' => (string)$code->cis,
                            ];
                        }
                    }
                    elseif ($item->id == $item_2->id){
                        $result[] = [
                            'name' => (string)$item->name,
                            'price' => (float)$item->price,
                            'quantity' => 1,
                            'quantity_type' => (int)$item->UOM,
                            'total_amount' => (round($item->price * 1 - $discount, 2)),
                            'is_nds' => $is_nds,
                            'discount' => (float)$discount,
                            'section' => (int)$Setting->idDepartment,
                        ];
                    }

                }


            } else {
                $result[$id] = [
                    'name' => (string)$item->name,
                    'price' => (float)$item->price,
                    'quantity' => (float)$item->quantity,
                    'quantity_type' => (int)$item->UOM,
                    'total_amount' => (round($item->price * $item->quantity - $discount, 2)),
                    'is_nds' => $is_nds,
                    'discount' => (float)$discount,
                    'section' => (int)$Setting->idDepartment,
                ];
            }
        }

        foreach ($result as $id => $item) {
            if ($item['discount'] <= 0) {
                unset($result[$id]['discount']);
            }
        }

        return $result;
    }

    private function getCustomer($Setting, $id_entity, $entity_type): array
    {
        $Client = new MsClient($Setting->tokenMs);
        $body = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/' . $entity_type . '/' . $id_entity);
        $agent = $Client->get($body->agent->meta->href);
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

}
