<?php

namespace Ninja\Project;

use Exception;
use Ninja\Project\Data\Dt;

class Application
{
    public const DEF_TTL = 3600 * 24 * 7;

    /**
     * Входная точка приложения
     *
     * @param string $componentName
     * @param string $methodName
     * @param ParamType $request
     *
     * @throws Exception
     */
    public static function run(string $componentName, string $methodName, ParamType $request): void
    {
        $result = null;

        switch ($componentName) {
            case 'dt':
                if ($methodName === 'getData') {
                    $dtId = $request->getInt('id') ?: false;
                    $streetId = $request->getInt('street') ?: false;
                    $type = $request->getString('type') ?: false;

                    if (!empty($dtId) && !empty($streetId) && !empty($type)) {
                        $data = Dt::get($dtId, $streetId, $type);
                        $result = Graph\Dt::get($data);
                    }
                }
                break;
        }

        // Возвращаем результат
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
