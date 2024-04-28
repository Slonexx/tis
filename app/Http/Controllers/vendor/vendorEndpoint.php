<?php

namespace App\Http\Controllers\vendor;

use App\Clients\v2\newMsClient;
use App\Http\Controllers\Controller;
use App\Models\mainSetting;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;

class vendorEndpoint extends Controller
{
    /**
     * @throws GuzzleException
     */
    public function put(Request $request, $apps, $accountId){
        Telescope::tag(function (IncomingEntry $entry) use ($accountId) {
            return [ $accountId, "install_app" ];
        });

        $data = json_decode(json_encode($request->all()));
        $app = Lib::load($apps, $accountId);

        $accessToken = $data->access[0]->access_token;

        if (!$app->getStatusName()) {
            $app->TokenMoySklad = $accessToken;
            $app->status = Lib::SETTINGS_REQUIRED;
            $app->persist();


            $modelQ = mainSetting::accId($accountId);

            if ($modelQ->toArray == null) $model = new mainSetting();
            else $model = $modelQ->query;

            $model->accountId = $accountId;
            $model->tokenMs = $data->access[0]->access_token;
            $model->authtoken = null;

            $model->save();


        }

        if (!$app->getStatusName()) {
            http_response_code(404);
        } else {
            return Response::json([
                'status' => $app->getStatusName()
            ]);
        }
    }

    public function delete(Request $request, $apps, $accountId){

        Telescope::tag(function (IncomingEntry $entry) use ($accountId) {
            return [ $accountId, "install_app" ];
        });

        $data = json_decode(json_encode($request->all()));
        $app = Lib::load($apps, $accountId);

        if (file_exists(public_path().'/data/'.$accountId.'.json')) {
            unlink( public_path().'/data/'.$accountId.'.json');
        }



        if (!$app->getStatusName()) {
            http_response_code(404);
        }
    }

}
