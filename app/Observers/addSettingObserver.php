<?php

namespace App\Observers;


use App\Models\addSettingModel;
use Illuminate\Support\Facades\DB;

class addSettingObserver
{
    public function created(addSettingModel $model)
    {

        $accountIds = addSettingModel::all('accountId');

        foreach($accountIds as $accountId){

            $query = addSettingModel::query();
            $logs = $query->where('accountId',$accountId->accountId)->get();
            if(count($logs) > 1){
                DB::table('add_setting_models')
                    ->where('accountId','=',$accountId->accountId)
                    ->orderBy('created_at', 'ASC')
                    ->limit(1)
                    ->delete();
            }

        }

    }


    public function updated(addSettingModel $model)
    {
        //
    }

    public function deleted(addSettingModel $model)
    {
        //
    }

    public function restored(addSettingModel $model)
    {
        //
    }

    public function forceDeleted(addSettingModel $model)
    {
        //
    }

}
