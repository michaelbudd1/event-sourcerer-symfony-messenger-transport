<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle;

final readonly class ProcessEvent
{
    /**
     * @param array{id: string, eventName: string, properties: array} $event
     */
    public function __construct(public array $event) {}
}
