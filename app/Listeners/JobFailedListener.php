<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;

class JobFailedListener
{
    public function handle(JobFailed $event): void
    {
        Log::channel('queue')->error('Job failed: '.$event->job->resolveName(), [
            'event' => 'job_failed',
            'job' => $event->job->resolveName(),
            'exception' => $event->exception->getMessage(),
        ]);
    }
}
