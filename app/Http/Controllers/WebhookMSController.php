<?php

namespace App\Http\Controllers;

use App\Clients\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Models\AutomationModel;
use App\Services\webhook\AutomatingServices;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookMSController extends Controller
{
    private AutomatingServices $automatingServices; // Переименуем переменную для соблюдения стандартов именования

    public function __construct(AutomatingServices $automatingServices)
    {
        $this->automatingServices = $automatingServices; // Внедрим зависимость через конструктор
    }

    /**
     * @throws GuzzleException
     */
    public function customerorder(Request $request): JsonResponse
    {
        $auditContext = $request->auditContext;
        $events = $request->events;
        $accountId = $events[0]['accountId'];

        if (empty($auditContext)) return $this->jsonResponseWithMoment(100, "2023-00-00 00:00:00", "Отсутствует auditContext, (изменений не было), скрипт прекращён!");
        if (empty($events[0]['updatedFields'])) return $this->jsonResponseWithMoment(101, $auditContext['moment'], "Отсутствует updatedFields, (изменений не было), скрипт прекращён!");


        $settings = app(getSettingVendorController::class, ['accountId' => $accountId]);
        $msClient = new MsClient($settings->TokenMoySklad);

        $multiDimensionalArray = AutomationModel::where('accountId', $accountId)
            ->select('accountId', 'entity', 'status', 'payment', 'saleschannel', 'project')
            ->get()
            ->toArray();

        if (empty($multiDimensionalArray)) return $this->jsonResponseWithMoment(102, $auditContext['moment'], "Отсутствует настройки автоматизации, скрипт прекращён!");


        try {
            $objectBody = $msClient->get($events[0]['meta']['href']);
            $state = $msClient->get($objectBody->state->meta->href);
        } catch (BadResponseException $e) {
            return $this->jsonResponseWithMoment(102, $auditContext['moment'], $e->getMessage());
        }

        if (property_exists($objectBody, 'attributes')) {
            foreach ($objectBody->attributes as $item) {
                if ($item->name == 'Фискализация (ТИС Prosklad)' and $item->value) return $this->jsonResponseWithMoment(104, $auditContext['moment'], "Фискальный чек уже создан");

            }
        }

        $arraySetProEntity = [];
        if ($events[0]['meta']['type'] == 'customerorder') $arraySetProEntity = ["0",];
        elseif ($events[0]['meta']['type'] == 'demand') $arraySetProEntity = ["1",];
        elseif ($events[0]['meta']['type'] == 'salesreturn') $arraySetProEntity = ["2",];


        foreach ($multiDimensionalArray as $item) {
            $start = ['entity' => in_array($item['entity'], $arraySetProEntity), 'state' => false, 'saleschannel' => false, 'project' => false];

            if ($state->id == $item['status']) $start['state'] = in_array("state", $events[0]['updatedFields']);

            if ($item['status'] == "0") $start['state'] = true;


            $hasProject = $item['project'] != "0" && property_exists($objectBody, 'project');
            $start['project'] = $hasProject ? $this->checkProject($item, $objectBody, $msClient) : $item['project'] == '0';

            $hasSalesChannel = $item['saleschannel'] != "0" && property_exists($objectBody, 'salesChannel');
            $start['saleschannel'] = $hasSalesChannel ? $this->checkSalesChannel($item, $objectBody, $msClient) : $item['saleschannel'] == '0';


            if ($this->allValuesTrue($start)) {
                return response()->json([
                    'code' => 200,
                    'status' => 'Инициализация в сервисе',
                    'message' => $this->automatingServices->initialization($objectBody, $item),
                ]);
            }
        }

        return $this->jsonResponseWithMoment(105, $auditContext['moment'], "Конец скрипта, прошел по foreach, не нашел нужный скрипт");
    }

    private function checkProject($item, $objectBody, $msClient)
    {
        foreach (array_filter(explode('/', $item['project'])) as $_item) {
            if ($msClient->get($objectBody->project->meta->href)->id == $_item) {
                return true;
            }
        }
        return false;
    }

    private function checkSalesChannel($item, $objectBody, $msClient)
    {
        foreach (array_filter(explode('/', $item['saleschannel'])) as $_item) {
            if ($msClient->get($objectBody->salesChannel->meta->href)->id == $_item) {
                return true;
            }
        }
        return false;
    }


    private function jsonResponseWithMoment(int $code, string $moment, string $message): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => [
                "ERROR ==========================================",
                "[" . $moment . "] - Начала выполнение скрипта",
                "[" . date('Y-m-d H:i:s') . "] - Конец выполнение скрипта",
                "===============================================",
                $message,
            ],
        ]);
    }

    private function allValuesTrue(array $start): bool
    {
        return count(array_unique($start)) === 1 && end($start) === true;
    }
}
