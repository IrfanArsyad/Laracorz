<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AdminLog;
use App\Models\SystemLog;
use Illuminate\Console\Command;

class LogsPruneCommand extends Command
{
    protected $signature = 'logs:prune {--admin-days= : Override admin retention days} {--system-days= : Override system retention days}';

    protected $description = 'Pangkas data admin_logs dan system_logs sesuai konfigurasi retensi.';

    public function handle(): int
    {
        $adminDays = (int) ($this->option('admin-days') ?? config('logging.retention.admin', 180));
        $systemDays = (int) ($this->option('system-days') ?? config('logging.retention.system', 90));

        $adminCount = AdminLog::query()->where('created_at', '<', now()->subDays($adminDays))->delete();
        $systemCount = SystemLog::query()->where('created_at', '<', now()->subDays($systemDays))->delete();

        $this->info("Admin logs dihapus: {$adminCount}");
        $this->info("System logs dihapus: {$systemCount}");

        return self::SUCCESS;
    }
}
