<?php

namespace Letkode\Helpers;

use Jawira\CaseConverter\Convert;
use RuntimeException;

final class StringHelper
{

    public function convertValueToBoolean(string $value): bool
    {
        return match (strtoupper($value)) {
            'SI', 'TRUE', '1' => true,
            'NO', 'FALSE', '0' => false
        };
    }

    public static function pluralize($number, $base, $plural = null): string
    {
        $number = (int)$number;

        if (0 === abs($number) || abs($number) > 1) {
            if (true === is_null($plural)) {
                if (in_array(substr($base, -1, 1), ['a', 'e', 'i', 'o', 'u'])) {
                    $salida = $base.'s';
                } else {
                    $salida = $base.'es';
                }
            } else {
                $salida = $plural;
            }
        } else {
            $salida = $base;
        }

        return sprintf('%d %s', $number, $salida);
    }

    public static function getterByString(string $tag): string
    {
        return self::stringToCase(sprintf('get_%s', $tag), 'camel', '_');
    }

    public static function setterByString(string $tag): string
    {
        return self::stringToCase(sprintf('set_%s', $tag), 'camel', '_');
    }

    public static function slugify($string, $separator = '-', $nullable = false): ?string
    {
        if (!is_string($string)) {
            $string = "$string";
        }

        setlocale(LC_ALL, 'en_US.UTF8');

        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8');
        }

        $string = preg_replace('~[^\\pL\d]+~u', $separator, $string);
        $string = trim($string, $separator);
        $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
        $string = strtolower($string);
        $string = preg_replace('~[^'.$separator.'\w]+~', '', $string);

        if (empty($string)) {
            return $nullable ? null:'n-a';
        }

        return $string;
    }

    public static function stringToCase(string $string, string $case, bool $hasClear = true): string
    {
        $formats = ['camel', 'pascal', 'snake', 'kebab', 'dot', 'train', 'cobol', 'ada', 'macro', 'title'];

        if (!in_array($case, $formats)) {
            throw new RuntimeException(sprintf('The %s format is not available for conversion', $case));
        }

        if ($hasClear) {
            $string = self::cleanSpecialCharacters($string, false);
        }

        $method = sprintf('to%s', ucfirst($case));

        return (new Convert($string))->{$method}();
    }

    public static function toUTF8(string $string): string
    {
        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8');
        }

        return trim($string);
    }

    public static function normalizeString(string $string): string
    {
        $table = [
            'Š' => 'S',
            'š' => 's',
            'Đ' => 'Dj',
            'đ' => 'dj',
            'Ž' => 'Z',
            'ž' => 'z',
            'Č' => 'C',
            'č' => 'c',
            'Ć' => 'C',
            'ć' => 'c',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'B',
            'ß' => 'Ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'o',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'þ' => 'b',
            'ÿ' => 'y',
            'Ŕ' => 'R',
            'ŕ' => 'r',
        ];

        return strtr($string, $table);
    }

    public static function clearSpaceWhite(string $string): string
    {
        return preg_replace("/\s+/", " ", trim(preg_replace("[\n|\r|\n\r]", "", $string)));
    }

    public static function cleanSpecialCharacters(
        string $string,
        bool $space = true,
        bool $sign = true,
        array $excludeSign = []
    ): string {
        $string = self::toUTF8(self::normalizeString($string));

        $string = strip_tags($string);
        $string = html_entity_decode($string);

        if ($space) {
            $string = str_replace(" ", "_", $string);
        }

        if ($sign) {
            $signArray = array_diff(
                [
                    "\\",
                    "¨",
                    "º",
                    "-",
                    "~",
                    "#",
                    "@",
                    "|",
                    "!",
                    "\"",
                    "®",
                    "°",
                    "·",
                    "$",
                    "%",
                    "&",
                    "/",
                    "(",
                    ")",
                    "?",
                    "'",
                    "¡",
                    "¿",
                    "[",
                    "^",
                    "]",
                    "+",
                    "}",
                    "{",
                    "¨",
                    "´",
                    ">",
                    "< ",
                    ";",
                    ",",
                    ":",
                    ".",
                    '\\n',
                    '\\t',
                ],
                $excludeSign
            );

            $string = str_replace(
                $signArray,
                '',
                $string
            );
        }

        return $string;
    }

    public static function generateHashRandom(int $length = 32): string
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_";

        return substr(str_shuffle($chars), 0, $length);
    }

}