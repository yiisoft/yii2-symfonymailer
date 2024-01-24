<?php

declare(strict_types=1);

namespace yii\symfonymailer;

use Psr\EventDispatcher\EventDispatcherInterface;
use yii\base\Component;

final class EventDispatcherProxy implements EventDispatcherInterface
{
    private Component $component;

    public function __construct(Component $component)
    {
        $this->component = $component;
    }

    public function dispatch(object $event): object
    {
        $this->component->trigger(get_class($event), new PsrEvent($event));
        return $event;
    }
}
