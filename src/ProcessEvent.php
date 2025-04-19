<?php

declare(strict_types=1);

namespace PearTreeWebLtd\EventSourcererSymfonyMessengerTransport;

final readonly class ProcessEvent
{
    /**
     * @param array{id: string, eventName: string, properties: array} $event
     */
    public function __construct(array $event) {}
}
