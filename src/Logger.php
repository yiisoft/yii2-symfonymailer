<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\symfonymailer;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Yii;
use yii\log\Logger as YiiLogger;

final class Logger implements LoggerInterface
{
    use LoggerTrait;

    private YiiLogger $logger;

    /**
     * @var array<string, int>
     */
    private array $map;

    private string $category;

    /**
     * @param array<string, int> $map
     */
    public function __construct(YiiLogger $logger, array $map = [
        LogLevel::ERROR => YiiLogger::LEVEL_ERROR,
        LogLevel::CRITICAL => YiiLogger::LEVEL_ERROR,
        LogLevel::ALERT => YiiLogger::LEVEL_ERROR,
        LogLevel::EMERGENCY => YiiLogger::LEVEL_ERROR,
        LogLevel::NOTICE => YiiLogger::LEVEL_WARNING,
        LogLevel::WARNING => YiiLogger::LEVEL_WARNING,
        LogLevel::DEBUG => YiiLogger::LEVEL_INFO,
        LogLevel::INFO => YiiLogger::LEVEL_INFO,
    ], string $category = 'app')
    {
        $this->logger = $logger;
        $this->map = $map;
        $this->category = $category;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = []): void
    {
        if (!is_string($level)) {
            throw new \InvalidArgumentException("This logger only supports string levels");
        }
        if (!isset($this->map[$level])) {
            throw new InvalidArgumentException("Unknown logging level $level");
        }

        $this->logger->log((string) $message, $this->map[$level], $this->category);
    }
}
