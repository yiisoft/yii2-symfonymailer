<?php

declare(strict_types=1);

namespace yiiunit\extensions\symfonymailer;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\mail\MessageInterface;
use yii\symfonymailer\Mailer;
use yii\symfonymailer\Message;
use yii\symfonymailer\MessageEncrypterInterface;
use yii\symfonymailer\MessageSignerInterface;

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

        $mailer = new Mailer();
        $mailer->transport = $transport;

        $message = $this->getMockBuilder(Message::class)->getMock();
        // We test if the correct transport is used
        $mailer->send($message);
    }

    public function testSendWithEncryptor(): void
    {
        $mailer = new Mailer();
        $mailer->transport = new Transport\NullTransport();

        $message = new Message();
        $message
            ->setHtmlBody('htmlbody')
            ->setFrom('test@test.com')
            ->setTo('test@test.com')
        ;
        $encrypter = $this->getMockBuilder(MessageEncrypterInterface::class)->getMock();
        $encrypter->expects($this->once())->method('encrypt')->willReturnCallback(function ($message) {
            return $message;
        });
        $mailer->encrypter = $encrypter;
        $mailer->send($message);
    }

    public function testSendWithSigner(): void
    {
        $mailer = new Mailer();
        $mailer->transport = new Transport\NullTransport();

        $message = new Message();
        $message
            ->setHtmlBody('htmlbody')
            ->setFrom('test@test.com')
            ->setTo('test@test.com')
        ;

        $signer = $this->getMockBuilder(MessageSignerInterface::class)->getMock();
        $signer->expects($this->once())->method('sign')->willReturnCallback(function ($message) {
            return $message;
        });
        $mailer->signer = $signer;
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
            'options' => [
                'ssl' => true,
            ],
        ];
        $mailer = new Mailer();

        $factory = $this->getMockBuilder(Transport\TransportFactoryInterface::class)->getMock();
        $factory->expects($this->atLeastOnce())->method('supports')->willReturn(true);
        $factory->expects($this->once())->method('create');

        $mailer->transportFactory = new Transport([$factory]);
        $mailer->setTransport($transportConfig);
    }

    public function testConfigureTransportFromArrayWithYii(): void
    {
        $transportConfig = [
            'scheme' => 'smtp',
            'host' => 'localhost',
            'username' => 'username',
            'password' => 'password',
            'port' => 465,
            'options' => [
                'ssl' => true,
            ],
        ];
        $mailer = Yii::createObject([
            'class' => Mailer::class,
            'transport' => $transportConfig,
        ]);
        $this->assertInstanceOf(Mailer::class, $mailer);
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
        $mailer->setTransport([
            'dsn' => 'null://null',
        ]);
    }

    public function testConfigureTransportFromDsnObject(): void
    {
        $mailer = new Mailer();

        $factory = $this->getMockBuilder(Transport\TransportFactoryInterface::class)->getMock();
        $factory->expects($this->atLeastOnce())->method('supports')->willReturn(true);
        $factory->expects($this->once())->method('create');

        $mailer->transportFactory = new Transport([$factory]);
        $mailer->setTransport([
            'dsn' => new \Symfony\Component\Mailer\Transport\Dsn('null', 'null'),
        ]);
    }

    public function testSendMessageThrowsOnBadMessageType(): void
    {
        $mailer = new Mailer();
        $this->expectException(InvalidArgumentException::class);
        $message = $this->getMockBuilder(MessageInterface::class)->getMock();

        $mailer->send($message);
    }
}
