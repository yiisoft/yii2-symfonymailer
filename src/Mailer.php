<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
declare(strict_types=1);

namespace yii\symfonymailer;

use RuntimeException;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Crypto\SMimeSigner;
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
    private ?SymfonyMessageEncrypterInterface $encrypter = null;

    private ?SymfonyMessageSignerInterface $signer = null;
    private array $signerOptions = [];
    /**
     * @var TransportInterface Symfony transport instance or its array configuration.
     */
    private TransportInterface $_transport;

    public Transport $transportFactory;
    /**
     * @var bool whether to enable writing of the Mailer internal logs using Yii log mechanism.
     * If enabled [[Logger]] plugin will be attached to the [[transport]] for this purpose.
     * @see Logger
     */
    public bool $_enableMailerLogging;
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
     * @deprecated This will become private
     */
    public function getSymfonyMailer(): SymfonyMailer
    {
        if (!isset($this->symfonyMailer)) {
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
        if (!is_array($transport) && !$transport instanceof TransportInterface) {
            throw new InvalidArgumentException('"' . get_class($this) . '::transport" should be either object or array, "' . gettype($transport) . '" given.');
        }

        $this->_transport = $transport instanceof TransportInterface ? $transport : $this->createTransport($transport);

        $this->symfonyMailer = null;
    }

    /**
     * @return TransportInterface
     * @deprecated This will become private
     */
    public function getTransport(): TransportInterface
    {
        if (!isset($this->_transport)) {
            throw new InvalidConfigException('No transport was configured.');
        }
        return $this->_transport;
    }

    public function init()
    {
        if (!isset($this->_enableMailerLogging)) {
            $this->setEnableMailerLogging(false);
        }
    }

    public function setEnableMailerLogging(bool $value): void
    {
        if (!isset($this->_enableMailerLogging) || $this->_enableMailerLogging !== $value) {
            $this->transportFactory = $this->createTransportFactory($value);
            $this->_enableMailerLogging = $value;
        }
    }

    private function createTransportFactory(bool $enableLogging): Transport
    {
        $logger = $enableLogging ? new Logger(\Yii::getLogger()) : null;
        $defaultFactories = Transport::getDefaultFactories(null, null, $logger);
        return new Transport($defaultFactories);
    }


    private function createTransport(array $config = []): TransportInterface
    {
        if (array_key_exists('dsn', $config)) {
            $transport = $this->transportFactory->fromString($config['dsn']);
        } elseif(array_key_exists('scheme', $config) && array_key_exists('host', $config)) {
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

    /**
     * Returns a new instance with the specified encrypter.
     *
     * @param SymfonyMessageEncrypterInterface $encrypter The encrypter instance.
     * @return self
     *@see https://symfony.com/doc/current/mailer.html#encrypting-messages
     *
     */
    public function withEncrypter(SymfonyMessageEncrypterInterface $encrypter): self
    {
        $new = clone $this;
        $new->encrypter = $encrypter;
        return $new;
    }

    /**
     * Returns a new instance with the specified signer.
     *
     * @param SymfonyMessageSignerInterface $signer The signer instance.
     * @param array $options The dynamic options for the signer, for example see DKIM signer {@see DkimSigner}.
     * @see https://symfony.com/doc/current/mailer.html#signing-messages
     * @return self
     */
    public function withSigner(SymfonyMessageSignerInterface $signer, array $options = []): self
    {
        $new = clone $this;
        $new->signer = $signer;
        $new->signerOptions = $options;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    protected function sendMessage($message): bool
    {
        if (!($message instanceof SymfonyMessageWrapperInterface)) {
            throw new InvalidArgumentException(sprintf(
                'The message must be an instance of "%s". The "%s" instance is received.',
                SymfonyMessageWrapperInterface::class,
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
