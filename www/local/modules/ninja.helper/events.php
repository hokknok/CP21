<?php

use Bitrix\Main\EventManager;
use Ninja\Helper\CacheManager;


$eventManager = EventManager::getInstance();

// Инфоблок
$eventManager->addEventHandler('iblock', 'OnIBlockDelete',      [CacheManager::class, 'processingEventIBlock']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockAdd',    [CacheManager::class, 'processingEventIBlock']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockUpdate', [CacheManager::class, 'processingEventIBlock']);


// Раздел инфоблока
$eventManager->addEventHandler('iblock', 'OnAfterIBlockSectionAdd',     [CacheManager::class, 'processingEventSection']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockSectionUpdate',  [CacheManager::class, 'processingEventSection']);
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockSectionDelete', [CacheManager::class, 'processingEventSection']);


// Элемент инфоблока
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd',    [CacheManager::class, 'processingEventElement']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', [CacheManager::class, 'processingEventElement']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementDelete', [CacheManager::class, 'processingEventElement']);


// Свойства инфоблока
// $eventManager->addEventHandler('iblock', 'OnAfterIBlockPropertyAdd',    [IblockEvents::class, 'afterPropUpdate']);
// $eventManager->addEventHandler('iblock', 'OnAfterIBlockPropertyUpdate', [IblockEvents::class, 'afterPropUpdate']);
// $eventManager->addEventHandler('iblock', 'OnAfterIBlockPropertyDelete', [IblockEvents::class, 'afterPropUpdate']);
