<?php

declare(strict_types=1);

namespace GotenbergPHP\Exception;

/**
 * Exception thrown when PDF conversion fails
 */
class ConversionException extends GotenbergException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
