<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Services\workWithBD\DataBaseService;
use Illuminate\Http\Request;

class getPersonal extends Controller
{
    var $accountId;
    var $email;
    var $name;
    var $status;

    public function __construct($accountId)
    {
        $app = DataBaseService::showPersonal($accountId);
        $this->accountId = $app['accountId'];
        $this->email = $app['email'];
        $this->name = $app['name'];
        $this->status = $app['status'];

    }
}
