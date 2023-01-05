<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Services\workWithBD\DataBaseService;
use Illuminate\Http\Request;

class getMainSettingBD extends Controller
{
    public $accountId;
    public $tokenMs;
    public $authtoken;
    public $idKassa;
    public $idDepartment;
    public $paymentDocument;
    public $payment_type;

    /**
     * @param $accountId
     */
    public function __construct($accountId)
    {
        $this->accountId = $accountId;

        $BD = DataBaseService::showMainSetting($accountId);
        $this->accountId = $BD['accountId'];
        $this->tokenMs = $BD['tokenMs'];
        $this->authtoken = $BD['authtoken'];


        $json = DataBaseService::showDocumentSetting($accountId);
        $this->idKassa = $json['idKassa'];
        $this->idDepartment = $json['idDepartment'];
        $this->paymentDocument = $json['paymentDocument'];
        $this->payment_type = $json['payment_type'];

    }


}
