<?php

namespace Ninja\Helper;

/*
 * Вспомогательный класс работы с массивами
 */

class Arr
{

    /**
     * Функция производит поиск по двумерному массиву
     * @param array $arIn Массив в котором производится поиск
     * @param string $sFieldName Имя ключа, по которому ищем
     * @param string $sSearchVal Искомое значение
     * @param string $sNeededFieldVal Значение какого поля нужно вернуть, если null - вернется ключ массива
     * @return false|int|string
     */
    public static function findInArr(array $arIn, string $sFieldName, string $sSearchVal, string $sNeededFieldVal = 'ID')
    {
        return array_search($sSearchVal, array_column($arIn, $sFieldName, $sNeededFieldVal), true);
    }


    /**
     * Функция разбивает массив на равное количество колонок
     * @param array $arIn
     * @param int $iCount
     * @return array
     */
    public static function getChunkCols($arIn, $iCount)
    {
        $listLength = count($arIn);
        $partLength = floor($listLength / $iCount);
        $partRem = $listLength % $iCount;
        $partition = Array();
        $mark = 0;

        for ($px = 0; $px < $iCount; $px++) {
            $increment = ($px < $partRem) ? $partLength + 1 : $partLength;
            $partition[$px] = array_slice($arIn, $mark, $increment);
            $mark += $increment;
        }

        return $partition;
    }


    /**
     * Метод вычисляет на сколько процентов первый массив похож на второй по значениям
     * @param array $arSource Исходный массив
     * @param array $arTarget Массив с которым идет сравнение
     * @return int Число в диапазоне 0-100
     */
    public static function getIntersectPercent($arSource, $arTarget)
    {
        if (!is_array($arSource) || !is_array($arTarget)) {
            return null;
        }

        $iSourceCount = count($arSource);
        $arIntersect = array_intersect($arSource, $arTarget);
        $arIntersectCount = count($arIntersect);

        if ($arIntersectCount > 0) {
            $iPercentage = round($arIntersectCount / $iSourceCount * 100);

        } else {
            $iPercentage = 0;

        }

        return (int)$iPercentage;

    }


    /**
     * Метод вычисляет на сколько процентов первый массив похож на второй
     * @param array $arSource Исходный массив
     * @param array $arTarget Массив с которым идет сравнение
     * @return int Число в диапазоне 0-100
     */
    public static function getIntersectKeyPercent($arSource, $arTarget)
    {
        if (!is_array($arSource) || !is_array($arTarget)) {
            return null;
        }

        $iSourceCount = count($arSource);
        $arIntersect = array_intersect_key($arSource, $arTarget);
        $arIntersectCount = count($arIntersect);

        if ($arIntersectCount > 0) {
            $iPercentage = round($arIntersectCount / $iSourceCount * 100);

        } else {
            $iPercentage = 0;

        }

        return (int)$iPercentage;

    }


    /**
     * Функция сводит два одномерных или двумерных массива к одному,
     * а дублирующие свойства превращаются в массивы
     * @param array $arTarget
     * @param array $arNew
     * @return array
     */
    public static function getMergeExt($arTarget, $arNew)
    {
        if (!is_array($arTarget) || !is_array($arNew)) {
            return null;
        }

        foreach ($arTarget as $fieldKey => $fieldValue) {
            if (is_array($fieldValue) && is_array($arNew[$fieldKey])) {
                $arTarget[$fieldKey] = array_merge($fieldValue, $arNew[$fieldKey]);

            } elseif (is_array($fieldValue) && !is_array($arNew[$fieldKey])) {
                $arTarget[$fieldKey][] = $arNew[$fieldKey];

            } elseif (is_array($arNew[$fieldKey]) && !is_array($fieldValue)) {
                $arNew[$fieldKey][] = $fieldValue;
                $arTarget[$fieldKey] = $arNew[$fieldKey];

            } elseif ($fieldValue !== $arNew[$fieldKey]) {
                $arTarget[$fieldKey] = Array($fieldValue, $arNew[$fieldKey]);

            }

        }

        return $arTarget;

    }


    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    public static function getMergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::getMergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }


    /**
     * Функция возвращает набор рандомных ключей из массива
     * @param array $arSource Массив в котором производится поиск
     * @param array $arParams Параметры поиска. Массив с параметрами:
     *    <li> offset (int) - Отступ, с которого начать поиск
     *    <li> count (int) - Необходимое количество ключей
     *    <li> reverse (boolean) - Инвертировать полученный массив или нет
     *    <li> padding (int) - Количество резервируемых элементов слева и справа
     * @return array Массив найденных ключей
     */
    public static function getRandKeys($arSource, $arParams)
    {
        if (!is_array($arSource) || !is_array($arParams)) {
            return null;
        }

        $arFoundKeys = Array();
        $iPadding = $arParams['padding'] ?: 0;


        // Поиск в массиве с учетом отступа offset
        $arSearchableArr = array_slice($arSource, $arParams['offset'], null, true);

        if ($arSearchableArr) {
            for ($i = 0; $i < $arParams['count']; $i++) {
                $iRandKey = array_rand($arSearchableArr);

                unset($arSearchableArr[$iRandKey]);

                if ($iPadding > 0) {
                    for ($p = 0; $p < $iPadding; $p++) {
                        if (isset($arSearchableArr[$iRandKey - $p])) {
                            unset($arSearchableArr[$iRandKey - $p]);
                        }

                        if (isset($arSearchableArr[$iRandKey + $p])) {
                            unset($arSearchableArr[$iRandKey + $p]);
                        }
                    }
                }

                $arFoundKeys[] = $iRandKey;
            }
        }
        // ---------------------------------------------------------------------


        // Если нужна инверсия
        if ($arParams['reverse']) {
            $arFoundKeys = array_reverse($arFoundKeys);
        }

        // ---------------------------------------------------------------------


        return $arFoundKeys;

    }


    /**
     * Функция производит поиск по массиву $arData, $sFieldName
     * @param string $sQuery Строка поиска или спец.код __get_all
     * @param array $arData Массив по которому ищем
     * @param array $arFieldName Массив полей в которых ищем
     * @param array $arParams Дополнительные параметры поиска [USE_TRANSLIT]
     * @return array Массив ID найденных элементов
     */
    public static function searchInArraySmart($sQuery, $arData, $arFieldName, $arParams)
    {
        $sQueryL = strtolower($sQuery);
        $arQueryList = Array($sQueryL);


        if ($arParams['USE_TRANSLIT'] === 'Y') {
            $arTranslitParams = Array(
                'replace_space' => ' ',
            );

            $arQueryList[] = \CUtil::translit($sQueryL, 'ru', $arTranslitParams);

        }


        // Ищем результаты поиска среди магазинов
        $arIDByQuery = Array();
        foreach ($arData as $arItem) {
            foreach ($arFieldName as $sFieldName) {
                foreach ($arQueryList as $sQueryItem) {
                    // Значение поля элемента приводим к нижнему регистру
                    $sItemFieldL = trim(strtolower($arItem[$sFieldName]));


                    if ($sQuery === '__get_all') {
                        // Если нужны все результаты
                        $arIDByQuery[] = $arItem['id'];

                    } elseif (stristr($sItemFieldL, $sQueryItem)) {
                        // Если есть полное вхождение строки поиска в названии
                        $arIDByQuery[] = $arItem['id'];

                    } elseif (strlen($sQuery) >= 4) {
                        // Если нет четкого вхождения, то вычисляем сходство строк
                        $iSimPointP = 0;
                        // $iSimPoint = similar_text($sItemFieldL, $sQueryItem, $iSimPointP);


                        // Определяем сколько очков сходства нужно набрать:
                        $iNeedSimPoint = 70;
                        if (strlen($sQueryItem) < 10 && strlen($sItemFieldL) < 10) {
                            $iNeedSimPoint = 80;
                        }

                        $iStringLength = (abs(strlen($sQueryItem) - strlen($sItemFieldL)) < 2) ? 1 : 0;

                        if ($iSimPointP > $iNeedSimPoint && $iStringLength) {
                            // Если сходство более необходимого то сохраним
                            $arIDByQuery[] = $arItem['id'];
                        }
                    }
                }
            }
        }

        return $arIDByQuery;
    }


    /**
     * Функция перемешивает массив с сохранением сортировки, но исключая дубли
     * элементов
     * Например, массив вида: [1][2][3][4][5][2][6][7][8][2][8][8][8][8][2][7]
     * Будет приведен к виду: [1][2][3][4][5][2][6][8][7][8][2][8][2][8][7][8]
     * @param array $arSource Исходный массив
     * @param string $sKey Ключ элемета массива по которому считать элементы дублями
     * @return array
     */
    public static function shuffleArrayWithDouble($arSource, $sKey = 'ID')
    {
        if (!is_array($arSource)) {
            return null;
        }

        $arResult = array_values($arSource);
        $iCount = count($arResult);


        $arItemLast = null;
        for ($k = 0; $k < $iCount; $k++) {
            $arItem = $arResult[$k];

            if ($arItemLast !== null && $arItem[$sKey] === $arItemLast[$sKey]) {
                $bFindToReplace = false;

                // Поиск подходящего элемента для замены среди следующих элементов
                for ($i = $k; $i < $iCount; $i++) {
                    if ($arResult[$i][$sKey] !== $arItem[$sKey]) {
                        $buffer = $arResult[$i];

                        $arResult[$i] = $arResult[$k];
                        $arResult[$k] = $buffer;
                        $arItem = $arResult[$k];

                        $bFindToReplace = true;

                        break;
                    }
                }


                // Если поиск не удался, то ищем в обратном направлении и смещаем дубли
                if ($bFindToReplace === false) {
                    for ($i = $k; $i >= 0; $i--) {
                        if ($arResult[$i][$sKey] !== $arItem[$sKey]
                            && $arResult[$i - 1][$sKey] !== $arItem[$sKey]) {

                            for ($j = $i; $j < $iCount; $j++) {
                                $buffer = $arResult[$i];

                                $arResult[$i] = $arResult[$j];
                                $arResult[$j] = $buffer;
                            }

                            $arItem = $arResult[$k];

                            break;
                        }
                    }
                }

            }

            $arItemLast = $arItem;

        }


        return $arResult;

    }


    /**
     * Функция сортирует двумерый массив по произвольному количеству полей
     * @param array $arIn Исходный массив
     * @param string $sField Ключ массива для сортировки
     * @param integer|bool $sortOrder Порядок для сортировки SORT_ASC|SORT_DESC|false
     * @return array Отсортированный масиив
     */
    public static function sortByField($arIn, $sField, $sortOrder = false)
    {
        // Копируем сортируемый массив в $arResult
        $arResult = $arIn;

        // Инициализируем массив, определяющий сортировку
        $arSortData = Array();
        foreach ($arIn as $arItem) {
            foreach ($arItem as $keyField => $valField) {
                if ($keyField == $sField) {
                    $arSortData[] = $valField;
                }
            }
        }

        if ($arSortData) {
            if ($sortOrder) {
                array_multisort($arSortData, $sortOrder, $arResult);

            } else {
                array_multisort($arSortData, $arResult);

            }

        }

        return $arResult;
    }


    /**
     * Функция сортирует двумерый массив по нужному полю
     * @param array $arIn Исходный массив
     * @param array $arFields Поля и направления сортировки
     * @return array Отсортированный масиив
     */
    public static function sortArrByFields($arIn, $arFields)
    {
        $arResult = $arIn;


        // Определение массива определяющего сортировку
        $arSortData = Array();
        foreach ($arIn as $arItem) {
            foreach ($arItem as $keyField => $valField) {
                if ($arFields[$keyField]) {
                    $arSortData[$keyField][] = $valField;
                }
            }
        }
        // ---------------------------------------------------------------------


        // Сортировка
        if ($arSortData) {
            $multiSort = Array();
            foreach ($arFields as $k => $v) {
                $multiSort[] = '$arSortData["' . $k . '"], ' . $v;
            }

            eval('array_multisort(' . implode(', ', $multiSort) . ', $arResult);');

        }

        // ---------------------------------------------------------------------


        return $arResult;
    }


    /**
     * Проверяет наличие списка ключей в массиве на первом уровне
     *
     * @param array $keys
     * @param array $data
     * @return bool
     */
    public static function isExistArrayKeys(array $keys, array $data): bool
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data) === false) {
                return false;
            }
        }

        return true;
    }

}
