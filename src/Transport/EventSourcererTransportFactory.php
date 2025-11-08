<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Transport;

use Doctrine\DBAL\Connection;
use PearTreeWeb\EventSourcerer\Client\Domain\Model\WorkerId;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\DoctrineDbalStore;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

final readonly class EventSourcererTransportFactory implements TransportFactoryInterface
{
    private const string DSN_PREFIX = 'es://';

    public function __construct(
        private Client $client,
        private LoggerInterface $workerLogger,
        private Connection $dbalConnection
    ) {}

    public function createTransport(
        #[\SensitiveParameter] string $dsn,
        array $options,
        SerializerInterface $serializer
    ): TransportInterface {
        return EventSourcererTransport::create(
            $this->client,
            new Serializer(),
            $this->workerLogger,
            self::workerName(),
            new LockFactory(new DoctrineDbalStore($this->dbalConnection))
        );
    }

    public function supports(#[\SensitiveParameter] string $dsn, array $options): bool
    {
        return str_contains($dsn, self::DSN_PREFIX);
    }

    private static function workerName(): WorkerId
    {
        return WorkerId::fromString(uniqid('worker-', true));
    }
}
