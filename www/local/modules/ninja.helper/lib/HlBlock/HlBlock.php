<?php

namespace Ninja\Helper\HlBlock;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use Ninja\Helper\CacheManager;
use Ninja\Helper\Dbg;
use Ninja\Helper\Exception\HelperException;
use Ninja\Helper\TypeConvert;
use Ninja\Project\Application;
use RuntimeException;


class HlBlock
{
    public static function getListFromCache(string $code, array $params, callable $callback = null): array
    {
        $useTypeConverter = empty($params['useTypeConverter']) ? true : $params['useTypeConverter'];

        $skipCache = ($params['__SKIP_CACHE'] === true);
        $cachePath = '/hlblocks/' . $code . '/';
        $cacheTtl = $params['ttl'] ?? Application::DEF_TTL;

        unset($params['ttl'], $params['useTypeConverter']);

        return CacheManager::getDataCache($cachePath, $params, static function (&$itemList) use ($code, $params, $callback, $useTypeConverter) {
            $itemList = self::getList($code, $params, $useTypeConverter);

            if (is_callable($callback)) {
                $callback($itemList);
            }
        }, $cacheTtl, $skipCache);
    }


    public static function getRowFromCache(string $code, array $params): ?array
    {
        $itemList = self::getListFromCache($code, $params);
        if (empty($itemList)) {
            return null;
        }

        return reset($itemList);
    }


    /**
     * Метод возвращает список элементов highload-блока
     *
     * @param string $code
     * @param array $params - Массив параметров выборки элементов
     * @param bool $useTypeConverter
     *
     * @return array - Массив записей, ключи массива соответствуют ID записей
     * @throws ArgumentException
     * @throws LoaderException
     * @throws SystemException
     */
    public static function getList(string $code, array $params = [], bool $useTypeConverter = true): array
    {
        self::checkModule();

        // Определение полей выборки
        if($useTypeConverter === true) {
            $typeConverter = new TypeConvert($params['select'] ?: []);
            $select        = $typeConverter->getSelect();
        } else {
            $select = $params['select'];
        }

        $entity = self::getEntity($code);

        $asArray = $params['asArray'] ?? false;
        unset($params['asArray']);

        $asXmlId = $params['asXmlId'] ?? false;
        unset($params['asXmlId']);

        $res = $entity::getList(array_merge($params, ['select' => $select]));

        $result = [];
        while ($item = $res->Fetch()) {
            // Переводим дату в строку
            if ($item['UF_DATE']) {
                $item['UF_DATE'] = $item['UF_DATE']->toString();
            }

            // Сохранение массива с привязкой к XML_ID, если есть
            if (!empty($item['UF_XML_ID']) && $asXmlId === true) {
                $result[$item['UF_XML_ID']] = $item;
            } elseif (!empty($item['ID'])) {
                $result[$item['ID']] = $item;
            } else {
                $result[] = $item;
            }
        }

        // Приведем массив к нужным типам данных
        if ($useTypeConverter === true) {
            if ($typeConverter->getTypes()) {
                $result = $typeConverter->convertDataTypes($result);
            }
        }

        // Возвращаем индексный массив
        if ($asArray === true) {
            $result = array_values($result);
        }

        return $result;
    }


    public static function getRow(string $code, array $params = [], bool $useTypeConverter = true): array
    {
        $getList = self::getList($code, $params, $useTypeConverter);
        if (empty($getList)) {
            return [];
        }

        return reset($getList);
    }


    /**
     * @param string $code
     * @param array $params
     * @return int
     * @throws ArgumentException
     * @throws LoaderException
     * @throws SystemException
     */
    public static function getCount(string $code, array $params): ?int
    {
        if (Loader::includeModule('highloadblock')) {
            $entity = self::getEntity($code);

            $params['select'] = ['ID'];
            $res = $entity::getList($params);

            return $res->getSelectedRowsCount();
        }

        return null;
    }


    /**
     * Функция добавляет элемент в HL-блок
     *
     * @param string $hlbCode Код HL-блока из опций модуля
     * @param array $fields Массив полей для добавления
     *
     * @return AddResult|bool
     * @throws \Exception
     */
    public static function add(string $hlbCode, array $fields): ?int
    {
        if (Loader::includeModule('highloadblock')) {
            $entity = self::getEntity($hlbCode);
            if ($entity) {
                $result = $entity::add($fields);
                return $result->getId();
            }
        }

        return null;
    }


    /**
     * Функция обновляет параметры записи в базе
     *
     * @param string $hlbCode Тип HL-блока
     * @param int $id ID записи
     * @param array $fields Массив обновляемых параметров
     *
     * @return UpdateResult|bool|null
     * @throws ArgumentException
     * @throws LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    public static function update(string $hlbCode, int $id, array $fields): ?int
    {
        if (Loader::includeModule('highloadblock') && intval($id)) {
            $entity = self::getEntity($hlbCode);

            if ($entity) {
                $result = $entity::update($id, $fields);
                return $result->getId();
            }

            return null;
        }

        return null;
    }


    /**
     * Функция удаляет запись с указанным ID
     * @param string $hlbCode Код HL-блока из опций модуля
     * @param int $id ID записи
     * @return boolean true - успешно и false в противном случае
     * @throws ArgumentException
     * @throws HelperException
     * @throws LoaderException
     * @throws SystemException
     * @throws Exception
     */
    public static function delete($hlbCode, $id): bool
    {
        if (Loader::includeModule('highloadblock') && (int)$id) {
            $entity = self::getEntity($hlbCode);
            if ($entity) {
                $result = $entity::delete($id);

                // Проверка результата
                if ($result->isSuccess()) {
                    return true;
                }

                throw new HelperException(implode(', ', $result->getErrorMessages()));
            }
        }

        return false;
    }


    /**
     * Функция получается список всех highload блоков по фильтру
     *
     * @param array $params Параметры выборки
     *
     * @return array
     * @throws ArgumentException
     * @throws LoaderException
     */
    public static function getHlList($params = [])
    {
        if (Loader::includeModule('highloadblock')) {
            $elements = [];
            $res = HighloadBlockTable::getList($params);
            while ($item = $res->Fetch()) {
                $key = $item['ID'];

                $item['ENTITY_ID'] = 'HLBLOCK_' . $key;

                $elements[$key] = $item;
            }

            return $elements;
        }

        return null;
    }




    // -----------------------------------------------------------------------------------------------------------------
    // ---   ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ   ----------------------------------------------------------------------------------
    // -----------------------------------------------------------------------------------------------------------------


    /**
     * @param $hlbCode
     *
     * @return DataManager|bool|null
     * @throws ArgumentException
     * @throws LoaderException
     * @throws SystemException
     */
    public static function getEntity($hlbCode)
    {
        if (Loader::includeModule('highloadblock') && !empty($hlbCode)) {
            $hlbId = null;
            if (is_numeric($hlbCode)) {
                $hlbId = (int)$hlbCode;
            } else {
                $res = HighloadBlockTable::getList(['select' => ['ID', 'NAME'], 'filter' => ['NAME' => $hlbCode]]);
                if ($arItem = $res->fetch()) {
                    $hlbId = (int)$arItem['ID'];
                }
            }

            if (!empty($hlbId)) {
                $arrHlb = HighloadBlockTable::getById($hlbId)->fetch();
                $entity = HighloadBlockTable::compileEntity($arrHlb);

                return $entity->getDataClass();

            } else {
                return null;
            }
        }

        return null;
    }


    /**
     * @param array $params данные для формирования Uri с полями:
     * <li>hlId (либо hlType) - идентификатор хайлоад блока
     * <li>hlType (либо hlId) - тип хайлоад блока (делает доп. запрос, чтобы узнать идентификатор хайлоад-блока
     * <li>id - идентификатор элемента
     * <li>lang - языковая версия
     * @return string
     * @throws LoaderException
     * @throws SystemException
     * @throw ArgumentException
     */
    public static function getAdminEditPageUri(array $params): string
    {
        if (empty($params['hlId']) && empty($params['hlType'])) {
            throw new ArgumentException('Не передан один из обязательных параметров hlId|hlType');
        }

        if (empty($params['hlId'])) {
            $entity = self::getEntity($params['hlType']);
            $hlId = $entity::getHighloadBlock()['ID'];
        } else {
            $hlId = $params['hlId'];
        }

        $params = [
            'ENTITY_ID' => $hlId,
            'ID'        => $params['id'],
            'LANG'      => $params['lang'] ?? LANGUAGE_ID
        ];

        return '/bitrix/admin/highloadblock_row_edit.php?' . http_build_query($params);
    }


    /**
     * Возвращает идентификатор сущности
     *
     * @param string $code
     * @param int $cacheTime
     * @return string|null
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getEntityId(string $code, int $cacheTime = 3600 * 24): ?string
    {
        Loader::includeModule('highloadblock');

        $items = HighloadBlockTable::getList([
            'select' => ['ID', 'NAME'],
            'cache'  => ['ttl' => $cacheTime]
        ])->fetchAll();

        foreach ($items as $item) {
            if ($item['NAME'] === $code) {
                return 'HLBLOCK_' . $item['ID'];
            }
        }

        return null;
    }


    /**
     * Проверяет наличие модуля в системе
     * @throws LoaderException
     */
    public static function checkModule(): void {
        if(!Loader::includeModule('highloadblock')) {
            throw new RuntimeException('Для работы API необходимо наличие модуля «iblock»');
        }
    }
}
