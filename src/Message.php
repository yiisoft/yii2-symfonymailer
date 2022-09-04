<?php
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
use yii\mail\BaseMessage;


class Message extends BaseMessage
{
    private Email $email;
    private string $charset = 'utf-8';
    public function __construct($config = [])
    {
        $this->email = new Email();
        parent::__construct($config);        
    }

    public function __clone()
    {
        $this->email = clone $this->email;
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

    public function getFrom()
    {
        return $this->convertAddressesToStrings($this->email->getFrom());
    }

    public function setFrom($from): self
    {
        $this->email->from(...$this->convertStringsToAddresses($from));
        return $this;
    }

    public function getTo()
    {
        return $this->convertAddressesToStrings($this->email->getTo());
    }

    public function setTo($to): self
    {
        $this->email->to(...$this->convertStringsToAddresses($to));
        return $this;
    }

    public function getReplyTo()
    {
        return $this->convertAddressesToStrings($this->email->getReplyTo());
    }

    public function setReplyTo($replyTo): self
    {
        $this->email->replyTo(...$this->convertStringsToAddresses($replyTo));
        return $this;
    }

    public function getCc()
    {
        return $this->convertAddressesToStrings($this->email->getCc());
    }

    public function setCc($cc): self
    {
        $this->email->cc(...$this->convertStringsToAddresses($cc));
        return $this;
    }

    public function getBcc()
    {
        return $this->convertAddressesToStrings($this->email->getBcc());
    }

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

    public function getTextBody(): string
    {
        return (string) $this->email->getTextBody();
    }

    public function setTextBody($text): self
    {
        $this->email->text($text, $this->charset);
        return $this;
    }

    public function getHtmlBody(): string
    {
        return (string) $this->email->getHtmlBody();
    }

    public function setHtmlBody($html): self
    {
        $this->email->html($html, $this->charset);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attach($fileName, array $options = [])
    {
        $file = [];
        if (!empty($options['fileName'])) {
            $file['name'] = $options['fileName'];
        } else {
            $file['name'] = $fileName;
        }

        if (!empty($options['contentType'])) {
            $file['contentType'] = $options['contentType'];
        } else {
            $file['contentType'] = mime_content_type($fileName);
        }

        $this->email->attachFromPath($fileName, $file['name'], $file['contentType']);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attachContent($content, array $options = [])
    {
        $file = [];
        if (!empty($options['fileName'])) {
            $file['name'] = $options['fileName'];
        } else {
            $file['name'] = null;
        }

        if (!empty($options['contentType'])) {
            $file['contentType'] = $options['contentType'];
        } else {
            $file['contentType'] = null;
        }

        $this->email->attach($content, $file['name'], $file['contentType']);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function embed($fileName, array $options = [])
    {
        $file = [];
        if (!empty($options['fileName'])) {
            $file['name'] = $options['fileName'];
        } else {
            $file['name'] = $fileName;
        }

        if (!empty($options['contentType'])) {
            $file['contentType'] = $options['contentType'];
        } else {
            $file['contentType'] = mime_content_type($fileName);
        }

        $this->email->embedFromPath($fileName, $file['name'], $file['contentType']);
        return 'cid:' . $file['name'];
    }

    /**
     * @inheritdoc
     */
    public function embedContent($content, array $options = [])
    {
        $file = [];
        if (!empty($options['fileName'])) {
            $file['name'] = $options['fileName'];
        } else {
            $file['name'] = null;
        }

        if (!empty($options['contentType'])) {
            $file['contentType'] = $options['contentType'];
        } else {
            $file['contentType'] = null;
        }

        $this->email->embed($content, $file['name'], $file['contentType']);
        return 'cid:' . $file['name'];
    }

    public function getHeader($name): array
    {
        $headers = $this->email->getHeaders();
        if (!$headers->has($name)) {
            return [];
        }

        $values = [];

        /** @var HeaderInterface $header */
        foreach ($headers->all($name) as $header) {
            $values[] = $header->getBodyAsString();
        }

        return $values;
    }

    public function addHeader($name, $value): self
    {
        $this->email->getHeaders()->addTextHeader($name, $value);
        return $this;
    }

    public function setHeader($name, $value): self
    {
        $headers = $this->email->getHeaders();

        if ($headers->has($name)) {
            $headers->remove($name);
        }

        foreach ((array) $value as $v) {
            $headers->addTextHeader($name, $v);
        }

        return $this;
    }

    public function setHeaders($headers): self
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
     * @param Address[] $addresses
     *
     * @return array<string, string>|string
     */
    private function convertAddressesToStrings(array $addresses)
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
     * @return Address[]
     */
    private function convertStringsToAddresses($strings): array
    {
        if (is_string($strings)) {
            return [new Address($strings)];
        }

        $addresses = [];

        foreach ($strings as $address => $name) {
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
