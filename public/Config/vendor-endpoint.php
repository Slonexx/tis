<?php

require_once 'lib.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);



$pp = explode('/', $path);
$n = count($pp);
$appId = $pp[$n - 2];
$accountId = $pp[$n - 1];

$url = 'https://smarttis.kz//api/moysklad/vendor/1.0/apps/'.$appId.'/'.$accountId;
$curl = curl_init($url);

// Установка опций для сессии cURL
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method); // Устанавливаем метод PUT
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(file_get_contents('php://input'))); // Устанавливаем тело запроса

// Выполнение запроса
$response = curl_exec($curl);

// Закрытие сессии cURL
curl_close($curl);


/*$app = AppInstanceContoller::load($appId, $accountId);
$replyStatus = true;

switch ($method) {
    case 'PUT':
        $requestBody = file_get_contents('php://input');

        $data = json_decode($requestBody);

        $appUid = $data->appUid;
        $accessToken = $data->access[0]->access_token;

        if (!$app->getStatusName()) {
            $app->TokenMoySklad = $accessToken;
            $app->status = AppInstanceContoller::SETTINGS_REQUIRED;
            $app->persist();

        }
        $url = 'https://smarttis.kz/setAttributes/' . $accountId . '/' . $accessToken;
        $install = file_get_contents($url);
        break;
    case 'GET':
        break;
    case 'DELETE':
        //Тут так же
        $replyStatus = false;
        break;
}

if (!$app->getStatusName()) {
    http_response_code(404);
} else if ($replyStatus) {
    header("Content-Type: application/json");
    echo '{"status": "' . $app->getStatusName() . '"}';
}*/


