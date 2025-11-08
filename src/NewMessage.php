<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle;

use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventName;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventVersion;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;

final readonly class NewMessage
{
    public function __construct(
        public StreamId $streamId,
        public EventName $eventName,
        public EventVersion $eventVersion,
        public array $payload
    ) {}
}
