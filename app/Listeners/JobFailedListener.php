<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\SystemLogService;
use Illuminate\Queue\Events\JobFailed;

class JobFailedListener
{
    public function __construct(private readonly SystemLogService $logger) {}

    public function handle(JobFailed $event): void
    {
        $this->logger->log(
            level: 'error',
            channel: 'queue',
            message: 'Job gagal: '.$event->job->resolveName(),
            context: ['event' => 'job_failed', 'job' => $event->job->resolveName()],
            exception: $event->exception,
        );
    }
}
