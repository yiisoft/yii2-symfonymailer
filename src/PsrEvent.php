<?php

declare(strict_types=1);

namespace yii\symfonymailer;

use yii\base\Event;

/**
 * This class wraps a PSR-14 event object. Note that PSR-14 does not place any demands on an event object.
 * In Yii all events are stoppable, in PSR-14 this is not the case. For this implementation we force all events to be
 * stoppable
 */
final class PsrEvent extends Event
{
    private object $originalEvent;

    public function __construct(object $originalEvent)
    {
        parent::__construct([]);
        $this->name = get_class($originalEvent);
        $this->originalEvent = $originalEvent;
    }

    public function getOriginalEvent(): object
    {
        return $this->originalEvent;
    }
}
