<?
namespace Ninja\Helper\Structure;

/**
 * Класс реализующий структуру дерева
 * Предки хранятся указателем на другой элемент массива. То есть, дерево создаётся за один проход массива (без учета создания этого массива с особыми ключами)
 *
 * В общем виде структура выглядит так:
 * [id, parent, items, data]
 */
class Tree
{
    /**
     * Дерево в виде массива (идентификаторы — ключи в хеш-таблице)
     *
     * @var array
     */
    public $list = [];

    /**
     * Дерево в виде дерева
     *
     * @var array
     */
    public $tree = [];

    public function setPropertyById($id = 0, $key = '', $value = null)
    {
        $this->list[$id]['data'][$key] = $value;
    }

    /**
     * Добавить новый элемент в виде структуры
     *
     * @param array   $data   Данные для встраивания
     * @param integer $id     Идентификатор
     * @param integer $parent Идентификатор отца
     */
    public function addListItem($data = [], $id = 0, $parent = 0)
    {
        $this->list[$id] = [
            'id'     => $id,
            'parent' => $parent,
            'data'   => $data,
            'items'  => [],
        ];
    }

    /**
     * Установить новый список
     *
     * @param array  $data      Данные для встраивания
     * @param string $idKey     Ключ из данных, который будет использоваться как идентификатор
     * @param string $parentKey Ключ, который будет использоваться для именования отцовского элемента
     */
    public function setList($data = [], $idKey = 'id', $parentKey = 'parent')
    {
        $this->list = [];

        foreach ($data as $item) {
            $this->addListItem($item, $item[$idKey], $item[$parentKey]);
        }
    }

    /**
     * Нарастить в дереве указанный дочерний элемент
     *
     * @param integer $id     Идентификатор элемента
     * @param integer $parent Идентификатор отцовского элемента
     * @param array   $data   Данные для встраивания
     */
    public function addItem($id = 0, $parent = 0, $data = null)
    {
        if (!isset($this->list[$id])) {
            $this->addListItem($data, $id, $parent);
        }

        if (intval($parent) === 0 || !isset($this->list[$parent])) {
            $this->tree[] = &$this->list[$id];
        } else {
            $parent = &$this->list[$parent];
            $parent['items'][] = &$this->list[$id];
        }
    }

    /**
     * Метод возвращает элемент стуктуры по указанному ключу и его значению
     *
     * @param  string      $key   Ключ для поиска
     * @param  string|null $value Значение для поиска
     * @return array              Элемент структуры дерева
     */
    public function getlItemByDataKey($key = '', $value = null)
    {
        foreach ($this->list as $item) {
            if ($item['data'][$key] === $value) {
                return $item;
            }
        }
        return [];
    }

    /**
     * Метод возвращает найденный элемент
     *
     * @param  string      $key   Ключ для поиска
     * @param  string|null $value Значение для поиска
     * @return array              Найденный элемент
     */
    public function getDataByDataKey($key = '', $value = null)
    {
        $result = [];

        $item = $this->getlItemByDataKey($key, $value);
        $list = [$item];

        for ($i = 0; $i < count($list); $i++) {
            $item = $list[$i];
            if (!empty($item['items'])) {
                $list = array_merge($list, $item['items']);
            }
            $result[] = $item['data'];
        }

        return $result;
    }

    /**
     * Метод возвращает найденный элемент
     *
     * @param  string             $key   Ключ для поиска
     * @param  string|array|null  $value Значение для поиска
     * @return array              Найденный элемент
     */
    public function getDataByDataKeys($key = '', $value = [])
    {
        $result = [];

        foreach ($value as $item) {
            $result[] = $this->getDataByDataKey($key, $item);
        }

        return $result;
    }

    /**
     * Метод возвращает массив найденных данных начиная с корня дерева и заканчивая найденным элементом
     *
     * @param  string      $key   Ключ для поиска
     * @param  string|null $value Значение для поиска
     * @return array              Массив найденных данных
     */
    public function getBranch($key = '', $value = null)
    {
        $result = array();

        while (!is_null($value)) {
            $needItem = $this->getlItemByDataKey($key, $value);
            $value    = $needItem['parent'];
            $result[] = $needItem['data'];
        }

        return array_reverse($result);
    }

    /**
     * Метод создаёт дерево из массива
     *
     * @param  array                      $data      Массив данных
     * @param  string                     $idKey     Ключ из данных, который будет использоваться как идентификатор
     * @param  string                     $parentKey Ключ, который будет использоваться для именования отцовского элемента
     * @return \ALS\Helper\Structure\Tree            Этот объект
     */
    public static function createTree($data = [], $idKey = 'id', $parentKey = 'parent')
    {
        $result = new self();

        $result->setList($data, $idKey, $parentKey);

        foreach ($data as $item) {
            $result->addItem($item[$idKey], $item[$parentKey], $item);
        }

        return $result;
    }

    public function getListWhere($keyArg = '', $needed = null)
    {
        $result = [];

        foreach ($this->list as $key => $item) {
            if ($item['data'][$keyArg] == $needed) {
                $result[] = $item['data'];
            }
        }

        return $result;
    }

    public function getElemWithChild($keyArg = '', $needed = null)
    {
        $arFoundElem = [];
        $result      = [];

        foreach ($this->list as $key => $item) {
            if (is_array($keyArg)) {
                $isNeedNode = 0;
                foreach ($keyArg as $neededKey => $neededValue) {
                    if ($item['data'][$neededKey] == $neededValue) {
                        $isNeedNode = 1;
                    } else {
                        $isNeedNode = -1;
                        break;
                    }
                }
                if ($isNeedNode === 1) {
                    $arFoundElem[] = $item;
                }
            } else {
                if ($item['data'][$keyArg] == $needed) {
                    $arFoundElem[] = $item;
                }
            }
        }

        for ($i = 0; $i < count($arFoundElem); $i++) {
            $result[] = $arFoundElem[$i]['data'];
            foreach ($arFoundElem[$i]['items'] as $value) {
                $arFoundElem[] = $value;
            }
        }

        return $result;
    }
}
