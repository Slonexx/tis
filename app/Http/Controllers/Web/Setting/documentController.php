<?php

namespace App\Http\Controllers\Web\Setting;

use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Controller;
use App\Services\workWithBD\DataBaseService;
use Illuminate\Http\Request;

class documentController extends Controller
{

    public function getDocument(Request $request, $accountId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $isAdmin = $request->isAdmin;

        $SettingBD = new getMainSettingBD($accountId);
        $tokenMs = $SettingBD->tokenMs;
        $paymentDocument = $SettingBD->paymentDocument;
        if ($tokenMs == null){
            return view('setting.no', [
                'accountId' => $accountId,
                'isAdmin' => $isAdmin,
            ]);
        }
        if ($paymentDocument == null) {
            $paymentDocument = "0";
        }

        return view('setting.document', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,

            'paymentDocument' => $paymentDocument,
        ]);
    }


    public function postDocument(Request $request, $accountId){
        $isAdmin = $request->isAdmin;

        $SettingBD = new getMainSettingBD($accountId);

        try {
            DataBaseService::createDocumentSetting($accountId,$SettingBD->idKassa, $SettingBD->idDepartment, $request->paymentDocument);
        } catch (\Throwable $e){
            $message["alert"] = " alert alert-danger alert-dismissible fade show in text-center ";
            $message["message"] = "Ошибка " . $e->getCode();
            return redirect()->route('getWorker', [ 'accountId' => $accountId, 'isAdmin' => $isAdmin, 'message'=>$message ]);
        }

        return redirect()->route('getWorker', [ 'accountId' => $accountId, 'isAdmin' => $isAdmin, 'message'=>"" ]);
    }

}
