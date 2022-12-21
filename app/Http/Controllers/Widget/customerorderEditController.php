<?php

namespace App\Http\Controllers\Widget;

use App\Clients\MsClient;
use App\Http\Controllers\BD\getWorkerID;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\indexController;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use function view;

class customerorderEditController extends Controller
{
    public function customerorder(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        $contextKey = $request->contextKey;
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;

        $Workers = new getWorkerID($employee->id);

        $Setting = new getSettingVendorController($accountId);
        try {
            $Client = new MsClient($Setting->TokenMoySklad);
            $body = $Client->get("https://online.moysklad.ru/api/remap/1.2/entity/employee");

            if ($Workers->access == 0 or $Workers->access = null){ return view( 'widget.noAccess', ['accountId' => $accountId, ] ); }

            return view( 'widget.customerorder', [
                'accountId' => $accountId,
                'entity' => 'counterparty',
            ] );

        } catch (BadResponseException $e){
            dd(json_decode($e->getResponse()->getBody()->getContents())->message,);
            return view( 'widget.Error', [
                'status' => false,
                'code' => 400,
                'message' => json_decode($e->getResponse()->getBody()->getContents())->message,
            ] );
        }

    }
}
