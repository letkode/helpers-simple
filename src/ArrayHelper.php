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


    public static function strContainsInArray(string $haystack, array $needles): bool
    {
        foreach($needles as $needle) {
            if(str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }


    public static function searchByArray(array $arrayValues, array $searchArray, mixed $default = null): mixed
    {
        $currentValue = $arrayValues;
        foreach ($searchArray as $search) {
            if (isset($currentValue[$search])) {
                $currentValue = $currentValue[$search];
            }else{
                return $default;
            }
        }

        return $currentValue;
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
        return self::convertKeysArrayToFormat($values, 'toSnake');
    }

    public static function convertKeyArrayToCamelCase(array $values): array
    {
        return self::convertKeysArrayToFormat($values, 'toCamel');
    }

    private static function convertKeysArrayToFormat(array $array, string $toFormat): array
    {
        $outputArray = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                self::convertKeysArrayToFormat($value, $toFormat);
            }

            $newKey = StringHelper::stringCase($key, $toFormat);

            $outputArray[$newKey] = $value;
        }

        return $outputArray;
    }

    public static function addPrefixKeys(array $values, string $prefix): array
    {
        foreach ($values as $key => &$value) {
            if (is_array($value)) {
                $values += self::addPrefixKeys($value, $key);
                unset($values[$key]);
            }
        }

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


    public static function arrayGroupByKey(array $values, string $key): array
    {
        $data = [];
        foreach ($values as $value) {
            if (!isset($value[$key])) {
                throw new \RuntimeException('Key not found in array');
            }
            $data[$value[$key]][] = $value;
        }

        return $data;
    }

    public static function changeKeyArray(array $data, array $keys): array
    {
        foreach ($keys as $from => $to) {
            foreach ($data as &$item) {
                if (!isset($item[$from])) {
                    continue;
                }

                $item[$to] = $item[$from];
                unset($item[$from]);
            }
        }

        return $data;
    }

    public static function formatValues(array $values, string $_label = 'name', string $_key = 'tag'): array
    {
        $array = [];

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                if (!isset($value[$_key])) {
                    $value[$_key] = $key;
                }

                $array[$key] = $value;

                continue;
            }

            if (is_string($value)) {
                $array[$key] = [
                    $_label => $value,
                    $_key => $key,
                ];
            }
        }

        return $array;
    }

    public static function replaceValuesObject(
        array $object,
        array $values,
        string $pregMatch = '/(#\[)\w+(\]#)/',
        string $startMatch = '#[',
        string $endMatch = ']#'
    ): array
    {
        $array = [];
        foreach ($object as $key => $item) {
            $val = $item;
            if (preg_match($pregMatch, $item)) {
                $item = str_replace($startMatch, '', str_replace($endMatch, '', $item));
                $val = $values[$item] ?? null;
            }

            $array[$key] = $val;
        }

        return $array;
    }

    public static function replaceValuesTextGroupItems(array $items, array $array, array $values): array
    {
        $result = [];
        foreach ($items as $item) {
            if (!isset($array[$item])) {
                continue;
            }

            $result[$item] = StringHelper::replaceValuesText(
                $array[$item],
                $values
            );
        }

        return $result;
    }

    public static function dynamicArrayOtherSimpleByData(mixed $data, string $separator = '.'): array
    {
        $values = [];
        foreach ($data as $keyVal => $value) {
            $keyArray = explode($separator, $keyVal);

            $tmpArray = array_reduce(array_reverse($keyArray), function ($carry, $item) use ($value) {
                if (empty($carry)) {
                    return [$item => $value];
                }
                return array($item => $carry);
            }, []);

            $values = array_merge_recursive($values, $tmpArray);
        }

        return $values;
    }

    public static function convertArrayInUniqueKey(
        array $array,
        string $prefix = '',
        string $separator = '.',
        int $iterator = 0
    ): array {
        if (0 === $iterator) {
            $prefix = sprintf('%s%s', $prefix, $separator);
        }

        $result = [];
        foreach ($array as $key => $value) {
            $iterator++;
            // Si el valor es otro array, llamar a la función de nuevo con el prefijo actualizado
            if (is_array($value)) {
                $result = array_merge(
                    $result,
                    self::convertArrayInUniqueKey(
                        $value,
                        sprintf('%s%s%s', $prefix, $key, $separator),
                        $separator,
                        $iterator
                    )
                );
            } else {
                // Si no, asignar el valor al array simple con la clave concatenada con el prefijo
                $result[sprintf('%s%s', $prefix, $key)] = $value;
            }
        }

        // Devolver el array simple
        return $result;
    }

}