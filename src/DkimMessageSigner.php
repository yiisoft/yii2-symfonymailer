<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */
declare(strict_types=1);

namespace yii\symfonymailer;

use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Message;

/**
 * @codeCoverageIgnore This class is a trivial proxy that requires no testing
 */
final class DkimMessageSigner implements MessageSignerInterface
{
    private DkimSigner $dkimSigner;

    public function __construct(DkimSigner $dkimSigner)
    {
        $this->dkimSigner = $dkimSigner;
    }

    public function sign(Message $message, array $options = []): Message
    {
        return $this->dkimSigner->sign($message, $options);
    }
}
