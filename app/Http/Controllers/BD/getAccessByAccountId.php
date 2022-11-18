<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Services\workWithBD\DataBaseService;
use Illuminate\Http\Request;

class getAccessByAccountId extends Controller
{
    public array $access;

    public function __construct($accountId)
    {
        $app = DataBaseService::getAccessByAccountId($accountId);
        if ($app) foreach ($app as $item){
            $this->access[$item->id] = $item;
        }
        else  $this->access[] = null;

    }

}
