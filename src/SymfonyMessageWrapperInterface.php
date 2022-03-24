<?php
declare(strict_types=1);

namespace yii\symfonymailer;

use Symfony\Component\Mime\Email;

interface SymfonyMessageWrapperInterface
{
    public function getSymfonyEmail(): Email;
}
