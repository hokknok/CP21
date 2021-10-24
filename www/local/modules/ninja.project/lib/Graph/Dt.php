<?php

namespace Ninja\Project\Graph;

class Dt
{
    public static function get(array $data): array
    {
        $result = [];
        foreach ($data as $time => $value) {
            $result[] = [
                'date' => date('H:i', $time),
                //'date' => date('Y-m-d H:i', $time),
                'value' => $value,
            ];
        }

        return array_reverse($result);
    }
}
