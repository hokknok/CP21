<?php

namespace Ninja\Helper;

use Bitrix\Main\Type;


class Date
{
    /**
     * Метод переводит дату из формата сайта в человеческий вид
     * @param string $date Дата в формате текущего сайта
     * @param string $format Формат в который необходимо её пробразовать
     * @return string Дата в отформатированном виде
     */
    public static function formatDateHuman(string $date, string $format): string
    {
        $result = FormatDateFromDB($date, $format);
        $result = preg_replace('/^0/', '', $result);
        $result = str_replace(' ', '&nbsp;', $result);

        if (LANGUAGE_ID === 'ru' && false !== strpos($format, 'MMMM')) {
            $result = mb_strtolower($result, 'UTF-8');
        }

        return $result;
    }


    public static function currentDate(): Type\Date
    {
        return Type\Date::createFromTimestamp(time());
    }


    public static function otherDateByDay(string $day): Type\Date
    {
        $time = AddToTimeStamp(['DD' => $day]);
        return Type\Date::createFromTimestamp($time);
    }

}
