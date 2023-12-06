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

        // Избавимся от прямого создания экземпляра класса getSettingVendorController, воспользуемся внедрением зависимостей
        $setting = app(getSettingVendorController::class, ['accountId' => $accountId]);
        $msClient = new MsClient($setting->TokenMoySklad);

        if (empty($request->auditContext)) {
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage("2023-00-00 00:00:00", "Отсутствует auditContext, (изменений не было), скрипт прекращён!"),
            ]);
        }
        if ( strpos($request->auditContext['uid'], "kaspi-kz.fixcom") !== false ) {
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage("2023-00-00 00:00:00", "Мы не работаем с fix (они плохие)"),
            ]);
        }

        if (empty($events[0]['updatedFields'])) {
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage($auditContext['moment'], "Отсутствует updatedFields, (изменений не было), скрипт прекращён!"),
            ]);
        }


        // Заменим обращение к базе данных с использованием Eloquent ORM, чтобы сократить количество запросов
        $multiDimensionalArray = AutomationModel::where('accountId', $accountId)
            ->select('accountId', 'entity', 'status', 'payment', 'saleschannel', 'project')
            ->get()
            ->toArray();

        if (empty($multiDimensionalArray)) {
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage($auditContext['moment'], "Отсутствует настройки автоматизации, скрипт прекращён!"),
            ]);
        }

        try {
            $objectBody = $msClient->get($events[0]['meta']['href']);
            $state = $msClient->get($objectBody->state->meta->href);
        } catch (BadResponseException $e) {
            Log::error($e); // Борируем ошибку, чтобы отслеживать возможные проблемы
            return response()->json([
                'code' => 203,
                'message' => $this->returnMessage($auditContext['moment'], $e->getMessage()),
            ]);
        }

        if (property_exists($objectBody, 'attributes')) {
            foreach ($objectBody->attributes as $item){
                if ($item->name == 'Фискализация (ТИС)' and $item->value){
                    return response()->json([
                        'code' => 203,
                        'message' => $this->returnMessage($auditContext['moment'], "Фискальный чек уже создан"),
                    ]);
                }
            }
        }

        //dd($msClient->get($objectBody->salesChannel->meta->href));

        foreach ($multiDimensionalArray as $item) {
            $start = ['entity' => false,'state' => false, 'saleschannel' => false, 'project' => false];
            if ($item['entity'] == "0") {
                $start['entity'] = true;
            }
            if ($state->id == $item['status'] and in_array("state", $events[0]['updatedFields'])) {
                $start['state'] = true;
            }

            if ($item['status'] == "0") {
                $start['state'] = true;
            }

            if ($item['project'] != "0" and property_exists($objectBody, 'project')) {

                foreach (array_filter(explode('/', $item['project'])) as $_item) {
                    if ($msClient->get($objectBody->project->meta->href)->id == $_item) {
                        $start['project'] = true;
                    }
                }

            } else {
                $start['project'] = true;
            }
            if ($item['saleschannel'] != "0" and property_exists($objectBody, 'salesChannel')) {

                foreach (array_filter(explode('/', $item['saleschannel'])) as $_item){
                    if ($msClient->get($objectBody->salesChannel->meta->href)->id == $_item) {
                        $start['saleschannel'] = true;
                    }
                }

            } else {
                $start['saleschannel'] = true;
            }

            if ($this->allValuesTrue($start)) {
                return response()->json([
                    'code' => 200,
                    'status' => 'Инициализация в сервисе',
                    'message' => $this->automatingServices->initialization($objectBody, $item),
                ]);
            }
        }

        return response()->json([
            'code' => 203,
            'message' => $this->returnMessage($auditContext['moment'], "Конец скрипта, прошел по foreach, не нашел нужный скрипт"),
        ]);
    }

    private function returnMessage($moment, string $message): array|string
    {
        return [
            "ERROR ==========================================",
            "[" . $moment . "] - Начала выполнение скрипта",
            "[" . date('Y-m-d H:i:s') . "] - Конец выполнение скрипта",
            "===============================================",
            $message,
        ];
    }

    private function allValuesTrue(array $start): bool
    {
        return count(array_unique($start)) === 1 && end($start) === true;
    }
}
