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
        try {
            DataBaseService::createDocumentSetting($accountId, $request->paymentDocument);
            $message["alert"] = " alert alert-success alert-dismissible fade show in text-center ";
            $message["message"] = "Настройки сохранились!";
        } catch (\Throwable $e){
            $message["alert"] = " alert alert-danger alert-dismissible fade show in text-center ";
            $message["message"] = "Ошибка " . $e->getCode();
        }

        return view('setting.document', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,

            'paymentDocument' => $request->paymentDocument,

            'message' => $message,
        ]);
    }

}