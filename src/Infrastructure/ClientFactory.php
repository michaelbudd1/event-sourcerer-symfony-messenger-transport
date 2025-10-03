<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Infrastructure;

use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Config;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Repository\LockedAvailableEvents;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Service\SymfonyLockStreamLocker;
use Symfony\Component\Cache\Adapter\DoctrineDbalAdapter;
use Doctrine\DBAL\Connection;

final readonly class ClientFactory
{
    public static function create(Config $config, Connection $dbalConnection, string $namespace): Client
    {
        return new Client(
            $config,
            new LockedAvailableEvents(
                new DoctrineDbalAdapter(
                    $dbalConnection,
                    $namespace
                ),
                SymfonyLockStreamLocker::create()
            ),
        );
    }
}
