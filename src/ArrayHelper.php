<?php

namespace Letkode\Helpers;

use Exception;

final class ArrayHelper
{

    public static function sortByKey(array $values, string $key): array
    {
        uasort($values, static fn($a, $b) => ($a[$key] ?? 0) <=> ($b[$key] ?? 0));

        return $values;
    }

    public static function convertValuesToDoctrine($values): array
    {
        $returnValues = [];
        array_walk($values, function (&$value, $key) use (&$returnValues) {
            $key = StringHelper::stringCase($key, 'lCamel', '_');

            $returnValues[$key] = $value;
        });

        return $returnValues;
    }

    public static function convertKeyArrayToSnakeCase(array $values): array
    {
        return self::convertKeysArrayToFormat($values, 'snake');
    }

    public static function convertKeyArrayToCamelCase(array $values): array
    {
        return self::convertKeysArrayToFormat($values, 'camel');
    }

    private static function convertKeysArrayToFormat(array $array, string $toFormat): array
    {
        $outputArray = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                self::convertKeysArrayToFormat($value, $toFormat);
            }

            $newKey = StringHelper::stringToCase($key, $toFormat);

            $outputArray[$newKey] = $value;
        }

        return $outputArray;
    }

    public static function addPrefixKeys(array $values, string $prefix): array
    {
        return array_combine(
            array_map(fn($k) => sprintf('%s_%s', $prefix, $k), array_keys($values)),
            array_values($values)
        );
    }

    public static function rangeColsDynamic(int $count): array
    {
        $range = range('A', 'Z');
        $qtyRange = count($range);
        $array = [];
        $loop = ceil($count / $qtyRange);
        $prefix = '';
        $qty = 0;

        for ($index = 1; $index <= $loop; ++$index) {
            foreach ($range as $rng) {
                ++$qty;

                if ($qty > $count) {
                    break;
                }

                $array[] = $prefix.$rng;
            }

            $prefix = $range[$index - 1];
        }

        return $array;
    }

}