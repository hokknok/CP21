<?php


namespace Ninja\Project;

use OverflowException;

/**
 * Класс с типизированными параметрами.
 */
class ParamType {
    private static $instance;

    private $data = [];


    private function __construct() {
    }


    private function __clone() {
    }


    private function __wakeup() {

    }


    public static function getInstance(): ParamType {

        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }

        return self::$instance;
    }


    /**
     * Вернет значение параметра
     *
     * @param string $name
     * @return mixed
     */
    public function get(string $name) {
        return $this->data[$name] ?? null;
    }


    /**
     * Вернет значение параметра в типе string
     *
     * @param string $name
     * @return string|null
     */
    public function getString(string $name): ?string {
        return is_string($this->get($name)) ? $this->get($name) : null;
    }


    /**
     * Вернет значение параметра в типе int
     *
     * @param string $name
     * @return int|null
     */
    public function getInt(string $name): ?int {
        return is_numeric($this->get($name)) ? (int)$this->get($name) : null;
    }


    /**
     * Вернет значение параметра в типе float
     *
     * @param string $name
     * @return float|null
     */
    public function getFloat(string $name): ?float {
        return is_numeric($this->get($name)) ? (float)$this->get($name) : null;
    }


    /**
     * Вернет значение параметра в типе array
     *
     * @param string $name
     * @return array|null
     */
    public function getArray(string $name): ?array {
        return is_array($this->get($name)) ? $this->get($name) : null;
    }


    /**
     * Вернет значение параметра в типе bool
     *
     * @param string $name
     * @return bool|null
     */
    public function getBool(string $name): ?bool {
        return is_bool($this->get($name)) ? (bool) $this->get($name) : null;
    }


    /**
     * Установка параметра
     *
     * @param string|int $name имя параметра
     * @param mixed @value значение параметра
     * @throws OverflowException
     */
    public function set($name, $value): void {
        if (isset($this->data[$name])) {
            throw new OverflowException('Param "' . $name . '" defined');
        }

        $this->data[$name] = $value;
    }
}
