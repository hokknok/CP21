<?php

// Входные параметры
use Ninja\Project\Application;
use Ninja\Project\ParamType;

ob_start();

$input = file_get_contents('php://input');
$requestData = $input ? json_decode($input, true) : $_REQUEST;

$requestData['lang'] = (string) ($requestData['lang'] ?? 'ru');

header('Access-Control-Allow-Origin: *');

// Подключение Bitrix
define('LANGUAGE_ID', $requestData['lang']);
require $_SERVER['DOCUMENT_ROOT'] . '/local/tools/include-bitrix.php';

$request = ParamType::getInstance();
foreach ($requestData as $key => $val) {
    $request->set($key, $val);
}

$action = explode('.', $request->get('action'));
if (count($action) === 2) {
    // ob_get_clean();

    // Определение результата
    Application::run($action[0], $action[1], $request);
}
