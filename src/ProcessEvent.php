<?php

declare(strict_types=1);

namespace PearTreeWeb\EventSourcerer\SymfonyClient;

use PearTreeWeb\EventSourcerer\Common\Model\Event;

final readonly class ProcessEvent
{
    public function __construct(public Event $event) {}
}
