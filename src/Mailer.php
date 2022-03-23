<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\symfonymailer;

use RuntimeException;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Crypto\SMimeEncrypter;
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
    private ?SMimeEncrypter $encryptor = null;
    /**
     * @var DkimSigner|SMimeSigner|null
     */
    private $signer = null;
    private array $dkimSignerOptions = [];
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
        if (!is_object($this->symfonyMailer)) {
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
        $logger = $enableLogging ? new Logger() : null;
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
     * Returns a new instance with the specified encryptor.
     *
     * @param SMimeEncrypter $encryptor The encryptor instance.
     *
     * @see https://symfony.com/doc/current/mailer.html#encrypting-messages
     *
     * @return self
     */
    public function withEncryptor(SMimeEncrypter $encryptor): self
    {
        $new = clone $this;
        $new->encryptor = $encryptor;
        return $new;
    }

    /**
     * Returns a new instance with the specified signer.
     *
     * @param DkimSigner|object|SMimeSigner $signer The signer instance.
     * @param array $options The options for DKIM signer {@see DkimSigner}.
     *
     * @throws RuntimeException If the signer is not an instance of {@see DkimSigner} or {@see SMimeSigner}.
     *
     * @see https://symfony.com/doc/current/mailer.html#signing-messages
     *
     * @return self
     */
    public function withSigner(object $signer, array $options = []): self
    {
        $new = clone $this;

        if ($signer instanceof DkimSigner) {
            $new->signer = $signer;
            $new->dkimSignerOptions = $options;
            return $new;
        }

        if ($signer instanceof SMimeSigner) {
            $new->signer = $signer;
            return $new;
        }

        throw new RuntimeException(sprintf(
            'The signer must be an instance of "%s" or "%s". The "%s" instance is received.',
            DkimSigner::class,
            SMimeSigner::class,
            get_class($signer),
        ));
    }

    /**
     * {@inheritDoc}
     *
     * @throws TransportExceptionInterface If sending failed.
     */
    protected function sendMessage($message): bool
    {
        if (!($message instanceof Message)) {
            throw new RuntimeException(sprintf(
                'The message must be an instance of "%s". The "%s" instance is received.',
                Message::class,
                get_class($message),
            ));
        }

        $message = $message->getSymfonyEmail();
        if ($this->encryptor !== null) {
            $message = $this->encryptor->encrypt($message);
        }

        if ($this->signer !== null) {
            $message = $this->signer instanceof DkimSigner
                ? $this->signer->sign($message, $this->dkimSignerOptions)
                : $this->signer->sign($message)
            ;
        }
        try {
            $this->getSymfonyMailer()->send($message);
        } catch (\Exception $exception) {
            Yii::getLogger()->log($exception->getMessage(), \yii\log\Logger::LEVEL_ERROR, __METHOD__);
            return false;
        }
        return true;
    }
}
