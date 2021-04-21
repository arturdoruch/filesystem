<?php

namespace ArturDoruch\Filesystem\Exception;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class IOException extends \RuntimeException
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $message
     * @param string $path Path to the file or directory, on which the operation was performed.
     * @param string|null $reason Filesystem operation error reason.
     */
    public function __construct(string $message, string $path, string $reason = null)
    {
        $this->path = $path;
        parent::__construct($message . ' ' . ($reason ?: self::getErrorMessage()));
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    private static function getErrorMessage(): string
    {
        $message = error_get_last()['message'];

        return ucfirst(substr($message, strpos($message, '):') + 3));
    }
}
