<?php

declare(strict_types=1);

namespace yii\symfonymailer;

use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Crypto\SMimeEncrypter;
use Symfony\Component\Mime\Crypto\SMimeSigner;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;

final class Mailer extends BaseMailer
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

    private TransportInterface $_transport;

    /**
     * Creates Symfony mailer instance.
     * @return SymfonyMailer mailer instance.
     */
    protected function createSymfonyMailer()
    {
        return new SymfonyMailer($this->getTransport());
    }

    /**
     * @return SymfonyMailer Swift mailer instance
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
    public function setTransport(array|TransportInterface $transport)
    {
        if (!is_array($transport) && !is_object($transport)) {
            throw new InvalidConfigException('"' . get_class($this) . '::transport" should be either object or array, "' . gettype($transport) . '" given.');
        }
        if(is_array($transport)) {
            $this->_transport = (new Transport\Smtp\EsmtpTransportFactory())->create(new Dsn(
                $transport['scheme'],
                $transport['host'],
                $transport['username'],
                $transport['password'],
                $transport['port'],
                $transport['options'],
            ));
        } elseif($transport instanceof TransportInterface) {
            $this->_transport = $transport;
        }
        $this->symfonyMailer = null;
    }

    /**
     * @return TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        return $this->_transport;
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
    protected function sendMessage($message): void
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
        $this->getSymfonyMailer()->send($message);
    }
}
