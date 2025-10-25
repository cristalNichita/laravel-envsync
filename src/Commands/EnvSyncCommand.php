<?php

namespace Fragly\LaravelEnvsync\Commands;

use Fragly\LaravelEnvsync\Services\EnvSyncService;
use Illuminate\Console\Command;

class EnvSyncCommand extends Command
{
    protected $signature = 'env:sync
        {--example=.env.example : Path to .env.example}
        {--target=.env : Path to target .env}
        {--empty : Use empty values instead of .env.example defaults}
        {--prune : Remove keys that are not present in .env.example}
        {--no-backup : Do not create .env backup before writing}
        {--yes : Non-interactive (assume yes) for CI/CD}
        {--json : Output result as JSON}';

    protected $description = 'Synchronize .env with .env.example (safe and CI/CD friendly).';

    public function handle(EnvSyncService $service): int
    {
        $examplePath = base_path($this->option('example'));
        $envPath = base_path($this->option('target'));

        $diff = $service->diff($examplePath, $envPath);

        $missingCount = count($diff['missing']);
        $extraCount   = count($diff['extra']);

        if ($missingCount === 0 && (!$this->option('prune') || $extraCount === 0)) {
            $this->info('Nothing to sync.');
            return self::SUCCESS;
        }

        $this->line("! Missing: {$missingCount}, Extra: {$extraCount}".($this->option('prune') ? ' (will prune)' : ''));

        if (!$this->option('yes')) {
            if (!$this->confirm('Proceed with synchronization?', true)) {
                $this->warn('Aborted.');
                return self::INVALID;
            }
        }

        $result = $service->sync(
            $examplePath,
            $envPath,
            useExampleValues: !$this->option('empty'),
            pruneExtra: (bool)$this->option('prune'),
            createBackup: !$this->option('no-backup')
        );

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        if (!empty($result['added'])) {
            $this->info("\n+ Added:");
            foreach ($result['added'] as $k => $v) {
                $shown = $v === '' ? '""' : (mb_strlen($v) > 40 ? substr($v, 0, 37).'…' : $v);
                $this->line("  • {$k} = {$shown}");
            }
        }

        if (!empty($result['removed'])) {
            $this->info("\n- Removed (pruned):");
            foreach (array_keys($result['removed']) as $k) {
                $this->line("  • {$k}");
            }
        }

        $this->info("\n> Sync complete.");
        return self::SUCCESS;
    }
}