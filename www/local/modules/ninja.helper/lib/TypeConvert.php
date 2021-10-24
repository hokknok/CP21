<?php

namespace Ninja\Helper;


class TypeConvert
{
    /**
     * Регулярка для разбора строки в select <br>
     * 0 - исходная строка <br>
     * 1 - поле в битриксе <br>
     * 3 - тип данных <br>
     * 5 - название поля на выходе <br>
     */
    public const typePattern = '/^([\w\.]+)(:|)([\w\[\]]+|)(>|)(\w+|)$/';

    /** @var array Массив строк - чистый SELECT */
    private $select = [];

    /** @var array Массив типов данных для select */
    private $types = [];


    public function __construct(array $select = [])
    {
        foreach ($select as $field) {
            /**
             * 0 - исходная строка
             * 1 - поле в битриксе
             * 3 - тип данных
             * 5 - название поля на выходе
             */
            $fieldMatches = [];

            if (preg_match(self::typePattern, $field, $fieldMatches)) {
                $this->select[] = $fieldMatches[1];

                $bxFieldName = $fieldMatches[1];

                if (false !== strpos($bxFieldName, 'PROPERTY_')) {
                    // Если это не поле элемента, а его свойство:

                    $isLinkToAnother = (bool)strpos($bxFieldName, '.');
                    $isLinkToProperty = (bool)strpos($bxFieldName, '.PROPERTY_');

                    if (!$isLinkToAnother || $isLinkToProperty) {
                        // И это не свойство привязки или привязка к другому свойству, то добавим _VALUE
                        $bxFieldName = $fieldMatches[1] . '_VALUE';
                    }
                }

                $this->types[] = [$bxFieldName, $fieldMatches[3], $fieldMatches[5]];
            }
        }
    }


    public function getSelect(): array
    {
        return $this->select ?: [];
    }


    public function getTypes(): array
    {
        return $this->types ?: [];
    }


    public function getPropertyTypes(): array
    {
        $result = [];
        foreach ($this->getTypes() as $type) {
            if (preg_match('/^PROPERTY_(\w+)_VALUE$/', $type[0], $matches)) {
                $propCode = $matches[1];
                if (!empty($propCode)) {
                    $result[$propCode] = $type[2];
                }
            }
        }
        return $result;
    }


    /**
     * Метод конвертирует результаты getList-а по заданным в SELECT типам
     *
     * Возможные типы данных
     *  <li> string | string[]
     *  <li> int | int[]
     *  <li> float | float[]
     *  <li> bool
     *  <li> Timestamp
     *  <li> Date
     *  <li> DateHuman
     *  <li> DescriptiveString[]
     *  <li> EnumBool // depend from `'GET_ENUM_CODE' => 'Y'`
     *  <li> EnumCode // depend from `'GET_ENUM_CODE' => 'Y'`
     *  <li> File | File[]
     *  <li> Image | Image[]
     *  <li> Html
     *  <li> Map
     *  <li> Table | Table[]
     *  <li> Tags
     *  <li> X - Удалить поле из финальной выдачи
     *
     * @param array $items
     * @return array
     */
    public function convertDataTypes(array $items = [])
    {
        $result = [];

        foreach ($items as $k => $item) {
            foreach ($this->types as $type) {
                $value = null;

                if (isset($item[$type[0]])) {
                    $value = $item[$type[0]];
                } elseif (false !== strpos($type[0], '.')) {
                    // Если это свойство привязки
                    $itemField = str_replace('.', '_', $type[0]);
                    $value = $item[$itemField];
                }

                $fieldType = $type[1] ?: 'string';
                $fieldName = $type[2] ?: $type[0];

                $descriptionPropKey = str_replace('_VALUE', '_DESCRIPTION', $type[0]);

                if ($fieldType === 'string[]') {
                    $newValue = [];

                    foreach ($value as $string) {
                        $newValue[] = (string)$string;
                    }

                    $value = $newValue;
                } elseif ($fieldType === 'int') {
                    $value = is_string($value) || !empty($value) ? (int)$value : null;
                } elseif ($fieldType === 'int[]') {
                    $newValue = [];

                    if ($value !== null && !is_array($value)) {
                        $value = [$value];
                    }

                    foreach ($value as $number) {
                        $newValue[] = (int)$number;
                    }

                    $value = $newValue;
                } elseif ($fieldType === 'float') {
                    $value = (float)$value;
                } elseif ($fieldType === 'float[]') {
                    $newValue = [];
                    foreach ($value as $number) {
                        $newValue[] = (float)$number;
                    }
                    $value = $newValue;
                } elseif ($fieldType === 'bool') {
                    $value = (bool)$value;
                } elseif ($fieldType === 'timestamp') {
                    $value = strtotime($value);
                } elseif ($fieldType === 'Date') {
                    $value = \Bitrix\Main\Type\Date::createFromText($value);
                } elseif ($fieldType === 'DateHuman') {
                    if (!empty($value)) {
                        $dateFormat = 'MMMM YYYY';
                        $value = Date::formatDateHuman($value, $dateFormat);
                    }
                } elseif ($fieldType === 'EnumBool') {
                    $fieldNameEnum = str_replace('_VALUE', '_XML_ID', $type[0]);
                    $value = ($item[$fieldNameEnum] === 'Y');
                } elseif ($fieldType === 'EnumCode') {
                    $fieldNameEnum = str_replace('_VALUE', '_XML_ID', $type[0]);
                    $value = $item[$fieldNameEnum];
                } elseif ($fieldType === 'DescriptiveString[]') {
                    $dataFormatted = [];
                    foreach ($value as $valueKey => $valueData) {
                        $dataFormatted[] = [
                            'value'       => $valueData,
                            'description' => $item[$descriptionPropKey][$valueKey],
                        ];
                    }
                    $value = $dataFormatted;

                } elseif ($fieldType === 'File') {
                    $value = is_numeric($value)
                        ? File::getDataTiny((int)$value)
                        : null;

                } elseif ($fieldType === 'File[]') {
                    $valueNew = [];

                    foreach ($value as $fileId) {
                        $valueNew[] = is_numeric($fileId)
                            ? File::getDataTiny((int)$fileId)
                            : null;
                    }

                    $value = $valueNew;
                } elseif ($fieldType === 'Image') {
                    if (!empty($value)) {
                        $value = File::getImageDataById($value);
                    }
                } elseif ($fieldType === 'Image[]' && is_array($value)) {
                    $valueNew = [];

                    foreach ($value as $imageId) {
                        $valueNew[] = File::getImageDataById($imageId);
                    }

                    $value = $valueNew;
                } elseif ($fieldType === 'Html') {
                    $value = $value['TYPE'] === 'HTML' ? $value['TEXT'] : TxtToHTML($value['TEXT']);
                } elseif ($fieldType === 'Map') {
                    $coordinates = explode(',', $value);
                    $value = $value ? [(float)$coordinates[0], (float)$coordinates[1]] : null;
                } elseif ($fieldType === 'Table' && $value['TYPE'] === 'HTML' && $value['TEXT']) {
                    $value = Html::getDataFromTable($value['TEXT']);
                } elseif ($fieldType === 'Table[]' && count($value)) {
                    $valueFormatted = [];
                    foreach ($value as $bitrixText) {
                        $valueFormatted[] = Html::getDataFromTable($bitrixText['TEXT']);
                    }

                    $value = $valueFormatted;
                } elseif ($fieldType === 'Tags') {
                    $tagsInArray = explode(',', $value);

                    $value = [];

                    if ($value) {
                        foreach ($tagsInArray as $tag) {
                            $value[] = trim($tag);
                        }
                    }
                } elseif ($fieldType === 'DescriptiveTable[]') {
                    $dataFormatted = [];
                    foreach ($value as $valueKey => $valueData) {
                        $dataFormatted[] = [
                            'value'       => Html::getDataFromTable($valueData['TEXT']),
                            'description' => $item[$descriptionPropKey][$valueKey],
                        ];
                    }

                    $value = $dataFormatted;
                }

                if ($fieldType !== 'X' && !empty($value)) {
                    $result[$k][$fieldName] = $value;
                }
            }
        }

        return $result;
    }
}
