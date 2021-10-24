<?php

namespace Ninja\Helper\Iblock;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CIBlock;
use CIBlockType;
use Ninja\Helper\Arr;
use Ninja\Helper\CacheManager;
use Ninja\Helper\Dbg;
use Ninja\Helper\TypeConvert;
use Ninja\Project\Application;
use RuntimeException;


class Iblock
{
    /**
     * @param array|null $params
     * @param callable|null $callback
     *
     * @return array
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getListFromCache(array $params = null, callable $callback = null): array
    {
        if (empty($params)) {
            $params = [
                'FILTER' => [
                    'ACTIVE'            => 'Y',
                    'CHECK_PERMISSIONS' => 'N',
                ],
                'SELECT' => [
                    'ID:int>id',
                    'CODE:string>code',
                ],
            ];
        }

        $skipCache = ($params['__SKIP_CACHE'] === true);
        $cachePath = '/iblocks/';

        return CacheManager::getDataCache($cachePath, $params, static function (&$itemList) use ($params, $callback) {
                $itemList = self::getList($params);

                if (is_callable($callback)) {
                    $callback($itemList);
                }
            }, Application::DEF_TTL, $skipCache);
    }


    /**
     * Функция возвращает массив с инфоблоками
     *
     * @param array $params Параметры выборки
     *
     * @return array
     * @throws LoaderException
     */
    public static function getList(array $params): array
    {
        self::checkModule();

        // Определение полей выборки
        $typeConverter = new TypeConvert($params['SELECT'] ?: ['ID:int>id']);

        // Определение направления сортировки
        $params['ORDER'] = $params['ORDER'] ?: [];

        // Определение фильтра
        $params['FILTER'] = $params['FILTER'] ?: [];

        // Возвращать ли количество элементов
        $params['CNT'] = $params['CNT'] ? true : false;

        // Выборка результата из базы
        $res = CIBlock::GetList($params['ORDER'], $params['FILTER'], $params['CNT']);

        // Выборка результата из базы
        $elements = [];
        while ($item = $res->Fetch()) {
            $key            = $item['ID'];
            $elements[$key] = $item;
        }

        // Результирующий массив
        $result = [];

        // Приведем массив к нужным типам данных
        if ($typeConverter->getTypes()) {
            $result = $typeConverter->convertDataTypes($elements);
        }

        return $result;
    }


    /**
     * Функция возвращает массив с типаим инфоблоков
     *
     * @param array $params Параметры выборки
     *
     * @return array
     * @throws LoaderException
     */
    public static function getTypes(array $params = []): array
    {
        self::checkModule();

        // Определение полей выборки
        $typeConverter = new TypeConvert($params['SELECT'] ?: ['NAME:string>name']);

        // Определение направления сортировки
        $params['ORDER'] = $params['ORDER'] ?: ['SORT' => 'ASC'];

        // Определение фильтра
        $params['FILTER'] = $params['FILTER'] ?: [];

        // Выборка результата из базы
        $res      = CIBlockType::GetList($params['ORDER'], $params['FILTER']);
        $elements = [];
        while ($item = $res->Fetch()) {
            if ($ibType = CIBlockType::GetByIDLang($item['ID'], LANG)) {
                $item['NAME'] = htmlspecialcharsEx($ibType['NAME']);
            }
            $elements[$item['ID']] = $item;
        }

        // Результирующий массив
        $result = [];

        // Приведем массив к нужным типам данных
        if ($typeConverter->getTypes()) {
            $result = $typeConverter->convertDataTypes($elements);
        }

        return $result;
    }



    /**
     * Метод возвращает символьный код инфоблока по его ID
     *
     * @param int $id
     * @return string|null
     */
    public static function getIblockCodeById(int $id): ?string
    {
        $code = Arr::findInArr(self::getListFromCache(), 'id', $id, 'code');
        return $code !== false ? $code : null;
    }


    /**
     * Метод возвращает ID инфоблока по коду
     *
     * @param string $code
     * @return int|null
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getIblockIdByCode(string $code): ?int
    {
        $id = Arr::findInArr(self::getListFromCache(), 'code', $code, 'id');
        return $id !== false ? $id : null;
    }


    /**
     * Метод выводит список свойств инфоблока.
     *
     * @param $ibId - ID инфоблока
     *
     * @return array
     * @throws LoaderException
     */
    /*public static function getPropList(int $ibId): array
    {
        self::checkModule();

        $ibCode = self::getIblockCodeById($ibId);
        $params = [
            'IBLOCK_ID' => $ibId,
        ];
        $cachePath = '/' . $ibCode . '/properties/';
        return CacheManager::getDataCache($cachePath, $params,  static function () use ($params) {
            $result = [];

            $resPropDb = CIBlock::GetProperties($params['IBLOCK_ID']);
            while($arrPropDb = $resPropDb->Fetch()) {
                $propId = $arrPropDb['ID'];
                $result[$propId] = $arrPropDb;
            }

            return $result;
        });
    }*/


    /**
     * Проверяет наличие модуля в системе
     * @throws LoaderException
     */
    public static function checkModule(): void {
        if(!Loader::includeModule('iblock')) {
            throw new RuntimeException('Для работы API необходимо наличие модуля «iblock»');
        }
    }
}
