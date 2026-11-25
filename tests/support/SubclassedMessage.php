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
 * Represents a {@see Message} subclass that adds no state and does not override the serialization hooks.
 */
final class SubclassedMessage extends Message
{
}
