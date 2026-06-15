<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AdminLog;
use Illuminate\Console\Command;

class LogsPruneCommand extends Command
{
    protected $signature = 'logs:prune {--admin-days= : Override admin retention days}';

    protected $description = 'Prune admin_logs table beyond retention.';

    public function handle(): int
    {
        $adminDays = (int) ($this->option('admin-days') ?? config('logging.retention.admin', 7));

        $adminCount = AdminLog::query()->where('created_at', '<', now()->subDays($adminDays))->delete();

        $this->info("Admin logs deleted: {$adminCount}");
        $this->line('System logs are file-based — rotate via Laravel daily channel.');

        return self::SUCCESS;
    }
}
