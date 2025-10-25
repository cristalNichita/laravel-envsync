<?php

namespace Fragly\LaravelEnvsync\Services;


use Fragly\LaravelEnvsync\Helpers\EnvHelper;

class EnvSyncService
{
    public function diff(
        string $examplePath,
        string $envPath
    ): array {
        $example = EnvHelper::read($examplePath);
        $current = EnvHelper::read($envPath);

        $missing = array_diff_key($example['data'], $current['data']);
        $extra   = array_diff_key($current['data'], $example['data']);

        $changedDefaults = [];
        foreach (array_intersect_key($example['data'], $current['data']) as $key => $exVal) {
            if ($exVal !== '' && $exVal === $current['data'][$key]) {
                $changedDefaults[$key] = $exVal;
            }
        }

        return [
            'missing' => $missing,
            'extra' => $extra,
            'maybe_default' => $changedDefaults,
            'example' => $example,
            'current' => $current,
        ];
    }

    public function sync(
        string $examplePath,
        string $envPath,
        bool $useExampleValues = true,
        bool $pruneExtra = false,
        bool $createBackup = true
    ): array {
        $diff = $this->diff($examplePath, $envPath);
        $lines = $diff['current']['lines'] ?: [];
        $positions = $diff['current']['positions'];

        $added = [];
        $removed = [];

        foreach ($diff['missing'] as $key => $val) {
            $value = $useExampleValues ? $val : '';
            $lines[] = EnvHelper::toLine($key, $value);
            $added[$key] = $value;
        }

        if ($pruneExtra && !empty($diff['extra'])) {
            $keysToRemove = array_keys($diff['extra']);
            $lines = array_values(array_filter($lines, function ($line) use ($keysToRemove) {
                $trim = ltrim($line);
                if ($trim === '' || str_starts_with($trim, '#')) return true;
                $candidate = $trim;
                if (str_starts_with($candidate, 'export ')) {
                    $candidate = substr($candidate, 7);
                }
                [$k,] = EnvHelper::splitKeyValue($candidate);
                return !($k && in_array($k, $keysToRemove, true));
            }));
            $removed = $diff['extra'];
        }

        if ($createBackup) {
            EnvHelper::backup($envPath);
        }

        EnvHelper::writeLines($envPath, $lines);

        return [
            'added' => $added,
            'removed' => $removed,
        ];
    }
}