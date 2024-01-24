<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */
declare(strict_types=1);

namespace yii\symfonymailer;

use Symfony\Component\Mime\Crypto\SMimeEncrypter;
use Symfony\Component\Mime\Message;

/**
 * @codeCoverageIgnore This class is a trivial proxy that requires no testing
 */
final class SMimeMessageEncrypter implements MessageEncrypterInterface
{
    public function __construct(private readonly SMimeEncrypter $encrypter) {}

    public function encrypt(Message $message): Message
    {
        return $this->encrypter->encrypt($message);
    }
}
