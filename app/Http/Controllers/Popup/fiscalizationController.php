<?php

namespace App\Http\Controllers\Popup;

use App\Clients\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TicketController;
use App\Models\htmlResponce;
use App\Models\mainSetting;
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
                if ($item->name == 'фискальный номер (ТИС)'){
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

                if (property_exists($uom_body, 'characteristics')){
                    $check_uom = $Client->get($uom_body->product->meta->href);

                    if ( property_exists($check_uom, 'uom') ) {
                        $propety_uom = true;
                        $uom = $Client->get($check_uom->uom->meta->href);
                        $uom = ['id' => $uom->code, 'name' => $uom->name];
                    } else {
                        $propety_uom = false;
                        $uom = ['id' => 796, 'name' => 'шт'];
                    }
                } else {
                    $propety_uom = false;
                    $uom = ['id' => 796, 'name' => 'шт'];
                }
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

        $data = $request->all();

        $accountId = $data['accountId'];
        $object_Id = $data['object_Id'];
        $entity_type = $data['entity_type'];

        if ($data['money_card'] === null) $money_card = 0;
        else $money_card = $data["money_card"];
        if ($data['money_cash'] === null) $money_cash = 0;
        else $money_cash = $data['money_cash'];
        $pay_type = $data['pay_type'];

        $total = $data['total'];

        $position = json_decode(json_encode($data['position']));


        $body = [
            'accountId' => $accountId,
            'id_entity' => $object_Id,
            'entity_type' => $entity_type,

            'money_card' => $money_card,
            'money_cash' => $money_cash,
            'pay_type' => $pay_type,

            'total' => $total,

            'positions' => $position,
        ];

        dd($body);

        try {

            return app(TicketController::class)->CreateTicketResponse($body);

        } catch (\Throwable $e){
            //dd($e->getCode());
            return response()->json($e->getMessage());
        }
    }

    public function printFiscalizationPopup(Request $request, $accountId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        $find = htmlResponce::query()->where('accountId', $accountId)->latest()->first();
        $result = $find->getAttributes();

        return view( 'popup.print', [ 'html' => $result['html'] ] );
    }

}
