<?php

declare(strict_types=1);

namespace PearTreeWebLtd\EventSourcererSymfonyMessengerTransport;

use App\Infrastructure\Messenger\EventSourcererTransport;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class EventSourcererTransportFactory implements TransportFactoryInterface
{
    private const string DSN_PREFIX = 'es://';

    public function __construct(
        private string $eventSourcererUrl,
        private string $eventSourcererApplicationId,
        private HttpClientInterface $httpClient
    ) {
    }

    public function createTransport(
        #[\SensitiveParameter] string $dsn,
        array $options,
        SerializerInterface $serializer
    ): TransportInterface {
        return new EventSourcererTransport(
            $this->eventSourcererUrl,
            $this->eventSourcererApplicationId,
            $this->httpClient
        );
    }

    public function supports(#[\SensitiveParameter] string $dsn, array $options): bool
    {
        return str_contains($dsn, self::DSN_PREFIX);
    }
}
