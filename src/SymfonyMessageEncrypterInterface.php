<?php

declare(strict_types=1);

namespace yii\symfonymailer;

use Symfony\Component\Mime\Message as Message;

interface MessageEncrypterInterface
{
    public function encrypt(Message $message): Message;
}
