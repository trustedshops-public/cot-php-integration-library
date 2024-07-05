<?php

namespace COT;

/**
 * Class Logger
 * @package COT
 */
class Logger
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * Logger constructor.
     *
     * @param string $name
     */
    public function __construct($name = 'COT')
    {
        $this->logger = new \Monolog\Logger($name);
    }

    /**
     * @param string $message - The message to log
     */
    public function error($message)
    {
        $this->logger->error($message);
    }

    /**
     * @param string $message - The message to log
     */
    public function warning($message)
    {
        $this->logger->warning($message);
    }

    /**
     * @param string $message - The message to log
     */
    public function info($message)
    {
        $this->logger->info($message);
    }

    /**
     * @param string $message - The message to log
     */
    public function debug($message)
    {
        $this->logger->debug($message);
    }
}
