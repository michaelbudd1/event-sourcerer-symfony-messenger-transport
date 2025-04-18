<?php

declare(strict_types=1);

namespace PearTreeWebLtd\EventSourcererSymfonyMessengerTransport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class EventSourcererTransport implements TransportInterface
{
    private function __construct(
        private string $eventSourcererUrl,
        private string $eventSourcererApplicationId,
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer
    ) {}

    public static function create(
        string $eventSourcererUrl,
        string $eventSourcererApplicationId,
        HttpClientInterface $httpClient,
        SerializerInterface $serializer
    ): self {
        return new self(
            $eventSourcererUrl,
            $eventSourcererApplicationId,
            $httpClient,
            $serializer
        );
    }

    public function get(): iterable
    {
        $results = $this->httpClient->request(
            'GET',
            sprintf(
                'https://%s/api/stream_events/queue/receive?itemsPerPage=1&applicationId=%s&streamId=*',
                $this->eventSourcererUrl,
                $this->eventSourcererApplicationId
            )
        );

        $event = json_decode($results->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (is_array($event)) {
            yield new Envelope(
                $this->serializer->decode($event)
            );
        }
    }

    public function ack(Envelope $envelope): void
    {
        $this->httpClient->request(
            'POST',
            sprintf(
                '%s/stream_events/%s/ack',
                $this->eventSourcererUrl,
                'test'
            )
        );
    }

    public function reject(Envelope $envelope): void
    {
        // TODO: Implement reject() method.
    }

    public function send(Envelope $envelope): Envelope
    {
        // TODO: Implement send() method.
    }
}
