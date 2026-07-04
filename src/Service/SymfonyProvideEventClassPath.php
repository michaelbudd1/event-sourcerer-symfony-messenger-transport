<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Service;

use PearTreeWeb\EventSourcerer\Common\Model\EventName;
use PearTreeWeb\EventSourcerer\Common\Service\ProvideEventClassPath;

final class SymfonyProvideEventClassPath implements ProvideEventClassPath
{
    public function for(EventName $eventName): string
    {
        // TODO: Implement for() method.
    }
}
