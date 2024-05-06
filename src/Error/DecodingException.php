<?php

namespace Woodlands\Core\Error;

use Exception;
use Throwable;

final class DecodingException extends Exception
{
    public function __construct(string $message = "Decoding Exception", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
