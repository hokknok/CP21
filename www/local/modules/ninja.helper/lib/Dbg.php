<?php

namespace Ninja\Helper;


use CEventLog;

class Dbg {

    const EVENT_TYPE_SECURITY = 1;
    const EVENT_TYPE_ERROR = 2;
    const EVENT_TYPE_WARNING = 3;
    const EVENT_TYPE_INFO = 4;
    const EVENT_TYPE_DEBUG = 5;

    /**
     * Дебагер
     *
     * @param $data - Массив
     * @param $die - die()
     * @param $all - Видно всем пользователям, иначе только администраторам
     */
    public static function show($data, bool $die = false, bool $all = false): void
    {
        global $USER;

        if ($USER->IsAdmin() || ($all == true)) {
            echo '<br clear="all" />';
            echo '<pre style="text-align: left;">';
            print_r($data);
            echo '</pre>';
        }

        if ($die) {
            die;
        }
    }

    /**
     * Функция добавляет новую запись в log-файл.
     *
     * @param string $str
     */
    public static function addLog(string $str): void
    {
        AddMessage2Log(print_r($str, true));
    }

    /**
     * @param int $type Тип ошибки, одна из констант self::EVENT_TYPE_SECURITY ...
     * @param array $params Массив с параметрами:
     * - auditTypeId - собственный ID типа события
     * - moduleId - модуль, который записывает в лог
     * - id - идентификатор связанного объекта
     * - message - сообщение, которое будет отображаться в логе
     */
    public static function addEventLog(int $type, array $params): void
    {
        CEventLog::Add([
            'SEVERITY'      => $type,
            'AUDIT_TYPE_ID' => $params['auditTypeId'] ?? '',
            'MODULE_ID'     => $params['moduleId'] ?? 'ninja.project',
            'ITEM_ID'       => $params['id'] ?? '',
            'DESCRIPTION'   => $params['message'] ?? '',
        ]);
    }
}
