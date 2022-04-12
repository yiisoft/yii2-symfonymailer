<?php

declare(strict_types=1);

namespace yii\symfonymailer;

use Symfony\Component\Mime\Email;

interface MessageWrapperInterface
{
    public function getSymfonyEmail(): Email;
}
