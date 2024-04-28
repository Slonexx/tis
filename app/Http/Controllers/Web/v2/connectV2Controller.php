<?php

namespace App\Http\Controllers\Web\v2;

use App\Clients\v2\newKassClient;
use App\Clients\v2\newMsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Models\mainSetting;
use App\Models\v2\listModel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class connectV2Controller extends Controller
{
    public function view(Request $request, $uid, $accountId ): Factory|View|Application
    {
        $isAdmin = $request->isAdmin;
        $message = $request->message ?? '';
        $class_message = $request->class_message ?? 'is-info';
        $model = (listModel::find($uid))->toArray();

        //dd($request->all(), $uid, $accountId);


        return view('setting.v2.items.update', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'uid' => $uid,

            'model' => $model,


            "message" => $message,
            "class_message" => $class_message,
        ]);
    }

    public function save(Request $request, $uid, $accountId)
    {
        $isAdmin = 'ALL';

        $data = [
            'email' => $request->email ?? '',
            'password' => $request->pass ?? '',
        ];

        $clientKs = new newKassClient();
        $post = $clientKs->newPost( Config::get("global.int_login"), $data );
        if (!$post->status) {
            $message = $post->message;
            if (property_exists($post->data, 'non_field_errors')) $message = $post->data->non_field_errors;
            return $this->coll_back_to_route('create', $accountId, $message, 'is-danger');
        }

        $model = listModel::model_in_update($uid, $accountId, $data, $post->data);
        if (!$model->status) return $this->coll_back_to_route('create', $accountId, $model->message, 'is-danger');


        return to_route('kassa', [
            'accountId' => $accountId,
            'uid' => $uid,
            'isAdmin' => $isAdmin,

            "message" => $request->message ?? '',
            "class_message" => $request->class_message ?? 'is-info',
        ]);
    }


    private function coll_back_to_route($name, $accountId, $message = '', $class_message = ''){
        return to_route($name, [
            'accountId' => $accountId,
            'isAdmin' => 'ALL',

            "message" => $message,
            "class_message" => $class_message,
        ]);
    }
}
