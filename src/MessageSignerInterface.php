<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */
declare(strict_types=1);

namespace yii\symfonymailer;

use Symfony\Component\Mime\Message;

interface MessageSignerInterface
{
    /**
     * @param array<mixed> $options
     */
    public function sign(Message $message, array $options = []): Message;
}
