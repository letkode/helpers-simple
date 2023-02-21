<?php

namespace Letkode\Helpers;

final class OperationHelper
{
    public static function comparateValues(string $operator, mixed $valueA, mixed $valueB): bool
    {
        return match ($operator) {
            '<', 'lessThan' => $valueA < $valueB,
            '<=', 'max', 'lessEqualThan' => $valueA <= $valueB,
            '>', 'greaterThan' => $valueA > $valueB,
            '>=', 'min', 'greaterEqualThan' => $valueA >= $valueB,
            '===', 'identical' => $valueA === $valueB,
            '!==', 'unidentified' => $valueA !== $valueB,
            '!=', 'distinct' => $valueA != $valueB,
            default => $valueA == $valueB,
        };
    }
}