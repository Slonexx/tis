<?php

namespace App\Models\v2;

use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class listModel extends Model
{
    protected $table = "info_list";

    public $incrementing = false;

    protected $guarded = [];

    public static function model_in_create($accountId, array $data, object $post): object
    {
        $model = new listModel();
        $uid  = Uuid::uuid4();
        $model->id = $uid->toString();
        $model->accountId = $accountId;
        $model->email = $data['email'];
        $model->pass = $data['password'];
        $model->auth = $post->auth_token;

        $model->companyName = $post->user_kassas->company->name;
        $model->full_name = $post->full_name;
        $model->isActivity = true;
        try {
            $model->save();
            return (object)  ['status' => true, 'id'=>$uid->toString()];
        } catch (BadResponseException $e){
            return (object) ['status' => false, 'message' => $e->getMessage()];
        }

    }

    public static function model_in_update($uid, $accountId, array $data, object $post): object
    {
        $model = listModel::find($uid);

        $model->accountId = $accountId;
        $model->email = $data['email'];
        $model->pass = $data['password'];
        $model->auth = $post->auth_token;

        $model->companyName = $post->user_kassas->company->name;
        $model->full_name = $post->full_name;

        try {
            $model->save();
            return (object)  ['status' => true];
        } catch (BadResponseException $e){
            return (object) ['status' => false, 'message' => $e->getMessage()];
        }

    }


}
