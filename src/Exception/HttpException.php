<?php

namespace COT\Exception;

use Exception;
use RuntimeException;

/**
 * Class HttpException
 *
 * @package COT
 */
final class HttpException extends RuntimeException
{
    /**
     * HttpException constructor.
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
