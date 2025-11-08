<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Infrastructure;

use PearTreeWeb\EventSourcerer\Client\Domain\Model\MessageBucket;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Config;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Repository\BucketedAvailableEvents;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Repository\SharedProcessCommunicationCache;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Service\SharedCacheStreamBuckets;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Service\SharedCacheStreamWorkerManager;
use Symfony\Component\Cache\Adapter\DoctrineDbalAdapter;
use Doctrine\DBAL\Connection;

final readonly class ClientFactory
{
    public static function create(Config $config, Connection $dbalConnection): Client
    {
        return new Client(
            $config,
            new BucketedAvailableEvents(
                new SharedCacheStreamBuckets(
                    new DoctrineDbalAdapter($dbalConnection, 'streamBuckets'),
                    new MessageBucket(new DoctrineDbalAdapter($dbalConnection, 'messageBucket1')),
                    new MessageBucket(new DoctrineDbalAdapter($dbalConnection, 'messageBucket2')),
                    new MessageBucket(new DoctrineDbalAdapter($dbalConnection, 'messageBucket3')),
                ),
                new SharedCacheStreamWorkerManager(
                    new DoctrineDbalAdapter($dbalConnection, 'streamWorkerManager'),
                    new DoctrineDbalAdapter($dbalConnection, 'workers')
                )
            ),
            new SharedProcessCommunicationCache(
                new DoctrineDbalAdapter($dbalConnection, 'sharedProcessCommunication')
            )
        );
    }
}
