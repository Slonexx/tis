<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\BD\getWorkerID;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class salesreturnEditController extends Controller
{
    public function salesreturn(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
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

        $entity = 'salesreturn';



        return view( 'widget.salesreturn', [
            'accountId' => $accountId,
            'entity' => $entity,
            //'worker' => $Workers->access,
        ] );
    }
}
