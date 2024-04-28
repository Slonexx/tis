<?php
$nameIntegration = "Учёт.ТИС";
$nameFolder = "";

$MsURL = "https://api.moysklad.ru/api/remap/1.2/";
$intURL = env('APP_URL_INTEGRATION').'api/';
return [
    /** => УРЛ МОЕГО СКЛАДА */
    'subscription' =>  "{$MsURL}accountSettings/subscription",


    /** => УРЛ ИНТЕГРАЦИИ*/
    'int_login' =>  "{$intURL}auth/login/",


    /** => legacy */
    'url' => env('APP_URL'),
    'url_' => env('APP_URL_INTEGRATION'),
    'appId' => env('APP_ID'),
    'appUid' => env('APP_UID'),
    'secretKey' => env('SECRET_KEY'),


    /** => VENDOR API */
    'moyskladVendorApiEndpointUrl' =>  'https://apps-api.moysklad.ru/api/vendor/1.0',
    'moyskladJsonApiEndpointUrl' =>  'https://api.moysklad.ru/api/remap/1.2',

    /** => ATTRIBUTES */
    "esf_eawr" => (object) [
        "name" => "esf_eawr",
        "type" => "customEntityMeta",
        'description' => 'Это дополнительное поле в интеграции'.$nameIntegration.'. Важно: не удаляйте его.'
    ],

];
