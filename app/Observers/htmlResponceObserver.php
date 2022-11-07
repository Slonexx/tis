<?php

namespace App\Observers;

use App\Models\addSettingModel;
use App\Models\htmlResponce;
use Illuminate\Support\Facades\DB;

class htmlResponceObserver
{
    public function created(htmlResponce $model)
    {

        $accountIds = htmlResponce::all('accountId');

        foreach($accountIds as $accountId){

            $query = htmlResponce::query();
            $logs = $query->where('accountId',$accountId->accountId)->get();
            if(count($logs) > 10){
                DB::table('html_responces')
                    ->where('accountId','=',$accountId->accountId)
                    ->orderBy('created_at', 'ASC')
                    ->limit(1)
                    ->delete();
            }

        }

    }


    public function updated(htmlResponce $model)
    {
        //
    }

    public function deleted(htmlResponce $model)
    {
        //
    }

    public function restored(htmlResponce $model)
    {
        //
    }

    public function forceDeleted(htmlResponce $model)
    {
        //
    }
}
