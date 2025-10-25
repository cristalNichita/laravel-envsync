<?php

namespace Fragly\LaravelEnvsync\Commands;

use Fragly\LaravelEnvsync\Services\EnvSyncService;
use Illuminate\Console\Command;

class EnvDiffCommand extends Command
{
    protected $signature = 'env:diff
        {--example=.env.example : Path to .env.example}
        {--target=.env : Path to target .env}
        {--only= : Show only one group: missing|extra|defaults}
        {--hide-defaults : Do not show same-as-default section}
        {--ignore= : Comma-separated keys/patterns to ignore (supports *)}
        {--json : Output as JSON}';

    protected $description = 'Show differences between .env.example and .env';

    public function handle(EnvSyncService $service): int
    {
        $examplePath = base_path($this->option('example'));
        $envPath     = base_path($this->option('target'));

        $diff = $service->diff($examplePath, $envPath);

        $patterns = $this->parseIgnore($this->option('ignore'));
        if ($patterns) {
            $diff['missing']       = $this->filterByIgnore($diff['missing'], $patterns);
            $diff['extra']         = $this->filterByIgnore($diff['extra'], $patterns);
            $diff['maybe_default'] = $this->filterByIgnore($diff['maybe_default'], $patterns);
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'missing'       => array_keys($diff['missing']),
                'extra'         => array_keys($diff['extra']),
                'same_as_default' => array_keys($diff['maybe_default']),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        $only = $this->option('only') ? strtolower($this->option('only')) : null;
        $showDefaults = !$this->option('hide-defaults');

        $this->info('> Checking .env consistency');
        $emptyAll = empty($diff['missing']) && empty($diff['extra']) && (empty($diff['maybe_default']) || !$showDefaults);
        if ($emptyAll) {
            $this->info('> All good, files are in sync.');
            return self::SUCCESS;
        }

        // helper to render a section as a nice table
        $render = function (string $title, array $items) {
            if (empty($items)) return;
            $this->newLine();
            $this->warn($title);
            $rows = [];
            foreach (array_keys($items) as $k) {
                $rows[] = [$k];
            }
            $this->table(['KEY'], $rows, 'compact');
        };

        if (!$only || $only === 'missing') {
            $render('! Missing in .env:', $diff['missing']);
        }

        if (!$only || $only === 'extra') {
            $render('>> Extra in .env:', $diff['extra']);
        }

        if ($showDefaults && (!$only || $only === 'defaults')) {
            $render('!! Same-as-default values (check you configured them):', $diff['maybe_default']);
        }

        // summary
        $this->newLine();
        $this->line(sprintf(
            'Summary: missing=%d, extra=%d, same-as-default=%d',
            count($diff['missing']),
            count($diff['extra']),
            $showDefaults ? count($diff['maybe_default']) : 0
        ));

        return self::SUCCESS;
    }

    /** @return array<int,string> */
    private function parseIgnore(?string $csv): array
    {
        if (!$csv) return [];
        return array_values(array_filter(array_map('trim', explode(',', $csv))));
    }

    /**
     * @param array<string,mixed> $arr
     * @param array<int,string> $patterns
     * @return array<string,mixed>
     */
    private function filterByIgnore(array $arr, array $patterns): array
    {
        if (!$patterns) return $arr;

        $match = function (string $key) use ($patterns): bool {
            foreach ($patterns as $p) {
                // превратим шаблон с * в регэксп
                $regex = '/^' . str_replace('\*', '.*', preg_quote($p, '/')) . '$/u';
                if (preg_match($regex, $key)) return true;
            }
            return false;
        };

        foreach (array_keys($arr) as $k) {
            if ($match($k)) unset($arr[$k]);
        }
        return $arr;
    }
}