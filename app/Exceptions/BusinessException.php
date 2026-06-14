<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    public function __construct(string $message = '', public readonly ?string $field = null, int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
