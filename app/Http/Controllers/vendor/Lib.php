<?php

namespace App\Http\Controllers\vendor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class Lib extends Controller
{

    private const UNKNOWN = 0;
    public const SETTINGS_REQUIRED = 1;
    public const ACTIVATED = 100;

    public mixed $accountId;
    public mixed $TokenMoySklad;
    public mixed $appId;

    public mixed $status = Lib::UNKNOWN;

    public function __construct($appId, $accountId)
    {
        $this->appId = $appId;
        $this->accountId = $accountId;
    }

    public function getStatusName(): ?string
   {
       return match ($this->status) {
           self::SETTINGS_REQUIRED => 'SettingsRequired',
           self::ACTIVATED => 'Activated',
           default => null,
       };
   }

    function persist(): void
    {
        @mkdir('data');
        file_put_contents($this->filename(),  serialize($this));
    }

    private function filename(): string
    {
        return self::buildFilename($this->accountId);
    }

    private static function buildFilename($accountId): string
    {
        return "data/$accountId.json";
    }

    static function loadApp($accountId): Lib {
        $app = self::load(Config::get("Global.appUid"), $accountId);
        if (! isset($app->TokenMoySklad) ){
            $app->TokenMoySklad = null ;
        }

        return $app;
    }


    static function load($appId, $accountId): Lib
    {
        $App = new Lib($appId, $accountId);
        $data = @file_get_contents(self::buildFilename($accountId));
        if ($data === false) {
            return $App ;
        } else {
            $app = json_decode(json_encode( unserialize($data) ));
        }

        $App->parsing($app);

        return $App;
    }

    public function parsing($json): void
    {
        $this->accountId = $json->accountId;
        $this->TokenMoySklad = $json->TokenMoySklad;
        $this->appId = $json->appId;
        $this->status = $json->status;
    }


}
