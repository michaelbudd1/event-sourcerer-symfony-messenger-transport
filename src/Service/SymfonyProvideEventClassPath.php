<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Service;

use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventName;
use PearTreeWebLtd\EventSourcererMessageUtilities\Service\ProvideEventClassPath;

final class SymfonyProvideEventClassPath implements ProvideEventClassPath
{
    public function for(EventName $eventName): string
    {
        // TODO: Implement for() method.
    }
}
