<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */
declare(strict_types=1);

namespace yii\symfonymailer;

use Symfony\Component\Mime\Crypto\SMimeSigner;
use Symfony\Component\Mime\Message;

/**
 * @codeCoverageIgnore This class is a trivial proxy that requires no testing
 */
final class SMimeMessageSigner implements MessageSignerInterface
{
    public function __construct(private readonly SMimeSigner $signer) {}

    public function sign(Message $message, array $options = []): Message
    {
        return $this->signer->sign($message);
    }
}
