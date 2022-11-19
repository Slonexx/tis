<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\BD\getWorkerID;
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


        $Workers = new getWorkerID($employee->id);

        if ($Workers->access == 0 or $Workers->access = null){
            return view( 'widget.noAccess', [
                'accountId' => $accountId,
            ] );
        }

        $entity = 'counterparty';



        return view( 'widget.customerorder', [
            'accountId' => $accountId,
            'entity' => $entity,
        ] );
    }
}
