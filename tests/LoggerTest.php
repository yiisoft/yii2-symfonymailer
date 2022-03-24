<?php

declare(strict_types=1);

namespace yiiunit\extensions\symfonymailer;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use yii\log\Logger;

/**
 * @covers \yii\symfonymailer\Logger
 */
final class LoggerTest extends TestCase
{
    public function testLogUsesMap(): void
    {
        $yiiLogger = $this->getMockBuilder(Logger::class)->getMock();
        $yiiLogger->expects($this->once())->method('log')->with('test', Logger::LEVEL_INFO, );

        $logger = new \yii\symfonymailer\Logger($yiiLogger, [
            LogLevel::CRITICAL => Logger::LEVEL_INFO,
        ]);

        $logger->log(LogLevel::CRITICAL, 'test');
    }

    public function testInvalidLogLevel(): void
    {
        $yiiLogger = $this->getMockBuilder(Logger::class)->getMock();
        $logger = new \yii\symfonymailer\Logger($yiiLogger);

        $this->expectException(InvalidArgumentException::class);
        $logger->log('badlevel', 'test');
    }
}
