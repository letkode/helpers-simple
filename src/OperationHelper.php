<?php

namespace Letkode\Helpers;

final class OperationHelper
{
    public static function comparateValues(string $operator, int $a, int $b): bool
    {
        return match ($operator) {
            '<', 'lessThan' => $a < $b,
            '<=', 'max', 'lessEqualThan' => $a <= $b,
            '>', 'greaterThan' => $a > $b,
            '>=', 'min', 'greaterEqualThan' => $a >= $b,
            '===', 'identical' => $a === $b,
            '!==', 'unidentified' => $a !== $b,
            '!=', 'distinct' => $a != $b,
            default => $a == $b,
        };
    }
}