<?php

namespace Letkode\Helpers;

final class BuildTreeHelper
{

    /**
     * Obtiene un arreglo con la relación padre-hijo.
     *      Direcciones:
     *      BI => bidireccional.
     *      DESC => unidirecional DESC.
     *      ASC => unidirecional ASC.
     *      DD => Nivel directo abajo.
     *      UD => Nivel directo hacia arriba.
     *      BID => Bidireccional directo 1 nivel arriba y abajo.
     */
    public static function getTreeUserGroup(
        array     $treeGroups,
        array|int $groupBase,
        string    $direction = null,
        bool      $addGroupBase = true
    ): array {

        $groups = [];
        switch ($direction) {
            case 'DESC':
                $groups = self::buildTreeDesc($treeGroups, $groupBase);

                break;
            case 'ASC':
                $groups = self::buildTreeAsc($treeGroups, $groupBase);

                break;
            case 'DD':
                $groups = self::buildTreeDesc($treeGroups, $groupBase, true);

                break;
            case 'UD':
                $groups = self::buildTreeAsc($treeGroups, $groupBase, true);

                break;
            case 'BI':
                $groups = array_merge(
                    self::buildTreeAsc($treeGroups, $groupBase),
                    self::buildTreeDesc($treeGroups, $groupBase)
                );

                break;
            case 'BID':
                $groups = array_merge(
                    self::buildTreeAsc($treeGroups, $groupBase, true),
                    self::buildTreeDesc($treeGroups, $groupBase, true)
                );

                break;
            default:
                break;
        }

        if ($addGroupBase) {
            $groups[] = $groupBase;
        }

        return $groups;
    }

    /**
     * Obtiene un arreglo con los grupos hijos segun su relacion padre e hijos.
     *
     */
    public static function buildTreeDesc(array $treeGroups, $parent, bool $onlyDirect = false): array
    {
        $groupsChild = [];
        if ($onlyDirect) {
            $groupsChild = array_keys(array_filter($treeGroups, function ($p, $c) use ($parent) {
                return $p === $parent;
            }, ARRAY_FILTER_USE_BOTH));
        } else {
            $lastLevel = false;
            while (!$lastLevel) {
                $parentSearch = is_array($parent) ? $parent : [$parent];
                $child = array_keys(array_intersect($treeGroups, $parentSearch));
                $groupsChild[] = $child;

                $parent = $child;
                $lastLevel = count($child) === 0;
            }
            $groupsChild = array_merge([], ...$groupsChild);
        }

        return $groupsChild;
    }

    /**
     * Obtiene un arreglo con los grupos padres segun su relacion padre e hijos.
     *
     */
    public static function buildTreeAsc(array $treeGroups, $child, bool $onlyDirect = false): array
    {
        $groupsParent = [];
        if ($onlyDirect) {
            $groupsParent[] = $treeGroups[$child];
        } else {
            $firstLevel = false;
            while (!$firstLevel) {
                $childSearch = is_array($child) ? $child : [$child];
                $parent = array_values(array_intersect_key($treeGroups, array_flip($childSearch)));
                $groupsParent[] = $parent;

                $child = $parent;
                $firstLevel = count($parent) === 0;
            }
            $groupsParent = array_filter(array_merge([], ...$groupsParent));
        }

        return $groupsParent;
    }

    public static function getTreeBuilderParentAndChild(array $data): array
    {
        $dataReturn = [];
        foreach ($data as $item) {
            if (!isset($dataReturn[$item['parent_id']])) {
                $dataReturn[$item['parent_id']] = [
                    'id' => $item['parent_id'],
                    'name' => $item['parent_name'],
                    'children' => [],
                ];
            }

            $dataReturn[$item['parent_id']]['children'][$item['child_id']] = [
                'id' => $item['child_id'],
                'name' => $item['child_name']
            ];
        }

        return $dataReturn;
    }

    public static function getBuilderTreeByParent(array $data): array
    {
        $qtyLevel = count(array_unique(array_column($data, '_level')));

        $prevLevel = 0;
        $treeData = [];
        for ($i = $qtyLevel; $i >= 1; --$i) {
            ${"level_".$i} = [];

            if ($i === $qtyLevel) {
                foreach (array_filter($data, static fn($p) => "level_{$i}" === $p['_level']) as $lastLevel) {
                    ${"level_".$i}[$lastLevel['parent_id']][$lastLevel['_id']] = $lastLevel;
                }

                $prevLevel = $i;

                continue;
            }

            foreach (array_filter($data, static fn($l) => "level_{$i}" === $l['_level']) as $item) {
                if (!isset(${"level_".$prevLevel}[$item['_id']])) {
                    continue;
                }

                $item['children'] = array_values(${"level_".$prevLevel}[$item['_id']]);
                ${"level_".$i}[$item['parent_id']][$item['_id']] = $item;
            }

            if ($i === 1) {
                $treeData = ${"level_".$i};
            }

            $prevLevel = $i;
        }

        $currentValues = current(array_values($treeData));

        return $currentValues ? array_values($currentValues) : [];
    }


}