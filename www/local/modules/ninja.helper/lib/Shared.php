<?php

namespace Ninja\Helper;

class Shared
{
    /**
     * Метод возвращает нужную словоформу чистилительного по колчиеству
     * @param int $amount Количество
     * @param array $wordList Массив словоформ (1, 2, 5)
     * @return string Результат
     */
    public static function amount2word(int $amount, array $wordList): ?string
    {
        $amount = $amount % 100;
        if ($amount > 19)
        {
            $amount = $amount % 10;
        }
        switch ($amount)
        {
            case 1: { return($wordList[0]); }
            case 2: case 3: case 4: { return($wordList[1]); }
            default: { return($wordList[2]); }
        }
    }


    /**
     * Метод возвращает число в формате суммы
     *
     * @param float|null $number Число для вывода суммы
     * @param int $decimal Число знаков после запятой, например, не более двух
     * @param string $groupSeparator Разделитель разрядов
     * @param string $fractionSeparator Разделитель дробных значений
     * @return string
     */
    public static function formatPrice(?float $number, int $decimal = 2, string $groupSeparator = '&#8201;', string $fractionSeparator = ',')
    {
        if (LANGUAGE_ID === 'en') {
            $fractionSeparator = '.';
            $groupSeparator    = ',';
        }

        $roundNumber   = round($number, $decimal);
        $numberParts   = explode('.', $roundNumber);
        $decimalDefine = $numberParts[1] ? strlen($numberParts[1]) : 0;
        $separatorTmp  = ($number >= 10000) ? '#' : false;

        $num = number_format($number, $decimalDefine, $fractionSeparator, $separatorTmp);
        $num = str_replace('#', $groupSeparator, $num);

        if (!$num) {
            $num = 0;
        }

        return $num;
    }
}