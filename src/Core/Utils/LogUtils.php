<?php

namespace App\Core\Utils;

class LogUtils
{
    /**
     * Filters the data to log from the specified array
     *
     * @param array $data The data
     * @return array
     */
    public static function filterSensitiveData(array $data) : array
    {
        return array_map(function ($elt) use ($data) {
            $name = strtolower(array_search($elt, $data));

            return (strpos($name, "password") === false) ? $elt : "********";
        }, $data);
    }

}
