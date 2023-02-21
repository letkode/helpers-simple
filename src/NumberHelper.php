<?php

namespace Letkode\Helpers;

use NumberToWords\NumberToWords;

final class NumberHelper
{
    public static function convertNumberToWords($number, string $locale = 'es'): string
    {
        return NumberToWords::transformNumber($locale, $number);
    }

    public static function convertNumberToOrdinal($number): string
    {
        $units = str_split("$number");

        $words = [
            0 => ['', 'primero', 'segundo', 'tercero', 'cuarto', 'quinto', 'sexto', 'séptimo', 'octavo', 'noveno'],
            1 => [
                '',
                'décimo',
                'vigésimo',
                'trigésimo',
                'cuadragésimo',
                'quincuagésimo',
                'sexagésimo',
                'septuagésimo',
                'octogésimo',
                'nonagésimo',
            ],
            2 => [
                '',
                'centésimo',
                'ducentésimo',
                'tricentésimo',
                'cuadringentésimo',
                'quingentésimo',
                'sexcentésimo',
                'septingentésimo',
                'octingentésimo',
                'noningentésimo',
            ],
        ];

        $arr = array();
        foreach ($units as $index => $unit) {
            $w = $words[$index][$unit];
            if (!empty($w)) {
                $arr[] = $w;
            }
        }

        return implode(' ', $arr);
    }

}