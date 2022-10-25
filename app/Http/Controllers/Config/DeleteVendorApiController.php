<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeleteVendorApiController extends Controller
{
    public function delete($accountId){

        $path = public_path().'/Config/data/'.$accountId.'.json';
        unlink($path);

    }
}
