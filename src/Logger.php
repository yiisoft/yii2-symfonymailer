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
use Yii;

final class Logger implements LoggerInterface
{
    use LoggerTrait;

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
                $yiiLevel = \yii\log\Logger::LEVEL_ERROR;
                break;
            case 'notice':
            case 'warning':
                $yiiLevel = \yii\log\Logger::LEVEL_WARNING;
                break;
            case 'debug':
            case 'info':
                $yiiLevel = \yii\log\Logger::LEVEL_INFO;
                break;
            default:
                throw new InvalidArgumentException("Unknown logging level $level");
        }
        Yii::getLogger()->log($message, $yiiLevel, "PSR Logging Adapter");
    }
}
