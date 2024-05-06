<?php

namespace Woodlands\Core\Exceptions;

use Exception;
use Throwable;

final class AppException extends Exception
{
    public string $reason = "";
    public function __construct(string $message, int $code = 0, string $reason = "", Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->reason = $reason;
    }
}
