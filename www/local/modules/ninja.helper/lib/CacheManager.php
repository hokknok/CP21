<?php

declare(strict_types=1);

namespace Ninja\Helper;

use Bitrix\Main\Data\Cache;
use Ninja\Helper\Iblock\Iblock;


class CacheManager
{
    public const DIR = '/ninja.project';
    public const DEF_TTL = 3600;


    /**
     * Кэширует данные через callback и возвращает их
     *
     * @param string $dir - директория кеша
     * @param array $paramsKey - входные параметры, используются для составления идентификатора кеша
     * @param callable $callback - callback функция
     * @param int $ttl - время жизни кеша
     * @param bool $skipCache
     * @return mixed
     */
    public static function getDataCache(string $dir, array $paramsKey, callable $callback, int $ttl = self::DEF_TTL, bool $skipCache = false) {
        $dir = self::DIR . $dir;

        $result = [];

        if ($skipCache === true) {
            return $callback($result);
        }

        $cache   = Cache::createInstance();
        $cacheId = md5(serialize($paramsKey));

        if ($cache->initCache($ttl, $cacheId, $dir)) {
            $result = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $callback($result);
            $cache->endDataCache($result);
        }

        return $result;
    }


    /**
     * Очистить одну папку.
     * @param string $dir Путь к папке, например /iblock/section/list/lang/ ! Путь важно передавать вместе с константой self::CACHE_DIR !
     */
    public static function cleanDir(string $dir): void
    {
        if (!empty($dir)) {
            $cache = Cache::createInstance();
            $cache->cleanDir($dir);
        }
    }


    /**
     * @param $event
     * @param string $eventType
     */
    public static function processingEvent($event, $eventType = '') {
        $ibId = (int) $event['IBLOCK_ID'];
        $ibCode = Iblock::getIblockCodeById($ibId);
        self::cleanDir(self::DIR . '/' . $eventType . '/' . $ibCode . '/');
    }


    /**
     * @param $event
     */
    public static function processingEventIBlock($event) {
        self::processingEvent($event, 'iblocks');
    }


    /**
     * @param $event
     */
    public static function processingEventSection($event) {
        self::processingEvent($event, 'sections');
    }


    /**
     * @param $event
     */
    public static function processingEventElement($event) {
        self::processingEvent($event, 'items');
    }

}
