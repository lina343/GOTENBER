<?php

declare(strict_types=1);

namespace GotenbergPHP\Exception;

use Exception;

/**
 * Base exception for Gotenberg-related errors
 */
class GotenbergException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
