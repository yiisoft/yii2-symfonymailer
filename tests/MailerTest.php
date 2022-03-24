<?php

namespace yiiunit\extensions\symfonymailer;

use Symfony\Component\Mailer\Transport\NullTransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Yii;
use yii\symfonymailer\Mailer;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;

Yii::setAlias('@yii/symfonymailer', __DIR__ . '/../../../../extensions/symfonymailer');

class MailerTest extends TestCase
{
    public function setUp(): void
    {
        $this->mockApplication([
            'components' => [
                'email' => $this->createTestEmailComponent()
            ]
        ]);
    }

    /**
     * @return Mailer test email component instance.
     */
    protected function createTestEmailComponent()
    {
        $component = new Mailer();

        return $component;
    }

    // Tests :

    public function testSetupTransport()
    {
        $mailer = new Mailer();
        $nullTransportFactory = new NullTransportFactory();
        $transport = $nullTransportFactory->create(new Dsn('null', 'null'));
        $mailer->setTransport($transport);
        $this->assertSame($transport, $mailer->getTransport(), 'Unable to setup transport!');
    }

    /**
     * @depends testSetupTransport
     */
    public function testConfigureTransport()
    {
        $mailer = new Mailer();

        $transportConfig = [
            'scheme' => 'smtp',
            'host' => 'localhost',
            'username' => 'username',
            'password' => 'password',
            'port' => 465,
            'options' => ['ssl' => true],
        ];
        $mailer->setTransport($transportConfig);
        $transport = $mailer->getTransport();
        $this->assertTrue(is_object($transport), 'Unable to setup transport via config!');
        $this->assertInstanceOf(TransportInterface::class, $transport, 'Invalid transport class');
    }

    public function testGetSymfonyMailer()
    {
        $mailer = new Mailer(['transport' => ['dsn' => 'null://null']]);
        $this->assertTrue(is_object($mailer->getSymfonyMailer()), 'Unable to get Symfony mailer instance!');
    }
}
