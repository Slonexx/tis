<?php

namespace App\Http\Controllers\Web;

use App\Clients\MsClient;
use App\Http\Controllers\BD\getPersonal;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getSetting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class indexController extends Controller
{
    public function Index(Request $request)
    {

        $contextKey = $request->contextKey;
        if ($contextKey == null) {
            return view("main.dump");
        }
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;

        $isAdmin = $employee->permissions->admin->view;

        return to_route('main', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function indexShow($accountId, Request $request)
    {
        $isAdmin = $request->isAdmin;
        $getPersonal = new getPersonal($accountId);
        if ($getPersonal->status == "деактивированный" or $getPersonal->status == null){
            $hideOrShow = "show";
        } else  $hideOrShow = "hide";

        try {
            $Client = new Client();
            $body = json_decode($Client->get("https://smarttis.kz/web/getInstallPersonalForID/".$accountId)->getBody()->getContents());
            if ($body->StatusCode == 200){
                return view("main.index" , [
                    'accountId' => $accountId,
                    'isAdmin' => $isAdmin,
                    'hideOrShow' => $hideOrShow,
                ] );
            } else {
                return view("main.accept" , [
                    'accountId' => $accountId,
                    'isAdmin' => $isAdmin,
                ] );
            }
        } catch (BadResponseException $e){
            return view("main.error" , [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
                'message' => $e->getMessage(),
            ] );
        }


    }

    public function widgetInfoAttributes(Request $request)
    {
        $ticket_id = null;

        $accountId = $request->accountId;
        $entity_type = $request->entity_type;
        $objectId = $request->objectId;

        $url = $this->getUrlEntity($entity_type, $objectId);
        $Setting = new getSettingVendorController($accountId);
        try {
            $Client = new MsClient($Setting->TokenMoySklad);
            $body = $Client->get($url);
        } catch (BadResponseException $e){
            return view( 'widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message,
            ] );
        }

        if (property_exists($body, 'attributes')){
            foreach ($body->attributes as $item){
                if ($item->name == 'фискальный номер (ТИС)'){
                    if ($item->value != null) $ticket_id = $item->value;
                    break;
                }
            }
        }
        return response()->json(['ticket_id' => $ticket_id]);
    }

    private function getUrlEntity($enType,$enId): ?string
    {
        return match ($enType) {
            "customerorder" => "https://api.moysklad.ru/api/remap/1.2/entity/customerorder/" . $enId,
            "demand" => "https://api.moysklad.ru/api/remap/1.2/entity/demand/" . $enId,
            "salesreturn" => "https://api.moysklad.ru/api/remap/1.2/entity/salesreturn/" . $enId,
            default => null,
        };
    }

}
