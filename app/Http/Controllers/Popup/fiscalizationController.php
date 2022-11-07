<?php

namespace App\Http\Controllers\Popup;

use App\Clients\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TicketController;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class fiscalizationController extends Controller
{
    public function fiscalizationPopup(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        return view( 'popup.fiscalization', [] );
    }

    public function ShowFiscalizationPopup(Request $request): \Illuminate\Http\JsonResponse
    {
        $object_Id = $request->object_Id;
        $accountId = $request->accountId;
        $Setting = new getSettingVendorController($accountId);

        $json = $this->info_object_Id($object_Id, $Setting);

        return response()->json($json);
    }

    public function info_object_Id($object_Id, $Setting){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/".$object_Id;
        $Client = new MsClient($Setting->TokenMoySklad);
        $Body = $Client->get($url);
        $attributes = null;
        if (property_exists($Body, 'attributes')){
            $attributes = [
                'ticket_id' => null,
            ];
            foreach ($Body->attributes as $item){
                if ($item->name == 'id (Ukassa)'){
                    $attributes['ticket_id'] = $item->value;
                    break;
                }
            }
        }
        $vatEnabled = $Body->vatEnabled;
        $vat = null;
        $products = [];
        $positions = $Client->get($Body->positions->meta->href)->rows;

        foreach ($positions as $id=>$item){
            $final = $item->price / 100 * $item->quantity;

            if ($vatEnabled == true) {if ($Body->vatIncluded == false) {
                    $final = $item->price / 100 * $item->quantity;
                    $final = $final + ( $final * ($item->vat/100) );
            }}
            $uom_body = $Client->get($item->assortment->meta->href);

            if (property_exists($uom_body, 'uom')){
               $propety_uom = true;
               $uom = $Client->get($uom_body->uom->meta->href);
               $uom = ['id' => $uom->code, 'name' => $uom->name];
            } else {
                $propety_uom = false;
                $uom = ['id' => 796, 'name' => 'шт'];
            }


            $products[$id] = [
                'position' => $item->id,
                'propety' => $propety_uom,
                'name' => $Client->get($item->assortment->meta->href)->name,
                'quantity' => $item->quantity,
                'uom' => $uom,
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



    public function SendFiscalizationPopup(Request $request){
        $accountId = $request->accountId;
        $object_Id = $request->object_Id;
        $entity_type = $request->entity_type;

        if ($request->money_card === null) $money_card = 0;
        else $money_card = $request->money_card;
        if ($request->money_cash === null) $money_cash = 0;
        else $money_cash = $request->money_cash;
        $pay_type = $request->pay_type;

        $total = $request->total;

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
            'pay_type' => $pay_type,

            'total' => $total,

            'positions' => $positions,
        ];

        //dd(($body), json_encode($body));

        $Client = new Client();
        //$url = 'https://smarttis.kz/api/create/ticket';
        $url = 'http://tus/api/create/ticket';
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