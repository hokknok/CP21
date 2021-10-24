<?php

namespace Ninja\Helper\HlBlock;

use \Exception;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\AddResult;
use \Bitrix\Main\LoaderException;
use \Bitrix\Main\SystemException;
use \Bitrix\Main\ArgumentException;
use \Bitrix\Main\Entity\DataManager;
use \Bitrix\Main\Entity\DeleteResult;
use \Bitrix\Main\Entity\UpdateResult;
use \Bitrix\Highloadblock\HighloadBlockTable;

/**
 * Class HLBWrap - класс-обертка для работы с Highload блоками по имени таблицы в БД.
 * Скрывает необходимость подготовки ORM-сущности методами compileEntity(), getDataClass().
 */
class Wrap
{
    private $tableName = "";

    /**
     * @param string $tableName - имя таблицы БД, связанной с Highload блоком.
     */
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    public function getName()
    {
        $class = static::getClass();

        print_r($class);
    }

    /**
     * Получение имени таблицы БД, связанной с Highload блоком.
     *
     * @return string - имя таблицы БД.
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Получение id highload блока по текущему имени таблицы.
     *
     * @return int - id highload блока или 0 в случае ошибки.
     * @throws LoaderException
     * @throws ArgumentException
     */
    public function getHlbId()
    {
        if(Loader::includeModule('highloadblock'))
        {
            $arHlBlock = $this->getHlbInfo();
            return intval($arHlBlock['ID']);
        }
        return 0;
    }

    /**
     * Получения списка элементов Highload блока.
     *
     * @param array $parameters - массив параметров.
     *
     * @return mixed - результат запроса.
     * @throws ArgumentException
     * @throws LoaderException
     * @throws SystemException
     */
    public function getList(array $parameters)
    {
        $class = static::getClass();
        return $class::getList($parameters);
    }

    /**
     * Добавление элемента Highload блока.
     *
     * @param array $data - данные элемента.
     *
     * @return AddResult - результат операции.
     * @throws Exception
     */
    public function add(array $data)
    {
        $class = static::getClass();
        return $class::add($data);
    }

    /**
     * Обновление элемента Highload блока.
     *
     * @param       $id   - идентификатор элемента Highload блока.
     * @param array $data - новые данные элемента Highload блока.
     *
     * @return UpdateResult - результат операции.
     * @throws Exception
     */
    public function update($id, array $data)
    {
        $class = static::getClass();
        return $class::update($id, $data);
    }

    /**
     * Удаление элемента Highload блока.
     *
     * @param $id - идентификатор элемента Highload блока.
     *
     * @return DeleteResult - результат операции.
     * @throws Exception
     */
    public function delete($id)
    {
        $class = static::getClass();
        return $class::delete($id);
    }

    /**
     * Получение ORM-сущности для работы с Highload блоком.
     *
     * @return DataManager ORM-сущность для соответствующего Highload блока.
     * @throws LoaderException
     * @throws SystemException
     */
    public function getClass()
    {
        if(Loader::includeModule('highloadblock'))
        {
            $arHLBlock = $this->getHlbInfo();
            $entity = HighloadBlockTable::compileEntity($arHLBlock);
            return $entity->getDataClass();
        }
        return null;
    }

    /**
     * Получение информации о highload блоке по текущему имени таблицы.
     *
     * @return array|false|mixed|null - информация о highload блоке.
     * @throws LoaderException
     * @throws ArgumentException
     */
    public function getHlbInfo()
    {
        if(Loader::includeModule('highloadblock'))
        {
            return HighloadBlockTable::getList([
                'filter' => [
                    '=TABLE_NAME' => $this->tableName
                ]
            ])->fetch();
        }
        return null;
    }
}
