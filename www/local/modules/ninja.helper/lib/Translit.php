<?php

namespace Ninja\Helper;

/**
 * Статический класс для транслитерации
 */
class Translit {

    /**
     * Транслитерирует строку
     *
     * @param  string $value Строка подлежащая транслитерации
     * @return string        Результат операции
     */
    public static function str($value = '') {
        return \Cutil::translit(
            $value,
            'ru',
            array(
                'replace_space'         => ' ',
                'replace_other'         => ' ',
                'change_case'           => false,
            )
        );
    }

    /**
     * Транлитерирует символьный код из русской строки
     *
     * @param  string $value Строка подлежащая транслитерации
     * @return string        Результат операции
     */
    public static function code($value = '') {
        return \Cutil::translit(
            $value,
            'ru',
            array(
                'replace_space'         => '-',
                'replace_other'         => '-',
                'change_case'           => 'L',
                'delete_repeat_replace' => true,
            )
        );
    }
}
