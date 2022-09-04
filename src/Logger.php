<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\symfonymailer;

use Yii;
use Psr\Log\LoggerInterface;

final class Logger implements LoggerInterface
{
    /**
     * System is unusable.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        Yii::getLogger()->log($message, \yii\log\Logger::LEVEL_ERROR, __METHOD__);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        Yii::getLogger()->log($message, \yii\log\Logger::LEVEL_ERROR, __METHOD__);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        Yii::getLogger()->log($message, \yii\log\Logger::LEVEL_ERROR, __METHOD__);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function error($message, array $context = []): void
    {
        Yii::getLogger()->log($message, \yii\log\Logger::LEVEL_ERROR, __METHOD__);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        Yii::getLogger()->log($message, \yii\log\Logger::LEVEL_WARNING, __METHOD__);
    }

    /**
     * Normal but significant events.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        Yii::getLogger()->log($message, \yii\log\Logger::LEVEL_WARNING, __METHOD__);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function info($message, array $context = []): void
    {
        Yii::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO, __METHOD__);
    }

    /**
     * Detailed debug information.
     *
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        Yii::getLogger()->log($message, \yii\log\Logger::LEVEL_INFO, __METHOD__);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = []): void
    {
        switch ($level) {
            case 'error':
            case 'critical':
            case 'alert':
            case 'emergency':
                $level = \yii\log\Logger::LEVEL_ERROR;
                break;
            case 'notice':
            case 'warning':
                $level = \yii\log\Logger::LEVEL_WARNING;
                break;
            case 'debug':
            case 'info':
                $level = \yii\log\Logger::LEVEL_INFO;
                break;
            default:
                $level = \yii\log\Logger::LEVEL_INFO;
        }
        Yii::getLogger()->log($message, $level, __METHOD__);
    }
}
