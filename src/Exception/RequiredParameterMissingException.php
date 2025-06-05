<?php

namespace TRSTD\COT\Exception;

use Exception;
use RuntimeException;

final class RequiredParameterMissingException extends RuntimeException
{
    /**
     * RequiredParameterMissingException constructor.
     *
     * @param string $message The message to log
     * @param int $code The error code
     * @param Exception|null $previous The previous exception
     */
    public function __construct($message = 'Unexpected error', $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
