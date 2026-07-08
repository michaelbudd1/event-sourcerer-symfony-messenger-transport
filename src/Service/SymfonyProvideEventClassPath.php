<?php

declare(strict_types=1);

namespace PearTreeWeb\EventSourcerer\SymfonyClient\Service;

use PearTreeWeb\EventSourcerer\Common\Model\EventName;
use PearTreeWeb\EventSourcerer\Common\Service\ProvideEventClassPath;

final readonly class SymfonyProvideEventClassPath implements ProvideEventClassPath
{
    public function __construct(private iterable $eventTemplates)
    {

    }

    public function for(EventName $eventName): string
    {
        foreach ($this->eventTemplates as $event) {
            if ($event::name() === (string) $eventName) {
                return $event::class;
            }
        }

        throw new \RuntimeException(sprintf('Event "%s" not found.', (string) $eventName));
    }
}
