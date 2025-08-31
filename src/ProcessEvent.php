<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle;

use PearTreeWebLtd\EventSourcererMessageUtilities\Model\Event;

final readonly class ProcessEvent
{
    public function __construct(public Event $event) {}
}
