<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Infrastructure;

use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Config;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Repository\CachedAvailableEvents;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\ApplicationType;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final readonly class ClientFactory
{
    public static function create(
        string $host,
        string $url,
        int $port,
        string $applicationId
    ): Client {
        return new Client(
            new Config(
                ApplicationType::Symfony,
                $host,
                $url,
                $port,
                $applicationId
            ),
            new CachedAvailableEvents(
                new FilesystemAdapter()
            )
        );
    }
}
