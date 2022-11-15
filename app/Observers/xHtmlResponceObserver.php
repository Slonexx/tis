<?php

namespace App\Observers;

use App\Models\XhtmlResponce;
use Illuminate\Support\Facades\DB;

class xHtmlResponceObserver
{
    public function created(XhtmlResponce $model)
    {

        $accountIds = XhtmlResponce::all('accountId');

        foreach($accountIds as $accountId){

            $query = XhtmlResponce::query();
            $logs = $query->where('accountId',$accountId->accountId)->get();
            if(count($logs) > 10){
                DB::table('xhtml_responces')
                    ->where('accountId','=',$accountId->accountId)
                    ->orderBy('created_at', 'ASC')
                    ->limit(1)
                    ->delete();
            }

        }

    }
}
