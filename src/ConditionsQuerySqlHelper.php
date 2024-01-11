<?php

namespace Letkode\Helpers;

final class ConditionsQuerySqlHelper
{
    public static function getConditionsByType(
        bool $isQueryBuilder = true,
        array $inputsWithAttributes = [],
        array $values = [],
        bool $isValueAsParams = true
    ): array {
        $conditions = self::getConditionsQuery($inputsWithAttributes, $values, $isValueAsParams);

        if ($isQueryBuilder) {
            return $conditions;
        }

        foreach ($conditions as &$condition) {
            $condition = sprintf('AND %s', $condition);
        }

        return $conditions;
    }

    private static function getConditionsQuery(
        array $inputsWithAttributes = [],
        array $values = [],
        bool $isValueAsParams = true
    ): array {
        $conditions = [];
        foreach ($inputsWithAttributes as $key => $input) {
            if (!isset($values[$key])) {
                continue;
            }

            $sqlAttr = $input['sql'];

            $valueInput = $values[$key];
            $aliasSQL = $sqlAttr['alias'] ?? $sqlAttr['alias_sql'] ?? null;

            $nameSQL = $sqlAttr['name'] ?? $sqlAttr['name_sql'] ?? $key;
            $nameSQL = $aliasSQL ? sprintf('%s.%s', $aliasSQL, $nameSQL) : $nameSQL;

            $eval = $sqlAttr['eval'] ?? $sqlAttr['eval_filter'] ?? 'equal';
            $format = $sqlAttr['format'] ?? $sqlAttr['format_filter'] ?? 'string';

            $condition = self::getEvalConditions(
                $nameSQL,
                ($isValueAsParams ? $key : $valueInput),
                $eval,
                $format,
                $isValueAsParams
            );

            $conditions[$key] = $condition;
        }

        return $conditions;
    }

    private static function getEvalConditions(
        string $name,
        string $value,
        ?string $eval,
        ?string $format,
        bool $isValueAsParams = true
    ): string {
        $formatCondition = self::getFormatConditions($format, $isValueAsParams);

        return match ($eval) {
            'like' => sprintf($formatCondition, $name, 'LIKE', $value),
            'null' => sprintf($formatCondition, $name, 'IS NULL'),
            'noNull' => sprintf($formatCondition, $name, 'IS NOT NULL'),
            'between' => function () use ($value, $name, $formatCondition) {
                list($start, $end) = explode(' - ', $value);
                return sprintf($formatCondition, $name, $start, $end);
            },
            default => sprintf($formatCondition, $name, '=', $value),
        };
    }

    private static function getFormatConditions(?string $format, bool $isValueAsParams = true): string
    {
        return match ($format) {
            'date' => $isValueAsParams ?
                'DATE(%s) %s :%s'
                : "DATE(%s) %s '%s'",
            'dateRange' => $isValueAsParams ?
                'DATE(%s) BETWEEN :start AND :end'
                : "DATE(%s) BETWEEN '%s' AND '%s'",
            'range' => $isValueAsParams ?
                '%s BETWEEN :start AND :end'
                : '%s BETWEEN %s AND %s',
            'nullable' => '%s %s',
            'contains' => $isValueAsParams ? '%s %s %%:%s%%' : "%s %s '%%%s%%'",
            default => $isValueAsParams ? '%s %s :%s' : "%s %s '%s'",
        };
    }
}
