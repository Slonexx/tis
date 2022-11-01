<?php

namespace App\Http\Controllers\Popup;

use App\Clients\MsClient;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getSetting;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class salesreturnController extends Controller
{
    public function salesreturnPopup(Request $request){

        return view( 'popup.salesreturn', [

        ] );
    }

    public function ShowSalesreturnPopup(Request $request){
        $object_Id = $request->object_Id;
        $accountId = $request->accountId;
        $Setting = new getSetting($accountId);

        $json = $this->info_object_Id($object_Id, $Setting);

        return response()->json($json);
    }

    public function info_object_Id($object_Id, $Setting){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/salesreturn/".$object_Id;
        $Client = new MsClient($Setting->tokenMs);
        $Body = $Client->get($url);
        $positions = $Client->get($Body->positions->meta->href)->rows;
        $attributes = null;
        if (property_exists($Body, 'attributes')){
            $attributes = [
                'ticket_id' => null,
            ];
            foreach ($Body->attributes as $item){
                if ($item->name == 'id-билета (ReKassa)'){
                    $attributes['ticket_id'] = $item->value;
                    break;
                }
            }
        }
        $vatEnabled = $Body->vatEnabled;
        $vat = null;
        $products = [];

        foreach ($positions as $id=>$item){

            $final = $item->price / 100 * $item->quantity;

            if ($vatEnabled == true) {
                if ($Body->vatIncluded == false) {
                    $final = $item->price / 100 * $item->quantity;
                    $final = $final + ( $final * ($item->vat/100) );
                }
            }
            $uom_body = $Client->get($item->assortment->meta->href);
            if (property_exists($uom_body, 'uom')){
                $propety_uom = true;
            } else $propety_uom = false;


            $products[$id] = [
                'position' => $item->id,
                'propety' => $propety_uom,
                'name' => $Client->get($item->assortment->meta->href)->name,
                'quantity' => $item->quantity,
                'price' => round($item->price / 100, 2) ?: 0,
                'vatEnabled' => $item->vatEnabled,
                'vat' => $item->vat,
                'discount' => round($item->discount, 2),
                'final' => round($final - ( $final * ($item->discount/100) ), 2),
            ];
        }

        if ($vatEnabled == true) {
            $vat = [
                'vatEnabled' => $Body->vatEnabled,
                'vatIncluded' => $Body->vatIncluded,
                'vatSum' => $Body->vatSum / 100 ,
            ];
        };
        return [
            'id' => $Body->id,
            'name' => $Body->name,
            'sum' => $Body->sum / 100,
            'vat' => $vat,
            'attributes' => $attributes,
            'products' => $products,
        ];
    }



    public function SendSalesreturnPopup(Request $request){
        $accountId = $request->accountId;
        $object_Id = $request->object_Id;
        $entity_type = $request->entity_type;
        if ($request->money_card === null) $money_card = 0;
        else $money_card = $request->money_card;

        if ($request->money_cash === null) $money_cash = 0;
        else $money_cash = $request->money_cash;

        if ($request->money_mobile === null) $money_mobile = 0;
        else $money_mobile = $request->money_mobile;

        $pay_type = $request->pay_type;
        $position = json_decode($request->position);
        $positions = [];
        foreach ($position as $item){
            if ($item != null){
                $positions[] = $item;
            }
        }

        $body = [
            'accountId' => $accountId,
            'id_entity' => $object_Id,
            'entity_type' => $entity_type,
            'money_card' => $money_card,
            'money_cash' => $money_cash,
            'money_mobile' => $money_mobile,
            'pay_type' => $pay_type,
            'positions' => $positions,
        ];

        $Client = new Client();
        $url = 'https://smartrekassa.kz/api/ticket';
        //$url = 'http://rekassa/api/ticket';
        try {
            $ClinetPost = $Client->post( $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'http_errors' => false,
                ],
                'form_params' => $body,
            ]);

            $res = json_decode($ClinetPost->getBody());

            return response()->json($res);

        } catch (\Throwable $e){
            return response()->json($e->getMessage());
        }
    }


}
