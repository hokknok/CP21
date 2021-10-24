<?php

namespace Ninja\Helper;

use Bitrix\Main\Application;
use Bitrix\Main\Application as BxApp;
use CFile;
use CUtil;


class File
{
    /**
     * Метод записывает данные в файл
     * @param $file
     * @param $data
     *
     * @return bool|int
     */
    public static function write($file, $data): bool
    {
        $file = new \Bitrix\Main\IO\File(BxApp::getDocumentRoot() . $file);
        return $file->putContents($data);
    }

    /**
     * Функция округляет размер файла в байтах и переводит в Кб, Мб, Гб и т.д.
     * @param int $iSize Размер файла в байтах
     * @param int $iPrecision Порядок округления. По умолчанию 2
     * @return string
     */
    public static function formatSize(int $iSize, int $iPrecision = 2): string
    {
        $sBitrixFormat = CFile::FormatSize($iSize, $iPrecision);

        if (LANGUAGE_ID === 'ru') {
            $sBitrixFormat = str_replace('.', ',', $sBitrixFormat);
        }

        if (LANGUAGE_ID === 'en') {
            $sBitrixFormat = strtoupper($sBitrixFormat);
        }

        return str_replace(' ', '&nbsp;', $sBitrixFormat);
    }


    /**
     * Функция возвращает Base64 для файла (пока только с картинками работает)
     * @param array $arParam Массив с параметрами <br>
     * <li> PATH - Путь к файлу относительно корня сайта
     * <li> SOURCE - Исходник файла
     * <li> TYPE - Тип данных, например «svg»
     * @return string Строка с закодированным в base64 файлом
     */
    public static function getBase64(array $arParam): string
    {
        $base64 = '';

        if ($arParam['PATH']) {
            $path = $_SERVER['DOCUMENT_ROOT'] . $arParam['PATH'];
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);

            if ($type === 'svg') {
                $type = 'svg+xml';
            }

            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        } elseif ($arParam['SOURCE'] && $arParam['TYPE']) {
            if ($arParam['TYPE'] === 'svg') {
                $arParam['TYPE'] = 'svg+xml';
            }

            $base64 = 'data:image/' . $arParam['TYPE'] . ';base64,' . base64_encode($arParam['SOURCE']);

        }

        return $base64;
    }


    /**
     * Функция возвращает массив с информацией о файле
     * @param int $file ID файла
     * @param int|string|null $userId
     * @return array
     */
    public static function getData(int $file, $userId = null): ?array
    {
        $arFile = CFile::GetFileArray($file);

        if (empty($arFile)) {
            return null;
        }

        $arFileInfo = pathinfo($_SERVER['DOCUMENT_ROOT'] . $arFile['SRC']);
        $arFile['EXT'] = strtoupper($arFileInfo['extension']);

        $arFile['FILE_SIZE_FORMAT'] = self::formatSize($arFile['FILE_SIZE'], 1);

        /**
         * Если передали идентификатор пользователя дополнительно проверяем принадлежность файла
         */
        if ($userId !== null && !self::checkPermission($arFile, $userId)) {
            $result = null;
        } else {
            $result = $arFile;
        }

        return $result;
    }


    /**
     * Функция возвращает массив с синформацией о файле в укороченном виде
     *
     * @param int $file ID файла
     *
     * @return array|null
     */
    public static function getDataTiny(int $file): ?array
    {
        $fileData = self::getData($file);
        if (empty($fileData)) {
            return null;
        }

        return [
            'ext'         => $fileData['EXT'],
            'id'          => $fileData['ID'],
            'name'        => $fileData['ORIGINAL_NAME'],
            'size'        => $fileData['FILE_SIZE'],
            'size_format' => $fileData['FILE_SIZE_FORMAT'],
            'src'         => $fileData['SRC'],
            'desc'        => $fileData['DESCRIPTION'],
        ];
    }


    /**
     * @param int $id
     * @return array
     */
    public static function getImageDataById(int $id): array
    {
        $fileData = CFile::GetFileArray($id);

        $result = [
            'id'     => $id,
            'src'    => $fileData['SRC'],
            'width'  => (int) $fileData['WIDTH'],
            'height' => (int) $fileData['HEIGHT'],
            'size'   => (int) $fileData['FILE_SIZE'],
            'alt'    => $fileData['DESCRIPTION'],
        ];

        // Удалим необязательные пустые поля
        $optionalFields = ['name', 'size', 'alt'];

        foreach ($optionalFields as $field) {
            if ($result[$field]) {
                continue;
            }
            unset($result[$field]);
        }

        return $result;
    }


    /**
     * Метод формирует информацию о картинке из превьюхи сформированной CFile::ResizeImageGet
     * @param $thumb
     * @return array|null
     */
    public static function getImageDataByResize($thumb): ?array
    {
        if (!is_array($thumb)) {
            return null;
        }

        return [
            'id'   => null,
            'src'  => $thumb['src'],
            'h'    => (int)$thumb['height'],
            'w'    => (int)$thumb['width'],
            'size' => (int)$thumb['size'],
            'name' => '',
            'alt'  => '',
        ];
    }


    /**
     * Функция возвращает массив пригодный для сохранения элемента
     * @param array $arFilesID Массив ID файлов из b_file
     * @param string $sModuleName Имя модуля
     * @return array Массив для CIBlockElement:Add()
     */
    public static function getFileArrayForSaveInProp(array $arFilesID, string $sModuleName = 'main'): ?array
    {
        if (!is_array($arFilesID)) {
            return null;
        }

        $arResult = array();
        foreach ($arFilesID as $key => $iFileID) {
            if (!$iFileID) continue;

            $arFileThis = CFile::MakeFileArray($iFileID);
            $arFileThis['MODULE_ID'] = $sModuleName;
            $arFileThis['name'] = CUtil::translit($arFileThis['name'], 'ru');

            if ($arFileThis) {
                $arResult['n' . $key] = array(
                    'VALUE'       => $arFileThis,
                    'DESCRIPTION' => $arFileThis['description'],
                );
            }
        }

        return $arResult;
    }


    /**
     * Возвращает информацию о файле для сохранения
     *
     * @param string|int $fileIn - путь к файлу или идентификатор
     * @param int|null $userId
     * @return array|bool|null
     */
    public static function getFileDataForSave($fileIn, int $userId = null)
    {
        $file = CFile::MakeFileArray($fileIn);

        if ($userId === null) {
            return $file;
        }

        /**
         * Если передали идентификатор пользователя дополнительно проверяем принадлежность файла
         */
        if (!self::checkPermission($file, $userId)) {
            $result = null;
        } else {
            $result = $file;
        }

        return $result;
    }


    /**
     * Функция проверяет, есть ли у пользователя права на доступ к временному файлу
     * @param array $fileData - Массив из CFile::GetFileArray
     * @param int|string $userId
     * @return bool
     */
    public static function checkPermission(array $fileData, $userId): bool
    {
        $desc = json_decode($fileData['DESCRIPTION'], true);
        $fileUserId = $desc['USER_ID'] ?? null;

        return $fileUserId === $userId;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public static function delete(int $id)
    {
        return CFile::Delete($id);
    }


    /**
     * Создает превью для изображения
     *
     * @param int $id - идентификатор файла
     * @param array{width:int,height:int} $size - размеры превью
     * @param int $type - тип масштабирования, используются Битрикс контстанты BX_RESIZE_IMAGE_EXACT|BX_RESIZE_IMAGE_PROPORTIONAL|BX_RESIZE_IMAGE_PROPORTIONAL_ALT
     * @return array|null
     */
    public static function getPreviewPicture(int $id, array $size, int $type): ?array
    {
        // Получаем данные о файле, необходимы для корректной проверки типа файла
        $file = CFile::GetFileArray($id);

        if ($file === false) {
            return null;
        }

        $file = [
            'id'       => $id,
            'name'     => $file['ORIGINAL_NAME'],
            'size'     => $file['FILE_SIZE'],
            'type'     => $file['CONTENT_TYPE'],
            'tmp_name' => Application::getDocumentRoot() . $file['SRC'],
        ];

        $preview = null;

        if (CFile::CheckImageFile($file) === null) {
            $result = CFile::ResizeImageGet($file['id'], $size, $type, true);

            if ($result !== false) {
                $preview = self::getImageDataByResize($result);
                $preview['size'] = self::formatSize($preview['size']);
            }
        }

        return $preview;
    }
}
