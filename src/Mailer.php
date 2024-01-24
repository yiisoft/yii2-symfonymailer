<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */
declare(strict_types=1);

namespace yii\symfonymailer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;
use yii\psr\DynamicLogger;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-type TransportHostArray array{scheme?:string, host?:string, username?:string, password?:string, port?:int, options?: array<mixed>, dsn?:string|Dsn }
 * @phpstan-type TransportConfigArray array{scheme?:string, host?:string, username?:string, password?:string, port?:int, options?: array<mixed>, dsn?:string|Dsn }
 * @extendable
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

    private function getTransportFactory(): Transport
    {
        if (isset($this->transportFactory)) {
            return $this->transportFactory;
        }
        /** @var LoggerInterface|null $logger */
        $logger = class_exists(DynamicLogger::class) ? new DynamicLogger() : null;
        $defaultFactories = Transport::getDefaultFactories(new EventDispatcherProxy($this), null, $logger);
        /** @psalm-suppress InvalidArgument Symfony's type annotation is wrong */
        return new Transport($defaultFactories);
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
