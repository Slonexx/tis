<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class demandEditController extends Controller
{
    public function demand(Request $request){
        $contextKey = $request->contextKey;
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;

        //$Workers = new getWorkerID($employee->id);

        $entity = 'demand';



        return view( 'widget.demand', [
            'accountId' => $accountId,
            'entity' => $entity,
            //'worker' => $Workers->access,
        ] );
    }
}
