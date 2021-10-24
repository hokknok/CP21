<?php

namespace Ninja\Helper;

use DOMDocument;

class Html {
    /**
     * Возвращает данные из html-таблицы в виде массива
     *
     * @param string $tableStr Html-строка с таблицей
     * @return array|null      Результат парсинга таблицы
     */
    public static function getDataFromTable(string $tableStr): ?array {
        $result = [];

        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $tableStr);
        $dom->normalizeDocument();

        $rowCount = 0;
        foreach ($dom->getElementsByTagName('tr') as $tableRow) {
            $tdItems = $tableRow->getElementsByTagName('td');
            $emptyCols = 0;
            $values = [];

            foreach ($tdItems as $tableCell) {
                $value = self::innerXML($tableCell);
                $value = str_replace('&#13;', '', $value);
                $value = self::mb_trim($value);
                $values[] = self::minifyHtml($value);

                $emptyCols += $value ? 0 : 1;
            }

            if ($emptyCols !== count($tdItems)) {
                $result[$rowCount] = $values;
            }


            $rowCount++;
        }

        return $result ?: null;
    }


    private static function innerXML($node) {
        $doc = $node->ownerDocument;
        $frag = $doc->createDocumentFragment();
        foreach ($node->childNodes as $child) {
            $frag->appendChild($child->cloneNode(true));
        }

        return $doc->saveXML($frag);
    }


    /**
     * Метод добавляет информацию о файлах в html-текст.
     * Например,
     *        <a href="/upload/file.pdf>Документ</a>
     * превращается в
     *        <a href="/upload/file.pdf>Документ</a>
     *        <span class="file-description">(PDF, 4.6 МБ)</span>
     *
     * @param string $html Html-строка для обработки
     * @param string $lang Языковая версия
     * @return string
     */
    public static function getHtmlWithFileDataToLinks(string $html, string $lang = 'ru'): string {
        $linkPattern = '/<a href="(\/upload\/[^"]+)".*?>(.+?)<\/a>/ims';

        $resultHtml = $html;

        $matches = [];
        preg_match_all($linkPattern, $html, $matches);

        if (is_array($matches[0]) && count($matches[0])) {
            foreach ($matches[0] as $keyMatch => $linkMatched) {
                $filePath = urldecode($_SERVER['DOCUMENT_ROOT'] . $matches[1][$keyMatch]);

                $fileInfo = pathinfo($filePath);
                $fileData = \CFile::MakeFileArray($filePath, true);

                $fileExt = strtoupper($fileInfo['extension']);
                $fileSizeFormat = File::formatSize($fileData['size'], 2);

                $linkWithData = $linkMatched . ' <span class="file-description">' . '(' . $fileExt . ', ' . $fileSizeFormat . ')</span>';

                $resultHtml = str_replace($linkMatched, $linkWithData, $resultHtml);
            }
        }

        return $resultHtml;
    }


    /**
     * Функция возвращает строку с преобразованными nobr в span с css-классом `nobr`
     *
     * @param string $text - Исходная строка
     * @return string
     */
    public static function getTextWithNiceNobr(string $text): string {
        return str_replace(
            ['<nobr>', '</nobr>'],
            ['<span class="nobr">', '</span>'],
            $text
        );
    }


    /**
     * Функция trim для строк mb_
     * @param $string
     * @param string $charlist
     * @param bool $ltrim
     * @param bool $rtrim
     * @return null|string|string[]
     */
    public static function mb_trim($string, string $charlist = '\\\\s', bool $ltrim = true, bool $rtrim = true) {
        $both_ends = $ltrim && $rtrim;

        $char_class_inner = preg_replace(
            ['/[\^\-\]\\\]/S', '/\\\{4}/S'],
            ['\\\\\\0', '\\'],
            $charlist
        );

        $work_horse = '[' . $char_class_inner . ']+';
        $ltrim && $left_pattern = '^' . $work_horse;
        $rtrim && $right_pattern = $work_horse . '$';

        if ($both_ends) {
            $pattern_middle = $left_pattern . '|' . $right_pattern;

        } elseif ($ltrim) {
            $pattern_middle = $left_pattern;

        } else {
            $pattern_middle = $right_pattern;
        }

        return preg_replace("/$pattern_middle/usSD", '', $string);
    }


    public static function minifyHtml($buffer) {
        $search = array(
            '/\>[^\S ]+/s',      // strip whitespaces after tags, except space
            '/[^\S ]+\</s',      // strip whitespaces before tags, except space
            '/(\s)+/s',          // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/', // Remove HTML comments
            '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s', // Remove new lines
        );

        $replace = array(
            '>',
            '<',
            '\\1',
            '',
            '',
        );

        return preg_replace($search, $replace, $buffer);
    }

}
