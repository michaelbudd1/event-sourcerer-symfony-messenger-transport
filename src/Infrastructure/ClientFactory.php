<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Infrastructure;

use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Config;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Repository\CachedAvailableEvents;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Repository\SymfonyLockStreamLocker;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final readonly class ClientFactory
{
    public static function create(
        Config $config,
        string $cacheDir,
        string $namespace
    ): Client {
        return new Client(
            $config,
            new CachedAvailableEvents(
                new FilesystemAdapter(
                    namespace: $namespace,
                    directory: strtolower($cacheDir)
                ),
                SymfonyLockStreamLocker::create()
            ),
        );
    }
}
