<?php

declare(strict_types=1);

namespace yii\symfonymailer;

use Symfony\Component\Mime\Message;

interface SymfonyMessageSignerInterface
{
    public function sign(Message $message, array $options = []): Message;
}
