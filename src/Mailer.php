<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yii\symfonymailer;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\NativeTransportFactory;
use Symfony\Component\Mailer\Transport\NullTransportFactory;
use Symfony\Component\Mailer\Transport\SendmailTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;
use yii\mail\MessageInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-type TransportHostArray array{scheme?:string, host?:string, username?:string, password?:string, port?:int, options?: array<mixed>, dsn?:string|Dsn }
 * @phpstan-type TransportConfigArray array{scheme?:string, host?:string, username?:string, password?:string, port?:int, options?: array<mixed>, dsn?:string|Dsn }
 * @extendable
 *
 * @property-write TransportConfigArray|TransportInterface $transport
 */
class Mailer extends BaseMailer
{
    /**
     * @var string message default class name.
     */
    public $messageClass = Message::class;

    /**
     * @see https://symfony.com/doc/current/mailer.html#encrypting-messages
     */
    public ?MessageEncrypterInterface $encrypter = null;
    /**
     * @see https://symfony.com/doc/current/mailer.html#signing-messages
     */
    public ?MessageSignerInterface $signer = null;
    /**
     * @var array<mixed>
     */
    public array $signerOptions = [];
    /**
     * @var TransportInterface|null Symfony transport instance or its array configuration.
     */
    private ?TransportInterface $_transport = null;
    public ?Transport $transportFactory = null;

    /**
     * @param TransportConfigArray|TransportInterface $transport
     * @throws InvalidConfigException on invalid argument.
     */
    public function setTransport(array|TransportInterface $transport): void
    {
        $this->_transport = $transport instanceof TransportInterface ? $transport : $this->createTransport($transport);
    }

    private function getTransport(): TransportInterface
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck Yii2 configuration flow does not guarantee full initialisation */
        if (!isset($this->_transport)) {
            throw new InvalidConfigException('No transport was configured.');
        }
        return $this->_transport;
    }

    /**
     * @throws InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function getTransportFactory(): Transport
    {
        if (isset($this->transportFactory)) {
            return $this->transportFactory;
        }

        // Use the Yii DI container, if available.
        if (Yii::$container !== null) {
            // Optional bridges are referenced by name, since their packages may not be installed.
            $factoryClasses = [
                NullTransportFactory::class,
                SendmailTransportFactory::class,
                EsmtpTransportFactory::class,
                NativeTransportFactory::class,
                'Symfony\Component\Mailer\Bridge\Amazon\Transport\SesTransportFactory',
                'Symfony\Component\Mailer\Bridge\Azure\Transport\AzureTransportFactory',
                'Symfony\Component\Mailer\Bridge\Google\Transport\GmailTransportFactory',
                'Symfony\Component\Mailer\Bridge\Infobip\Transport\InfobipTransportFactory',
                'Symfony\Component\Mailer\Bridge\Mailchimp\Transport\MandrillTransportFactory',
                'Symfony\Component\Mailer\Bridge\Mailgun\Transport\MailgunTransportFactory',
                'Symfony\Component\Mailer\Bridge\Mailjet\Transport\MailjetTransportFactory',
                'Symfony\Component\Mailer\Bridge\OhMySmtp\Transport\OhMySmtpTransportFactory',
                'Symfony\Component\Mailer\Bridge\Postmark\Transport\PostmarkTransportFactory',
                'Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridTransportFactory',
                'Symfony\Component\Mailer\Bridge\Sendinblue\Transport\SendinblueTransportFactory',
            ];

            $factories = [];

            foreach ($factoryClasses as $factoryClass) {
                if (!class_exists($factoryClass)) {
                    continue;
                }
                $factories[] = Yii::$container->get($factoryClass);
            }
        } else {
            $factories = Transport::getDefaultFactories();
        }

        /** @psalm-var array<array-key, \Symfony\Component\Mailer\Transport\TransportFactoryInterface> $factories */
        return new Transport($factories);
    }

    /**
     * @param TransportConfigArray $config
     * @throws InvalidConfigException
     */
    private function createTransport(array $config = []): TransportInterface
    {
        $transportFactory = $this->getTransportFactory();
        if (array_key_exists('dsn', $config)) {
            if (is_string($config['dsn'])) {
                $transport = $transportFactory->fromString($config['dsn']);
            } else {
                $transport = $transportFactory->fromDsnObject($config['dsn']);
            }
        } elseif (array_key_exists('scheme', $config) && array_key_exists('host', $config)) {
            $dsn = new Dsn(
                $config['scheme'],
                $config['host'],
                $config['username'] ?? '',
                $config['password'] ?? '',
                $config['port'] ?? null,
                $config['options'] ?? [],
            );
            $transport = $transportFactory->fromDsnObject($dsn);
        } else {
            throw new InvalidConfigException('Transport configuration array must contain either "dsn", or "scheme" and "host" keys.');
        }
        return $transport;
    }

    /**
     * @param MessageInterface|MessageWrapperInterface $message
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    protected function sendMessage($message): bool
    {
        if (!($message instanceof MessageWrapperInterface)) {
            throw new InvalidArgumentException(sprintf(
                'The message must be an instance of "%s". The "%s" instance is received.',
                MessageWrapperInterface::class,
                get_class($message),
            ));
        }

        $message = $message->getSymfonyEmail();
        if ($this->encrypter !== null) {
            $message = $this->encrypter->encrypt($message);
        }

        if ($this->signer !== null) {
            $message = $this->signer->sign($message, $this->signerOptions);
        }
        $this->getTransport()->send($message);
        return true;
    }
}
