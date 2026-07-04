<?php

declare(strict_types=1);

namespace EventSourcerer\SymfonyClient;

use PearTreeWeb\EventSourcerer\Common\Model\EventName;
use PearTreeWeb\EventSourcerer\Common\Model\EventVersion;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final readonly class NewMessage
{
    public function __construct(
        public StreamId $streamId,
        public EventName $eventName,
        public EventVersion $eventVersion,
        public array $payload
    ) {}
}
