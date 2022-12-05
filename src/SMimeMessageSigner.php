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
class SMimeMessageSigner implements MessageSignerInterface
{
    private SMimeSigner $signer;
    public function __construct(SMimeSigner $signer)
    {
        $this->signer = $signer;
    }

    public function sign(Message $message, array $options = []): Message
    {
        return $this->signer->sign($message);
    }
}
