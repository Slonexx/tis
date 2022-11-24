<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\BD\getPersonal;
use App\Http\Controllers\Controller;
use App\Services\workWithBD\DataBaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeleteVendorApiController extends Controller
{
    public function delete($accountId){

        $personal = new getPersonal($accountId);
        DataBaseService::updatePersonal($personal->accountId, $personal->email, $personal->name, 'деактивированный');

        $path = public_path().'/Config/data/'.$accountId.'.json';
        unlink($path);

    }
}
