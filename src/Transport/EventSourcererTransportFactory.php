<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Transport;

use PearTreeWeb\EventSourcerer\Client\Domain\Repository\WorkerMessages;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

final readonly class EventSourcererTransportFactory implements TransportFactoryInterface
{
    private const string DSN_PREFIX = 'es://';

    public function __construct(
        private Client $client,
        private WorkerMessages $workerMessages
    ) {}

    public function createTransport(
        #[\SensitiveParameter] string $dsn,
        array $options,
        SerializerInterface $serializer
    ): TransportInterface {
        return EventSourcererTransport::create(
            $this->client,
            new Serializer(),
            $this->workerMessages
        );
    }

    public function supports(#[\SensitiveParameter] string $dsn, array $options): bool
    {
        return str_contains($dsn, self::DSN_PREFIX);
    }
}
