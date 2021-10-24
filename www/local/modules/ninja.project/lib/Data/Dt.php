<?php

namespace Ninja\Project\Data;

use Bitrix\Main\ArgumentException as ArgumentExceptionAlias;
use Bitrix\Main\Web\Json;
use Ninja\Project\ProjectException;

class Dt
{
    private const DT_DATA_PATH = '/upload/data/dt/dt#ID#_#STREET_ID#.json';
    private static int $streetId;
    private static int $dtId;
    private const TIME_KEY = 'Time';
    private const SLICE_NUM = 32;

    public static function get(int $dtId, int $streetId, string $type): array
    {
        self::$dtId = $dtId;
        self::$streetId = $streetId;

        try {
            return self::getDataByType($type);
        } catch (ArgumentExceptionAlias | ProjectException $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @throws ProjectException
     * @throws ArgumentExceptionAlias
     */
    private static function getDataByType(string $type)
    {
        $typeMap = self::typeMap();
        if (!$typeMap[$type]) {
            throw new ProjectException('Error: No valid type');
        }

        $resultData = self::getDataFromFile();
        if (!$resultData[$typeMap[$type]]) {
            throw new ProjectException('Error: No valid json Data');
        }

        $typeCode = $typeMap[$type];
        $timeData = array_slice($resultData[self::TIME_KEY], 0, self::SLICE_NUM);
        $typeData = $resultData[$typeCode];

        $result = [];
        foreach ($timeData as $index => $date) {
            $date = str_replace('.21', '.2021', $date);
            $time = strtotime($date);

            $result[$time] = $typeData[$index];
        }

        return $result;
    }

    /**
     * @throws ArgumentExceptionAlias
     */
    private static function getDataFromFile(): array
    {
        $filePath = str_replace(['#ID#', '#STREET_ID#'], [self::$dtId, self::$streetId], self::DT_DATA_PATH);
        $json = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $filePath);

        return Json::decode($json);
    }

    private static function typeMap(): array
    {
        return [
            'traffic-congestion' => 'TrafficCongestion',
            'staking' => 'Staking',
            'trend' => 'Trend',
        ];
    }
}
