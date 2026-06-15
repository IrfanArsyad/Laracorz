<?php

declare(strict_types=1);

namespace App\Support\Traits;

use DateTimeInterface;

/**
 * Override Eloquent\Model::serializeDate supaya semua datetime cast
 * keluar sebagai `Y-m-d H:i:s` di response JSON / Inertia — konsisten
 * dengan Carbon::serializeUsing yang sudah dipasang di AppServiceProvider.
 */
trait SerializesDates
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
