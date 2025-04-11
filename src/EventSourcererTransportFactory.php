<?php

declare(strict_types=1);

namespace PearTreeWebLtd\EventSourcererSymfonyMessengerTransport;

use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class EventSourcererTransportFactory implements TransportFactoryInterface
{
    private const string DSN_PREFIX = 'es://';

    public function __construct(
        private string $eventSourcererApplicationId,
        private HttpClientInterface $httpClient,

    ) {
    }

    public function createTransport(
        #[\SensitiveParameter] string $dsn,
        array $options,
        SerializerInterface $serializer
    ): TransportInterface {
        return new EventSourcererTransport(
            str_replace(self::DSN_PREFIX, '', $dsn ),
            $this->eventSourcererApplicationId,
            $this->httpClient
        );
    }

    public function supports(#[\SensitiveParameter] string $dsn, array $options): bool
    {
        return str_contains($dsn, self::DSN_PREFIX);
    }
}
