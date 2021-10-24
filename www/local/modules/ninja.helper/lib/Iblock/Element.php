<?php

namespace Ninja\Helper\Iblock;


use Bitrix\Iblock\InheritedProperty\ElementValues;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CIBlockElement;
use CIBlockFormatProperties;
use CIBlockPropertyEnum;
use Ninja\Helper\CacheManager;
use Ninja\Helper\Dbg;
use Ninja\Helper\TypeConvert;
use Ninja\Project\Application;
use RuntimeException;


/**
 * Class Element
 */
class Element
{
    /**
     * Метод возвращает список элементов инфоблока с использованием CacheManager проекта
     *
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
        $cachePath = '/items/' . $ibCode . '/';

        return CacheManager::getDataCache($cachePath, $params, static function (&$itemList) use ($params, $callback) {
            $itemList = self::getList($params);

            if (is_callable($callback)) {
                $callback($itemList);
            }
        }, $params['TTL'] ?? Application::DEF_TTL, $skipCache);
    }


    /**
     * Метод возвращает первую строку из массива getList с использованием CacheManager проекта
     * Используется если нужно выбрать 1 элемент
     *
     * @param array $params
     * @param callable|null $callback
     * @return array|null
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getRowFromCache(array $params, callable $callback = null): ?array
    {
        $itemList = self::getListFromCache($params, $callback);
        if (empty($itemList)) {
            return null;
        }

        return reset($itemList);
    }



    /**
     * Метод возвращает массив с данными об элементах инфоблока
     *
     * @param array $params Параметры выборки
     *
     * @return array
     * @throws LoaderException
     */
    public static function getList(array $params): array
    {
        self::checkModule();

        $typeConverter = new TypeConvert($params['SELECT'] ?: []);
        $queryParams = self::getQueryParams($params, $typeConverter);

        // Выборка результата из базы
        $res = CIBlockElement::GetList($queryParams['order'], $queryParams['filter'], $queryParams['group'], $queryParams['nav'], $queryParams['select']);

        $result = [];
        if ($params['GET_NEXT'] === 'Y') {
            while ($item = $res->GetNext(true, false)) {
                $key            = $item['ID'];
                $result[$key] = $item;
            }

        } else {
            while ($item = $res->Fetch()) {
                $key = $item['ID'];
                $result[$key] = $item;
            }
        }

        if ($params['GET_ENUM_CODE'] === 'Y') {
            // Если необходима выборка кодов свойств типа «список»
            $arEnumXmlID = [];

            foreach ($result as $key => $arElement) {
                if (!is_array($arElement)) {
                    continue;
                }

                foreach ($arElement as $keyField => $valField) {
                    $arMatches = [];

                    if (preg_match('/^PROPERTY_(\w+)_ENUM_ID$/', $keyField, $arMatches)) {
                        // Если поле относится к свойству «список»
                        if ($arEnumXmlID[$valField]) {
                            $sXmlID = $arEnumXmlID[$valField];
                        } else {
                            $arPropEnum             = CIBlockPropertyEnum::GetByID($valField);
                            $sXmlID                 = $arPropEnum['XML_ID'];
                            $arEnumXmlID[$valField] = $sXmlID;

                        }

                        $result[$key]['PROPERTY_' . $arMatches[1] . '_XML_ID'] = $sXmlID;
                    }
                }
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
     * @param bool $fullList
     *
     * @return array
     * @throws LoaderException
     */
    public static function getRow(array $params, bool $fullList = false): array
    {
        if($fullList === true) {
            $getList = self::getFullList($params);
        } else {
            $getList = self::getList($params);
        }

        if(!empty($getList)) {
            return reset($getList);
        }

        return [];
    }


    /**
     * Метод возвращает массив с данными об элементах инфоблока
     *
     * @param array $params Параметры выборки
     *
     * @return array
     * @throws LoaderException
     */
    public static function getFullList(array $params): array
    {
        self::checkModule();

        $typeConverter = new TypeConvert($params['SELECT'] ?: []);
        $queryParams = self::getQueryParams($params, $typeConverter);

        // Выборка результата из базы
        $result = [];
        $resultPropNames = [];
        $res = CIBlockElement::GetList($queryParams['order'], $queryParams['filter'], $queryParams['group'], $queryParams['nav'], $queryParams['select']);
        while ($item = $res->GetNextElement(true, false)) {
            $fields = $item->GetFields();
            $props  = $item->GetProperties();
            $propNames = [];

            foreach ($queryParams['select_props'] as $propCode) {
                $prop = $props[$propCode];
                if(!empty($prop['VALUE'])) {
                    $props[$propCode] = CIBlockFormatProperties::GetDisplayValue($fields, $prop, '');
                }
            }

            foreach ($props as $prop) {
                $fieldCode = 'PROPERTY_' . $prop['CODE'] . '_VALUE';
                if(!empty($prop['DISPLAY_VALUE'])) {
                    if($prop['PROPERTY_TYPE'] === 'F') {
                        $fields[$fieldCode] = $prop['FILE_VALUE'];
                    } else {
                        $fields[$fieldCode] = strip_tags($prop['DISPLAY_VALUE']);
                    }

                    $propNames[$fieldCode] = $prop['NAME'];
                }
            }

            $key = $fields['ID'];
            $result[$key] = $fields;
            $resultPropNames[$key] = $propNames;
        }

        // Приведем массив к нужным типам данных
        if ($typeConverter->getTypes()) {
            // Обрабатываем значения
            $result = $typeConverter->convertDataTypes($result);

            // Обрабатываем названия
            $resultPropNames = $typeConverter->convertDataTypes($resultPropNames);
            foreach ($resultPropNames as $id=>$item) {
                $result[$id]['propNames'] = $item;
            }
        }

        // Возвращаем индексный массив
        if ($params['AS_ARRAY'] === 'Y') {
            $result = array_values($result);
        }

        return $result;
    }


    /**
     * @param array $params
     * @param TypeConvert $typeConverter
     * @return array
     */
    private static function getQueryParams(array $params, TypeConvert $typeConverter): array
    {
        // Определение направления сортировки
        $order = $params['ORDER'] ?: ['SORT' => 'ASC'];

        // CHECK_PERMISSIONS
        if(empty($params['FILTER']['CHECK_PERMISSIONS'])) {
            $params['FILTER']['CHECK_PERMISSIONS'] = 'N';
        }

        // Определение фильтра
        $filter = $params['FILTER'] ?: [];

        // Устонавливает IBLOCK_ID
        if (empty($params['FILTER']['IBLOCK_ID']) && !empty($params['IBLOCK_ID'])) {
            $filter['IBLOCK_ID'] = $params['IBLOCK_ID'];
        }

        // Определение групировки
        $group = $params['GROUP'] ?: false;

        // Определение постраничной навигации
        $nav = $params['NAV'] ?: false;

        // Определение полей выборки
        $select = array_merge($typeConverter->getSelect(), ['ID', 'IBLOCK_ID']);
        $select = array_unique($select);

        $selectProps = [];
        foreach ($select as $selectCode) {
            $matches = [];
            if (preg_match('/^PROPERTY_(\w+)$/', $selectCode, $matches)) {
                $selectProps[] = $matches[1];
            }
        }

        return [
            'nav'          => $nav,
            'group'        => $group,
            'order'        => $order,
            'filter'       => $filter,
            'select'       => $select,
            'select_props' => $selectProps,
        ];
    }


    /**
     * Метод получает разделы к которым принадлежит эдемент.
     *
     * @param array $elementId - Массив элементов
     * @param bool $bElementOnly - Указывает на необходимость выборки привязок и из свойств типа "Привязка к разделу".
     *
     * @return array
     * @throws LoaderException
     */
    public static function getGroups(array $elementId, bool $bElementOnly = false): array
    {
        if (Loader::includeModule('iblock') && !empty($elementId)) {
            $groups = [];
            $res = CIBlockElement::GetElementGroups($elementId, $bElementOnly);
            while($item = $res->Fetch()) {
                $groups[] = $item['ID'];
            }

            return $groups;
        }

        return [];
    }


    /**
     * Возвращает кол-во элементов
     *
     * @param array $filter
     *
     * @return int
     * @throws LoaderException
     */
    public static function getCount(array $filter = []): int
    {
        Loader::includeModule('iblock');

        /**
         * Логика основывается на поведении метода GetList.
         * Если в качестве параметра группировки передать пустой массив, вернется кол-во элементов удовлетворяющих фильтру
         */
        return (int) CIBlockElement::GetList([], $filter, []);
    }


    /**
     * Метод для добавления элемента инфоблока
     *
     * @param array $fields Поля и свойства нового элемента
     * @param bool $workFlow Режим документооборота
     *
     * @return array ID нового элемента или код ошибки в строке
     * @throws LoaderException
     */
    public static function add(array $fields, bool $workFlow=false): array
    {
        if (Loader::includeModule('iblock')) {

            $ibId = !empty($fields['IBLOCK_ID']) ? $fields['IBLOCK_ID'] : Iblock::getIblockIdByCode($fields['IBLOCK_CODE']);
            if (!empty($ibId)) {

                $fields['IBLOCK_ID'] = $ibId;
                unset($fields['IBLOCK_CODE']);

                $el = new CIBlockElement;
                if ($elmId = $el->Add($fields, $workFlow)) {
                    return [
                        'type'    => 'success',
                        'message' => 'Элемент добавлен.',
                        'id'      => $elmId
                    ];
                }

                return [
                    'type'    => 'error',
                    'message' => 'Ошибка: ' . $el->LAST_ERROR
                ];
            }
        }

        return [
            'type'    => 'warning',
            'message' => 'Упс! Что-то пошло не так.'
        ];
    }


    /**
     * Метод обновляет поля и свойства элемента
     *
     * @param int $id ID изменяемой записи
     * @param array $fields Массив полей [FIELDS] и свойств [PROPERTY_VALUES]:
     * если идет обновление свойств обязательна передача одного из двух элементов массива IBLOCK_ID|IBLOCK_CODE, иначе пропустится обновление свойств
     * @param bool $workFlow Режим документооборота
     *
     * @return array
     * @throws LoaderException
     */
    public static function update(int $id, array $fields, bool $workFlow = false): array
    {
        self::checkModule();

        $updFields = $fields;

        /**
         * Удаляем не нужные поля, которые не участвуют в Update
         */
        $removeFields = ['IBLOCK_ID', 'IBLOCK_CODE', 'PROPERTY_VALUES'];
        foreach ($removeFields as $field) {
            if (isset($updFields[$field])) {
                unset($updFields[$field]);
            }
        }

        /**
         * Обновление в БД
         */
        $el  = new CIBlockElement;
        $res = $el->Update($id, $updFields, $workFlow);

        if ($res === true) {
            /**
             * Формируем массив свойств отдельно, чтобы после обновления из одновить.
             * Нужно для того, чтобы не задавать весь список свойст в Update.
             * Иначе незаданные затрутся.
             */
            $props = [];
            if ($fields['PROPERTY_VALUES']) {
                $props = $fields['PROPERTY_VALUES'];
            }

            /**
             * Определение ID инфоблока
             */
            $ibId = !empty($fields['IBLOCK_ID']) ? $fields['IBLOCK_ID'] : Iblock::getIblockIdByCode($fields['IBLOCK_CODE']);

            /**
             * Обновляем свойства
             */
            if (!empty($ibId) && !empty($props)) {

                CIBlockElement::SetPropertyValuesEx($id, $ibId, $props);
            }

            return [
                'type'    => 'success',
                'message' => 'Элемент обновлен.',
                'id'      => $id
            ];
        }

        return [
            'type'    => 'error',
            'message' => 'Ошибка: ' . $el->LAST_ERROR,
            'id'      => $id
        ];
    }


    /**
     * @throws \Bitrix\Main\LoaderException
     */
    public static function updateProperty(int $id, array $values): void
    {
        self::checkModule();

        CIBlockElement::SetPropertyValuesEx($id, false, $values);
    }


    /**
     * Метод удаляет элемент инфоблока
     *
     * @const  object  $DB Класс для работы с базой данной
     *
     * @param int $id ID удаляемого элемента
     *
     * @return boolean true, если удаление прошло успешно и false, если нет
     * @throws LoaderException
     */
    public static function delete(int $id): bool
    {
        self::checkModule();

        global $DB;

        $DB->StartTransaction();
        if (!CIBlockElement::Delete($id)) {
            $DB->Rollback();

            return false;
        } else {
            $DB->Commit();

            return true;
        }
    }


    // =========================================================================
    // ======================== ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ =========================
    // =========================================================================

    /**
     * Функция возвращает поле элемента инфоблока
     *
     * @param int    $id
     * @param string $code
     *
     * @return bool
     * @throws LoaderException
     */
    public static function getField(int $id, string $code): ?bool
    {
        if ($id) {
            return null;
        }
        if (!$code) {
            return null;
        }

        // Выборка элементов из базы
        $arQuery = [
            'FILTER' => ['ID' => $id],
            'SELECT' => [$code],
        ];

        if ($code === 'DETAIL_PAGE_URL') {
            $arQuery['GET_NEXT'] = 'Y';
        }

        $arElements = self::getList($arQuery);

        if ($arElements) {
            foreach ($arElements as $arElement) {
                return $arElement[$code];
            }
        }

        return false;
    }


    /**
     * Метод возвращает массив с описанием мета-тегов title, keywords и description
     *
     * @param int $ibId
     * @param int $itemId
     * @param string|null $itemName
     *
     * @return array
     */
    public static function getSeo(int $ibId, int $itemId, ?string $itemName = ''): array
    {
        $props  = new ElementValues($ibId, $itemId);
        $values = $props->getValues();

        $title       = $values['ELEMENT_META_TITLE'] ?: $values['SECTION_META_TITLE'] ?: $itemName;
        $caption     = $values['ELEMENT_PAGE_TITLE'] ?: $values['ELEMENT_PAGE_TITLE'] ?: $itemName;
        $keywords    = $values['ELEMENT_META_KEYWORDS'] ?: $values['SECTION_META_KEYWORDS'];
        $description = $values['ELEMENT_META_DESCRIPTION'] ?: $values['SECTION_META_DESCRIPTION'];

        return [
            'title'       => $title,
            'caption'     => $caption,
            'keywords'    => $keywords,
            'description' => $description,
        ];
    }


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
