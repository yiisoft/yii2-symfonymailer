<?php
declare(strict_types=1);

namespace yiiunit\extensions\symfonymailer;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\symfonymailer\Mailer;
use yii\symfonymailer\Message;

Yii::setAlias('@yii/symfonymailer', __DIR__ . '/../../../../extensions/symfonymailer');

/**
 * @covers \yii\symfonymailer\Mailer
 */
final class MailerTest extends TestCase
{
    // Tests :
    public function testSetupTransport(): void
    {
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();
        $transport->expects($this->once())->method('send');

        $mailer = new Mailer(['transport' => $transport]);

        $message = $this->getMockBuilder(Message::class)->getMock();
        // We test if the correct transport is used
        $mailer->send($message);
    }

    /**
     * @depends testSetupTransport
     */
    public function testConfigureTransportFromArray(): void
    {
        $transportConfig = [
            'scheme' => 'smtp',
            'host' => 'localhost',
            'username' => 'username',
            'password' => 'password',
            'port' => 465,
            'options' => ['ssl' => true],
        ];
        $mailer = new Mailer();

        $factory = $this->getMockBuilder(Transport\TransportFactoryInterface::class)->getMock();
        $factory->expects($this->atLeastOnce())->method('supports')->willReturn(true);
        $factory->expects($this->once())->method('create');

        $mailer->transportFactory = new Transport([$factory]);
        $mailer->setTransport($transportConfig);
    }

    public function testConfigureTransportInvalidArray(): void
    {
        $transportConfig = [

        ];
        $mailer = new Mailer();
        $this->expectException(InvalidConfigException::class);
        $mailer->setTransport($transportConfig);

    }

    public function testConfigureTransportFromString(): void
    {
        $mailer = new Mailer();

        $factory = $this->getMockBuilder(Transport\TransportFactoryInterface::class)->getMock();
        $factory->expects($this->atLeastOnce())->method('supports')->willReturn(true);
        $factory->expects($this->once())->method('create');

        $mailer->transportFactory = new Transport([$factory]);
        $mailer->setTransport(['dsn' => 'null://null']);
    }

    public function testSetTransportWithInvalidArgumentThrowsException(): void
    {
        $mailer = new Mailer();
        $this->expectException(InvalidArgumentException::class);
        $mailer->setTransport(new \stdClass());
    }

    /**
     * @deprecated This test should be removed when `getTransport` is made private
     */
    public function testGetTransportThrowsExceptionIfNotConfigured(): void
    {
        $mailer = new Mailer();
        $this->expectException(InvalidConfigException::class);
        $mailer->getTransport();
    }
}
