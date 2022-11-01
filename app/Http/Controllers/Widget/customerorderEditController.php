<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use function view;

class customerorderEditController extends Controller
{
    public function customerorder(Request $request){

        $contextKey = $request->contextKey;
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;
        //$accountId = "1dd5bd55-d141-11ec-0a80-055600047495";

        //$Workers = new getWorkerID($employee->id);

        $entity = 'counterparty';



        return view( 'widget.customerorder', [
            'accountId' => $accountId,
            'entity' => $entity,
            //'worker' => $Workers->access,
        ] );
    }
}
