<?php


namespace Ninja\Helper;


use Bitrix\Currency\CurrencyLangTable;
use Bitrix\Currency\CurrencyRateTable;
use Bitrix\Currency\CurrencyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CCurrencyLang;

class Currency
{
    /**
     * Возвращает список валют
     *
     * @param array $params
     * @return array
     * @throws LoaderException
     * @throws ArgumentException
     */
    public static function getList(array $params = []): array
    {
        Loader::includeModule('currency');
        return CurrencyTable::getList($params)->fetchAll();
    }


    /**
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getListFullData(string $lang, array $params = []): array
    {
        $getList = self::getList($params);
        $getLangList = CurrencyLangTable::getList()->fetchAll();

        foreach ($getList as $key => $item) {
            $code = $item['CURRENCY'];
            $getLang = array_filter($getLangList, static function ($item) use ($code, $lang) {
                return $item['CURRENCY'] === $code && $item['LID'] === $lang;
            });

            if (!empty($getLang)) {
                $getLang = reset($getLang);

                $getList[$key]['NAME'] = $getLang['FULL_NAME'];
                $getList[$key]['SYMBOL'] = $getLang['FORMAT_STRING'];
            }
        }

        return $getList;
    }


    /**
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getBase(): array
    {
        return current(array_filter(self::getList(), static function ($item) {
            return $item['BASE'] === 'Y';
        }));
    }


    /**
     * Возвращает список курсов валют
     *
     * @param array $params - стандартный параметр для ORM D7
     * @return array
     * @throws LoaderException
     * @throws ArgumentException
     */
    public static function getRateList(array $params = []): array
    {
        Loader::includeModule('currency');

        return CurrencyRateTable::getList($params)->fetchAll();
    }


    /**
     * Добавляет курсы валют
     *
     * @param array $items
     * @return bool
     * @throws LoaderException
     */
    public static function addRateList(array $items): bool
    {
        Loader::includeModule('currency');

        $rates = CurrencyRateTable::createCollection();
        foreach ($items as $item) {
            $rate = CurrencyRateTable::createObject();
            foreach ($item as $keyProp => $valProp) {
                $rate->set($keyProp, $valProp);
            }

            $rates[] = $rate;
        }

        $rates->save();

        return true;
    }


    /**
     * Форматирует число $value в соответствии с назначенным форматом в админ панели Битрикса для валюты $currency
     *
     * @param $value
     * @param string $currency
     * @param bool $useTemplate
     * @return string
     * @throws LoaderException
     * @throws ArgumentException
     */
    public static function format($value, string $currency, bool $useTemplate = true): string
    {
        Loader::includeModule('currency');

        if (!is_numeric($value)) {
            throw new ArgumentException('Not correct argument $value');
        }

        return CCurrencyLang::CurrencyFormat($value, $currency, $useTemplate);
    }
}
