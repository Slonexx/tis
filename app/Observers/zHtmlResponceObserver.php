<?php

namespace App\Observers;

use App\Models\htmlResponce;
use App\Models\zHtmlResponce;
use Illuminate\Support\Facades\DB;

class zHtmlResponceObserver
{
    public function created(zHtmlResponce $model)
    {

        $accountIds = zHtmlResponce::all('accountId');

        foreach($accountIds as $accountId){

            $query = zHtmlResponce::query();
            $logs = $query->where('accountId',$accountId->accountId)->get();
            if(count($logs) > 10){
                DB::table('z_html_responces')
                    ->where('accountId','=',$accountId->accountId)
                    ->orderBy('created_at', 'ASC')
                    ->limit(1)
                    ->delete();
            }

        }

    }


    public function updated(zHtmlResponce $model)
    {
        //
    }

    public function deleted(zHtmlResponce $model)
    {
        //
    }

    public function restored(zHtmlResponce $model)
    {
        //
    }

    public function forceDeleted(zHtmlResponce $model)
    {
        //
    }
}
