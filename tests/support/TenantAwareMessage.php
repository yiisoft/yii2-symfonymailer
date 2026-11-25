<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\extensions\symfonymailer\support;

use yii\symfonymailer\Message;

/**
 * Represents a {@see Message} subclass with its own state that extends both serialization hooks through `parent::`.
 */
final class TenantAwareMessage extends Message
{
    public string $tenant = '';

    public function __serialize(): array
    {
        return parent::__serialize() + ['tenant' => $this->tenant];
    }

    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);

        $this->tenant = $data['tenant'];
    }
}
