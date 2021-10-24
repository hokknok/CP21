<?php

namespace Ninja\Helper\Iblock;

use Bitrix\Iblock\InheritedProperty\SectionValues;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CIBlockSection;
use Ninja\Helper\CacheManager;
use Ninja\Helper\TypeConvert;
use Ninja\Project\Application;
use RuntimeException;


class Section
{
    /**
     * @param array $params
     * @param callable|null $callback
     * @return array
     * @throws LoaderException
     */
    public static function getListFromCache(array $params, callable $callback = null): array
    {
        $ibCode = $params['FILTER']['IBLOCK_CODE'];
        if (empty($ibCode)) {
            die('IBLOCK_CODE is empty!');
        }

        $ibId = Iblock::getIblockIdByCode($ibCode);
        $params['FILTER']['IBLOCK_ID'] = $ibId;
        unset($params['FILTER']['IBLOCK_CODE']);

        $skipCache = ($params['__SKIP_CACHE'] === true);
        $cachePath = '/sections/' . $ibCode . '/';

        return CacheManager::getDataCache($cachePath, $params, static function (&$itemList) use ($params, $callback) {
            $itemList = self::getList($params);

            if (is_callable($callback)) {
                $callback($itemList);
            }
        }, Application::DEF_TTL, $skipCache);
    }


    /**
     * @param array $params
     * @return array|null
     * @throws LoaderException
     */
    public static function getRowFromCache(array $params): ?array
    {
        $itemList = self::getListFromCache($params);
        if (empty($itemList)) {
            return null;
        }

        return reset($itemList);
    }


    /**
     * Метод возвращает массив с данными о разделах инфоблока
     *
     * @param array $params Параметры выборки
     *
     * @return array|bool
     * @throws LoaderException
     */
    public static function getList(array $params): array
    {
        self::checkModule();

        $typeConverter = new TypeConvert($params['SELECT'] ?: []);
        $queryParams = self::getQueryParams($params, $typeConverter);

        // Выборка результата из базы
        $res = CIBlockSection::GetList($queryParams['order'], $queryParams['filter'], $queryParams['bIncCnt'], $queryParams['select'], $queryParams['nav']);

        $result = [];
        if ($params['GET_NEXT'] === 'Y') {
            while ($item = $res->GetNext()) {
                $key = $item['ID'];
                $result[$key] = $item;
            }
        } else {
            while ($item = $res->Fetch()) {
                $key = $item['ID'];
                $result[$key] = $item;
            }
        }

        // Приведем массив к нужным типам данных
        if ($typeConverter->getTypes()) {
            $result = $typeConverter->convertDataTypes($result);
        }

        // Возвращаем индексный массив
        if ($params['AS_ARRAY'] === 'Y') {
            $result = array_values($result);
        }

        return $result;
    }


    /**
     * Метод возвращает первую строку из массива getList.
     * Используется если нужно выбрать 1 элемент.
     *
     * @param array $params Параметры выборки
     *
     * @return array
     * @throws LoaderException
     */
    public static function getRow(array $params): array
    {
        $getList = self::getList($params);
        if (!empty($getList)) {
            return reset($getList);
        }

        return [];
    }


    private static function getQueryParams(array $params, TypeConvert $typeConverter): array
    {
        // Определение направления сортировки
        $order = $params['ORDER'] ?: ['SORT' => 'ASC'];

        // Определение фильтра
        $filter = $params['FILTER'] ?: [];

        // CHECK_PERMISSIONS
        if (empty($params['FILTER']['CHECK_PERMISSIONS'])) {
            $params['FILTER']['CHECK_PERMISSIONS'] = 'N';
        }

        // Возвращать ли поле ELEMENT_CNT - количество элементов в разделе
        $bIncCnt = ($params['bIncCnt'] === true);

        // Определение полей выборки
        $select = array_merge($typeConverter->getSelect(), ['ID', 'IBLOCK_ID']);
        $select = array_unique($select);

        // Определение постраничной навигации
        $nav = $params['NAV'] ?: false;

        return [
            'order'        => $order,
            'filter'       => $filter,
            'bIncCnt'       => $bIncCnt,
            'select'       => $select,
            'nav'          => $nav,
        ];
    }


    /**
     * Функция для добавления раздела инфоблока
     *
     * @param array $params Поля и свойства нового раздела
     *
     * @return array ID нового раздела или код ошибки в строке
     * @throws LoaderException
     */
    public static function add(array $params): array
    {
        self::checkModule();

        if (empty($params['IBLOCK_ID'])) {
            throw new RuntimeException('Empty IBLOCK_ID');
        }

        $sect = new CIBlockSection;
        if ($sectionId = $sect->Add($params)) {
            return [
                'type'    => 'success',
                'message' => 'Раздел добавлен.',
                'id'      => $sectionId
            ];
        }

        return [
            'type'    => 'error',
            'message' => 'Ошибка: ' . $sect->LAST_ERROR
        ];
    }


    /**
     * Метод обновляет параметры раздела
     *
     * @param int $id ID раздела
     * @param array $params Поля раздела
     *
     * @return array
     * @throws LoaderException
     */
    public static function update(int $id, array $params): array
    {
        self::checkModule();

        $sect = new CIBlockSection;
        $result = $sect->Update($id, $params);

        if ($result === true) {
            return [
                'type'    => 'success',
                'message' => 'Элемент обновлен.',
                'id'      => $id
            ];
        }

        return [
            'type'    => 'error',
            'message' => 'Ошибка: ' . $sect->LAST_ERROR
        ];
    }


    // =========================================================================
    // ======================== ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ =========================
    // =========================================================================


    /**
     * Функция возвращает значение поля раздела инфоблока
     *
     * @param int $id ID раздела
     * @param string $code Символьный код поля
     *
     * @return string
     *
     * @throws LoaderException
     */
    public static function getField(int $id, string $code): ?string
    {
        if (!is_numeric($id)) {
            return null;
        }

        $arSectionList = self::getList([
            'FILTER' => ['ID' => $id],
            'SELECT' => [$code],
        ]);

        $arSection = end($arSectionList);


        $result = null;
        if ($arSection[$code]) {
            $result = $arSection[$code];
        }


        return $result;
    }


    /**
     * Метод возвращает массив с описанием мета-тегов title, keywords и description
     *
     * @param int $iblockId - ID инфоблока
     * @param array $section - Массив элемента с ключем ['id']
     *
     * @return array
     */
    public static function getSeo(int $iblockId, array $section): array
    {
        $props = new SectionValues($iblockId, $section['id']);
        $values = $props->getValues();

        return [
            'title'       => $values['SECTION_META_TITLE'] ?: $section['name'],
            'caption'     => $values['SECTION_PAGE_TITLE'] ?: $section['name'],
            'keywords'    => $values['SECTION_META_KEYWORDS'] ?: $values['SECTION_PAGE_KEYWORDS'],
            'description' => $values['SECTION_META_DESCRIPTION'] ?: $values['SECTION_PAGE_DESCRIPTION'],
        ];
    }


    /**
     * Функция возвращает список разделов, отсортированный в порядке "полного развернутого дерева"
     *
     * @param array $params Массив с параметрами выборки. Используемые ключи <br>
     *                        <li> IBLOCK_CODE
     *                        <li> IBLOCK_ID
     *                        <li> FILTER
     *                        <li> SELECT
     *                        <li> GET_NEXT
     *
     * @return array Данные по разделам
     *
     * @throws LoaderException
     */
    public static function getTreeList(array $params): array
    {
        self::checkModule();

        $typeConverter = new TypeConvert($params['SELECT'] ?: []);

        $iIblockID = is_set($params['IBLOCK_CODE'])
            ? Iblock::getIblockIdByCode($params['IBLOCK_CODE'])
            : $params['IBLOCK_ID'];

        // Определение фильтра
        $arFilter = $params['FILTER'] ?: [];

        if ($iIblockID) {
            $arFilter['IBLOCK_ID'] = $iIblockID;
        }


        // Определение полей выборки
        $select = $typeConverter->getSelect();


        // Выборка результата из базы
        $rsSection = CIBlockSection::GetTreeList($arFilter, $select);

        $arResult = [];
        if ($params['GET_NEXT'] === 'Y') {
            while ($arSection = $rsSection->GetNext()) {
                if ($arSection['ID']) {
                    $key = $arSection['ID'];
                    $arResult[$key] = $arSection;
                } else {
                    $arResult[] = $arSection;
                }
            }

        } else {
            while ($arSection = $rsSection->Fetch()) {
                if ($arSection['ID']) {
                    $key = $arSection['ID'];
                    $arResult[$key] = $arSection;
                } else {
                    $arResult[] = $arSection;
                }
            }

        }

        if ($select) {
            $arResult = $typeConverter->convertDataTypes($arResult);
        }


        if ($params['AS_ARRAY'] === 'Y') {
            $arResult = array_values($arResult);
        }

        return $arResult;
    }


    /**
     * Проверяет наличие модуля в системе
     * @throws LoaderException
     */
    public static function checkModule(): void {
        if (!Loader::includeModule('iblock')) {
            throw new RuntimeException('Для работы API необходимо наличие модуля «iblock»');
        }
    }
}
