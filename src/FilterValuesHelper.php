<?php

namespace Letkode\Helpers;

use DateTime;

final class FilterValuesHelper
{
    public static function filterValuesBySchema(array $filtersParams, array $data): array
    {
        $inputsFilter = array_column($filtersParams['inputs'] ?? [], null, 'tag');
        $valuesFilter = $filtersParams['values'] ?? [];

        $conditions = [];
        foreach ($valuesFilter as $keyValue => $value) {
            $input = $inputsFilter[$keyValue] ?? null;
            $attr = $input['attributes'];

            $evalFilter = $attr['sql']['eval_filter'] ?? 'equal';

            if (null === $type = ($input['type'] ?? null)) {
                continue;
            }

            if ($type === 'date_range') {
                list($start, $end) = explode('|', $value);
                $value = [
                    'start' => '' !== $start ? DateTime::createFromFormat('Y-m-d', $start) : null,
                    'end' => '' !== $end ? DateTime::createFromFormat('Y-m-d', $end) : null
                ];

                $evalFilter = 'between';
                if ($value['start'] && null === $value['end']) {
                    $evalFilter = 'greater-equal';
                }
                if ($value['end'] && null === $value['start']) {
                    $evalFilter = 'less-equal';
                }
            }

            if ($type === 'list') {
                $value = $value['id'];
            }

            $keyValue = $attr['sql']['name_sql'] ?? $keyValue;

            $conditions[$keyValue] = [
                'value' => $value,
                'type' => $type,
                'eval' => $evalFilter
            ];
        }

        $ignoreByFilter = [];
        foreach ($data as $keyItem => $item) {
            foreach ($conditions as $key => $condition) {
                $val = $item[$key] ?? null;
                $type = $condition['type'] ?? 'string';
                $eval = $condition['eval'];

                $check = match ($type) {
                    'date_range' => DateHelper::isExistDateByTypeRange($eval, $val, $condition['value']['start'], $condition['value']['end']),
                    'string' => str_contains($val, $condition['value']),
                    default => $val == $condition['value'],
                };

                if (!$check) {
                    $ignoreByFilter[] = $keyItem;
                    break;
                }
            }
        }

        return array_diff_key($data, array_flip($ignoreByFilter));
    }

}