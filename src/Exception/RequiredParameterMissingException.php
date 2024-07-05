<?php

namespace COT\Exception;

use Exception;
use RuntimeException;

/**
 * Class RequiredParameterMissingException
 *
 * @package COT
 */
final class RequiredParameterMissingException extends RuntimeException
{
    /**
     * RequiredParameterMissingException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = 'Unexpected error', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
