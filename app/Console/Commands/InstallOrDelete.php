<?php

namespace App\Console\Commands;

use App\Http\Controllers\Config\getSettingVendorController;
use App\Services\Settings\SettingsService;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;

class InstallOrDelete extends Command
{

    protected $signature = 'InstallOrDelete:get';

    protected $description = 'Command description';

    private SettingsService $settingsService;

    /**
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        parent::__construct();
    }


    public function handle()
    {
        $allSettings = $this->settingsService->getSettings();
        //dd($allSettings);
        $accountIds = [];
        foreach ($allSettings as $setting){
            $accountIds[] = $setting->accountId;
        }
        //dd($accountIds);

        if (count($accountIds) == 0) return;

        $client = new Client();
        //$url = "http://tus/api/installOfDelete";
        $url = "https://smarttis.kz/api/installOfDelete";
        $countFailSettings = 0;

        $promises = (function () use ($accountIds, $client, $url, &$countFailSettings){
            foreach ($accountIds as $accountId){
                $settings = new getSettingVendorController($accountId);

                yield $client->postAsync($url,[
                    'form_params' => [
                        "tokenMs" => $settings->TokenMoySklad,
                        "accountId" => $settings->accountId,
                    ],
                ]);
            }
        })();

        $eachPromise = new EachPromise($promises,[
            'concurrency' => count($accountIds) - $countFailSettings,
            'fulfilled' => function (Response $response) {
                if ($response->getStatusCode() == 200) {
                    dd($response->getBody()->getContents());
                } else {
                    dd($response);
                }
            },
            'rejected' => function ($reason) {
                dd($reason);
            }
        ]);

        $eachPromise->promise()->wait();
    }

}
