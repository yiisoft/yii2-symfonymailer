<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
declare(strict_types=1);

namespace yii\symfonymailer;

use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;

class Mailer extends BaseMailer
{
    /**
     * @var string message default class name.
     */
    public $messageClass = Message::class;

    private ?SymfonyMailer $symfonyMailer = null;

    /**
     * @see https://symfony.com/doc/current/mailer.html#encrypting-messages
     */
    public ?MessageEncrypterInterface $encrypter = null;

    /**
     * @see https://symfony.com/doc/current/mailer.html#signing-messages
     */
    public ?MessageSignerInterface $signer = null;

    public array $signerOptions = [];

    /**
     * @var TransportInterface Symfony transport instance or its array configuration.
     */
    private TransportInterface $_transport;

    public Transport $transportFactory;

    /**
     * Creates Symfony mailer instance.
     * @return SymfonyMailer mailer instance.
     */
    private function createSymfonyMailer(): SymfonyMailer
    {
        return new SymfonyMailer($this->getTransport());
    }

    /**
     * @return SymfonyMailer Swift mailer instance
     */
    private function getSymfonyMailer(): SymfonyMailer
    {
        if (! isset($this->symfonyMailer)) {
            $this->symfonyMailer = $this->createSymfonyMailer();
        }
        return $this->symfonyMailer;
    }

    /**
     * @param array|TransportInterface $transport
     * @throws InvalidConfigException on invalid argument.
     */
    public function setTransport($transport): void
    {
        if (! is_array($transport) && ! $transport instanceof TransportInterface) {
            throw new InvalidArgumentException('"' . get_class($this) . '::transport" should be either object or array, "' . gettype($transport) . '" given.');
        }

        $this->_transport = $transport instanceof TransportInterface ? $transport : $this->createTransport($transport);

        $this->symfonyMailer = null;
    }

    private function getTransport(): TransportInterface
    {
        if (! isset($this->_transport)) {
            throw new InvalidConfigException('No transport was configured.');
        }
        return $this->_transport;
    }

    public function init()
    {
        $this->transportFactory = $this->transportFactory ?? $this->createTransportFactory();
    }

    private function createTransportFactory(): Transport
    {
        $defaultFactories = Transport::getDefaultFactories();
        return new Transport($defaultFactories);
    }

    private function createTransport(array $config = []): TransportInterface
    {
        if (array_key_exists('dsn', $config)) {
            $transport = $this->transportFactory->fromString($config['dsn']);
        } elseif (array_key_exists('scheme', $config) && array_key_exists('host', $config)) {
            $dsn = new Dsn(
                $config['scheme'],
                $config['host'],
                $config['username'] ?? '',
                $config['password'] ?? '',
                $config['port'] ?? '',
                $config['options'] ?? [],
            );
            $transport = $this->transportFactory->fromDsnObject($dsn);
        } else {
            throw new InvalidConfigException('Transport configuration array must contain either "dsn", or "scheme" and "host" keys.');
        }
        return $transport;
    }

    protected function sendMessage($message): bool
    {
        if (! ($message instanceof MessageWrapperInterface)) {
            throw new InvalidArgumentException(sprintf(
                'The message must be an instance of "%s". The "%s" instance is received.',
                MessageWrapperInterface::class,
                get_class($message),
            ));
        }

        try {
            $message = $message->getSymfonyEmail();
            if ($this->encrypter !== null) {
                $message = $this->encrypter->encrypt($message);
            }

            if ($this->signer !== null) {
                $message = $this->signer->sign($message, $this->signerOptions);
            }
            $this->getSymfonyMailer()->send($message);
        } catch (\Exception $exception) {
            Yii::getLogger()->log($exception->getMessage(), \yii\log\Logger::LEVEL_ERROR, __METHOD__);
            return false;
        }
        return true;
    }
}
