<?php

namespace App\Http\Controllers\Web;

use App\Clients\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Models\AutomationModel;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class AutomationController extends Controller
{

    public function getAutomation(Request $request, $accountId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        if ($request->isAdmin == "NO") { return redirect()->route('indexNoAdmin', ["accountId" => $accountId, "isAdmin" => $request->isAdmin]); }

        if (isset($request->message)) {
            $message = $request->message;
            if ($message == "Настройки сохранились") {
                $class = "mt-1 alert alert-success alert-dismissible fade show in text-center";
            } else $class = "mt-1 alert alert-warning alert-danger fade show in text-center";
        } else {
            $message = '';
            $class = '';
        };

        $Setting = new getSettingVendorController($accountId);
        $Client = new MsClient($Setting->TokenMoySklad);

        try {
            $customerorder = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata');
            $demand = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata');
            $salesreturn = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/salesreturn/metadata');
        } catch (BadResponseException $e){
            return view('setting.error', [
                'accountId' => $accountId,
                'isAdmin' => $request->isAdmin,
                'message' => $e->getResponse()->getBody()->getContents()
            ]);
        }

        $body_project = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/project');
        $body_saleschannel = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/saleschannel');

        $dontChoose = json_decode(json_encode(['id'=>'0', 'name'=>'Не выбирать']));

        if (!property_exists($customerorder,'states')) { $customerorder = $dontChoose;
        } else { $customerorder = $customerorder->states; array_unshift($customerorder, $dontChoose); }
        if (!property_exists($demand,'states')) { $demand = $dontChoose;
        } else { $demand = $demand->states; array_unshift($demand, $dontChoose); }
        if (!property_exists($salesreturn,'states')) { $salesreturn = $dontChoose;
        } else { $salesreturn = $salesreturn->states; array_unshift($salesreturn, $dontChoose); }

        if (!$body_project->meta->size > 0) { $body_project = $dontChoose;
        } else { $body_project = $body_project->rows; array_unshift($body_project, $dontChoose); }

        if (!$body_saleschannel->meta->size > 0) { $body_saleschannel = $dontChoose;
        } else { $body_saleschannel = $body_saleschannel->rows; array_unshift($body_saleschannel, $dontChoose); }


        $body_meta_status = [
            'customerorder' => $customerorder,
            'demand' => $demand,
            'salesreturn' => $salesreturn,
        ];
        $body_meta_project = [
            'customerorder' => $body_project,
            'demand' => $body_project,
            'salesreturn' => $body_project,
        ];
        $body_meta_saleschannel = [
            'customerorder' => $body_saleschannel,
            'demand' => $body_saleschannel,
            'salesreturn' => $body_saleschannel,
        ];


        $multiDimensionalArray = (AutomationModel::where('accountId', $accountId)->get())->map(function ($record) {
            return [
                'accountId' => $record->accountId,
                'entity' => $record->entity,
                'status' => $record->status,
                'payment' => $record->payment,
                'saleschannel' => $record->saleschannel,
                'project' => $record->project,
            ];
        })->toArray();

        return view('setting.Automation.Automation', [
            'arr_meta' => $body_meta_status,
            'arr_project' => $body_meta_project,
            'arr_saleschannel' => $body_meta_saleschannel,

            'SavedCreateToArray' => $multiDimensionalArray,

            "message" => $message,
            "class" => $class,

            "accountId" => $accountId,
            "isAdmin" => $request->isAdmin,
        ]);
    }


    public function postAutomation(Request $request, $accountId): \Illuminate\Http\RedirectResponse
    {
        $Setting = new getSettingVendorController($accountId);

        $dataFromRequest = $request->all();

        $groupedData = [];

        foreach ($dataFromRequest as $key => $value) {
            if ($key !== '_token' && $key !== 'isAdmin') {
                $index = $this->getIndexFromKey($key);
                $field = str_replace("_{$index}", '', $key);
                $groupedData[$index][$field] = $value;
            }
        }

        $existingRecords = AutomationModel::where('accountId', $accountId)->get();

        if (!$existingRecords->isEmpty()) {
            foreach ($existingRecords as $record) {
                $record->delete();
            }
        }

        foreach ($groupedData as $data) {
            // Создаем экземпляр модели
            $model = new AutomationModel();

            // Устанавливаем значения полей из группированных данных
            $model->accountId = $accountId; // Примерно такая же логика для других полей
            $model->entity = $data['entity'] ?? '';
            $model->status = $data['status'] ?? '';
            $model->payment = $data['payment'] ?? '';
            $model->saleschannel = $data['saleschannel'] ?? '';
            $model->project = $data['project'] ?? '';

            // Сохраняем модель в базе данных
            $model->save();
        }






        try {
            $Client = new MsClient($Setting->TokenMoySklad);
            $url_check ='https://smarttis.kz' ;
            $Webhook_check = true;
            $Webhook_body = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/webhook/')->rows;
            if ($Webhook_body != []){
                foreach ($Webhook_body as $item){
                    if ($item->url == $url_check){
                        $Webhook_check = false;
                    }
                }
            }
            if ($Webhook_check) {
                foreach ($Client->get('https://online.moysklad.ru/api/remap/1.2/entity/webhook/')->rows as $item){
                    if (strpos(($item->url), "https://smarttis.kz") !== false) {
                        $Client->delete($item->meta->href,null);
                    }
                }

                $Client->post('https://online.moysklad.ru/api/remap/1.2/entity/webhook/', [
                    'url' => 'https://smarttis.kz/api/webhook/customerorder',
                    'action' => "UPDATE",
                    'entityType' => 'customerorder',
                    'diffType' => "FIELDS",
                ]);
                $Client->post('https://online.moysklad.ru/api/remap/1.2/entity/webhook/', [
                    'url' => 'https://smarttis.kz/api/webhook/demand',
                    'action' => "UPDATE",
                    'entityType' => 'demand',
                    'diffType' => "FIELDS",
                ]);
                $Client->post('https://online.moysklad.ru/api/remap/1.2/entity/webhook/', [
                    'url' => 'https://smarttis.kz/api/webhook/salesreturn',
                    'action' => "UPDATE",
                    'entityType' => 'salesreturn',
                    'diffType' => "FIELDS",
                ]);
            }


            $message = "Настройки сохранились";
        } catch (BadResponseException $e){
            $message = json_decode($e->getResponse()->getBody()->getContents())->errors[0]->error;
        }


        return redirect()->route('getAutomation', ['accountId' => $accountId, 'isAdmin' => $request->isAdmin, 'message' => $message]);
    }
    function getIndexFromKey($key): int
    {
        return (int) explode('_', $key)[1];
    }

}

