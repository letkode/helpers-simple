<?php

namespace Letkode\Helpers;

final class FileDirectoryHelper
{
    private const IGNORE_FILE_PARAMS = [
        'abstract' => true,
        'hidden' => true,
    ];

    public static function getFilesInDirectory(string $directory, array $ignoreFilesAttribute = []): array
    {
        return array_filter(
            array_values(array_diff(scandir($directory), ['..', '.'])),
            function ($file) use ($directory, $ignoreFilesAttribute) {
                $filePath = realpath(sprintf('%s/%s', $directory, $file));

                return !is_dir($filePath) && !($ignoreFilesAttribute['hidden'] && $this->isHiddenFile($file));
            }
        );
    }

    public static function getFilesNameInDirectory(string $directory, array $ignoreFilesAttribute = []): array
    {
        $ignoreFilesAttribute = self::getIgnoreFileAttribute($ignoreFilesAttribute);

        $filesArray = [];
        foreach (self::getFilesInDirectory($directory, $ignoreFilesAttribute) as $file) {
            if ($ignoreFilesAttribute['abstract'] && self::isAbstractClass($file)) {
                continue;
            }

            $filesArray[] = str_replace('.php', '', $file);
        }

        return $filesArray;
    }

    public static function getClassInDirectory(string $directory, string $namespace): array
    {
        $classArray = [];
        foreach (self::getFilesInDirectory($directory) as $file) {
            if (self::isAbstractClass($file)) {
                continue;
            }

            $className = str_replace('.php', '', $file);
            $class = sprintf('%s%s', $namespace, $className);

            $classArray[$className] = $class;
        }

        return $classArray;
    }

    private static function isAbstractClass(string $fileName): bool
    {
        return preg_match('/^Abstract[A-Z]\w+$/', $fileName);
    }

    private static function isHiddenFile(string $fileName): bool
    {
        return preg_match('/^.\w+$/', $fileName);
    }

    private static function getIgnoreFileAttribute(array $ignoreFilesAttribute): array
    {
        return array_replace_recursive(self::IGNORE_FILE_PARAMS, $ignoreFilesAttribute);
    }
}
