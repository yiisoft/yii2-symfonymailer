<?php

declare(strict_types=1);
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\symfonymailer;

use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\HeaderInterface;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\mail\BaseMessage;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-type PsalmFileOptions array{fileName?: string, contentType?: string}
 * @psalm-type PsalmAddressList array<int|string, string>|string
 *
 * @property PsalmAddressList $bcc The type defined by the message interface is not strict enough.
 * @property Email $symfonyEmail Symfony email instance.
 *
 * @extendable
 */
class Message extends BaseMessage implements MessageWrapperInterface
{
    private Email $email;
    private string $charset = 'utf-8';

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->email = new Email();
        parent::__construct($config);
    }

    public function __clone()
    {
        $this->email = clone $this->email;
    }

    public function __sleep(): array
    {
        return ['email', 'charset'];
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function setCharset($charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion Symfony typehint is too loose
     * @return array<string, string>|string
     */
    public function getFrom(): array|string
    {
        return $this->convertAddressesToStrings($this->email->getFrom());
    }

    /**
     * @param array<int|string, string>|string $from
     * @psalm-suppress MoreSpecificImplementedParamType Yii typehint is too loose
     * @psalm-suppress ArgumentTypeCoercion Symfony typehint is too loose
     * @return $this
     */
    public function setFrom($from): static
    {
        $this->email->from(...$this->convertStringsToAddresses($from));
        return $this;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion Symfony typehint is too loose
     * @return array<string, string>|string
     */
    public function getTo(): array|string
    {
        return $this->convertAddressesToStrings($this->email->getTo());
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     * @param PsalmAddressList $to
     * @return $this
     */
    public function setTo($to): self
    {
        $this->email->to(...$this->convertStringsToAddresses($to));
        return $this;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion Symfony typehint is too loose
     * @return array<string, string>|string
     */
    public function getReplyTo()
    {
        return $this->convertAddressesToStrings($this->email->getReplyTo());
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     * @param PsalmAddressList $replyTo
     * @return $this
     */
    public function setReplyTo($replyTo): self
    {
        $this->email->replyTo(...$this->convertStringsToAddresses($replyTo));
        return $this;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion Symfony typehint is too loose
     * @return array<string,string>|string
     */
    public function getCc(): array|string
    {
        return $this->convertAddressesToStrings($this->email->getCc());
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     * @param PsalmAddressList $cc
     * @return $this
     */
    public function setCc($cc): self
    {
        $this->email->cc(...$this->convertStringsToAddresses($cc));
        return $this;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion Symfony typehint is too loose
     * @return array<string, string>|string
     */
    public function getBcc(): array|string
    {
        return $this->convertAddressesToStrings($this->email->getBcc());
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     * @param PsalmAddressList $bcc The type defined by the message interface is not strict enough
     * @return $this
     */
    public function setBcc($bcc): self
    {
        $this->email->bcc(...$this->convertStringsToAddresses($bcc));
        return $this;
    }

    public function getSubject(): string
    {
        return (string) $this->email->getSubject();
    }

    public function setSubject($subject): self
    {
        $this->email->subject($subject);
        return $this;
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->email->getDate();
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->email->date($date);
        return $this;
    }

    public function getPriority(): int
    {
        return $this->email->getPriority();
    }

    public function setPriority(int $priority): self
    {
        $this->email->priority($priority);
        return $this;
    }

    public function getReturnPath(): string
    {
        $returnPath = $this->email->getReturnPath();
        return $returnPath === null ? '' : $returnPath->getAddress();
    }

    public function setReturnPath(string $address): self
    {
        $this->email->returnPath($address);
        return $this;
    }

    public function getSender(): string
    {
        $sender = $this->email->getSender();
        return $sender === null ? '' : $sender->getAddress();
    }

    public function setSender(string $address): self
    {
        $this->email->sender($address);
        return $this;
    }

    public function setTextBody($text): self
    {
        $this->email->text($text, $this->charset);
        return $this;
    }

    public function setHtmlBody($html): self
    {
        $this->email->html($html, $this->charset);
        return $this;
    }

    /**
     * @param string $fileName
     * @param PsalmFileOptions $options
     * @psalm-suppress MoreSpecificImplementedParamType The real expected type is defined in human readable text only
     * @return $this
     */
    public function attach($fileName, array $options = []): self
    {
        $this->email->attachFromPath(
            $fileName,
            $options['fileName'] ?? $fileName,
            $options['contentType'] ?? FileHelper::getMimeType($fileName)
        );
        return $this;
    }

    /**
     * @param resource|string $content
     * @param PsalmFileOptions $options
     * @psalm-suppress MoreSpecificImplementedParamType The real expected type is defined in human readable text only
     * @return $this
     */
    public function attachContent($content, array $options = []): self
    {
        $this->email->attach($content, $options['fileName'] ?? null, $options['contentType'] ?? null);
        return $this;
    }

    /**
     * @param string $fileName
     * @param PsalmFileOptions $options
     * @psalm-suppress MoreSpecificImplementedParamType The real expected type is defined in human readable text only
     */
    public function embed($fileName, array $options = []): string
    {
        $name = $options['fileName'] ?? $fileName;
        $this->email->embedFromPath(
            $fileName,
            $name,
            $options['contentType'] ?? FileHelper::getMimeType($fileName)
        );
        return 'cid:' . $name;
    }

    /**
     * @param resource|string $content
     * @param PsalmFileOptions $options
     * @psalm-suppress MoreSpecificImplementedParamType The real expected type is defined in human readable text only
     */
    public function embedContent($content, array $options = []): string
    {
        if (!isset($options['fileName'])) {
            throw new InvalidConfigException('A valid file name must be passed when embedding content');
        }
        $this->email->embed($content, $options['fileName'], $options['contentType'] ?? null);
        return 'cid:' . $options['fileName'];
    }

    /**
     * @return list<string>
     */
    public function getHeader(string $name): array
    {
        $headers = $this->email->getHeaders();

        $values = [];

        /** @var HeaderInterface $header */
        foreach ($headers->all($name) as $header) {
            $values[] = $header->getBodyAsString();
        }

        return $values;
    }

    public function addHeader(string $name, string $value): self
    {
        $this->email->getHeaders()->addTextHeader($name, $value);
        return $this;
    }

    /**
     * @param list<string>|string $value
     */
    public function setHeader(string $name, array|string $value): self
    {
        $headers = $this->email->getHeaders();

        $headers->remove($name);

        foreach ((array) $value as $v) {
            $headers->addTextHeader($name, $v);
        }

        return $this;
    }

    /**
     * @param array<string, list<string>|string> $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
    }

    public function toString(): string
    {
        return $this->email->toString();
    }

    /**
     * Returns a Symfony email instance.
     *
     * @return Email Symfony email instance.
     */
    public function getSymfonyEmail(): Email
    {
        return $this->email;
    }

    /**
     * Converts address instances to their string representations.
     *
     * @param list<Address> $addresses
     *
     * @return array<string, string>|string
     */
    private function convertAddressesToStrings(array $addresses): string|array
    {
        $strings = [];

        foreach ($addresses as $address) {
            $strings[$address->getAddress()] = $address->getName();
        }

        return empty($strings) ? '' : $strings;
    }

    /**
     * Converts string representations of address to their instances.
     *
     * @param array<int|string, string>|string $strings
     *
     * @return list<Address>
     */
    private function convertStringsToAddresses(array|string $strings): array
    {
        $addresses = [];

        foreach ((array) $strings as $address => $name) {
            if (!is_string($address)) {
                // email address without name
                $addresses[] = new Address($name);
                continue;
            }

            $addresses[] = new Address($address, $name);
        }

        return $addresses;
    }
}
