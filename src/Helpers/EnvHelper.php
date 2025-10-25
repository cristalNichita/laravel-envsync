<?php

namespace Fragly\LaravelEnvsync\Helpers;

class EnvHelper
{
    public static function read(string $path): array
    {
        $lines = file_exists($path)
            ? file($path, FILE_IGNORE_NEW_LINES) ?: []
            : [];

        $data = [];
        $positions = [];

        foreach ($lines as $i => $line) {
            $trim = ltrim($line);
            if ($trim === '' || str_starts_with($trim, '#')) continue;

            $line = str_starts_with($trim, 'export ') ? substr($trim, 7) : $line;

            [$k, $v] = self::splitKeyValue($line);
            if ($k === null) continue;

            $data[$k] = $v ?? '';
            $positions[$k] = $i;
        }

        return ['data' => $data, 'lines' => $lines, 'positions' => $positions];
    }

    public static function splitKeyValue(string $line): array
    {
        $pos = strpos($line, '=');
        if ($pos === false) return [null, null];

        $key = rtrim(substr($line, 0, $pos));
        $value = substr($line, $pos + 1);

        $key = preg_replace('/\s+/', '', $key);

        $value = trim($value);

        // remove surrounding quotes but keep escapes
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $q = $value[0];
            $value = substr($value, 1, -1);
            if ($q === '"') {
                $value = stripcslashes($value);
            }
        }

        return [$key !== '' ? $key : null, $value];
    }

    public static function toLine(string $key, string $value): string
    {
        if ($value === '' || preg_match('/\s|"|\'|#/', $value)) {
            $escaped = addcslashes($value, "\\\"\n\r\t");
            return $key.'="'.$escaped.'"';
        }
        return $key.'='.$value;
    }

    public static function backup(string $path): ?string
    {
        if (!file_exists($path)) return null;
        $suffix = date('Ymd-His');
        $backup = $path.'.bak.'.$suffix;
        copy($path, $backup);
        return $backup;
    }

    public static function writeLines(string $path, array $lines): void
    {
        file_put_contents($path, rtrim(implode(PHP_EOL, $lines)).PHP_EOL);
    }
}