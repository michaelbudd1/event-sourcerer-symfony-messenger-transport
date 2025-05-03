<?php

declare(strict_types=1);

namespace PearTreeWebLtd\EventSourcererSymfonyMessengerTransport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class EventSourcererTransport implements TransportInterface
{
    private const int NOT_FOUND_RESPONSE = 404;

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

        try {
            $event = json_decode($results->getContent(), true, 512, JSON_THROW_ON_ERROR);

            if (is_array($event)) {
                yield $this->serializer->decode($event);
            }
        } catch (ClientExceptionInterface $e) {
            if ($e->getCode() !== self::NOT_FOUND_RESPONSE) {
                throw $e;
            }
        }
    }

    public function ack(Envelope $envelope): void
    {
        /** @var ProcessEvent $message */
        $message = $envelope->getMessage();

        $this->httpClient->request(
            'POST',
            sprintf(
                'https://%s/stream_events/%s/ack',
                $this->eventSourcererUrl,
                '*'
            ),
            [
                'body' => [
                    'applicationId' => $this->eventSourcererApplicationId,
                    'checkpoint'    => $message->event['allSequence'],
                ],
            ]
        );
    }

    public function reject(Envelope $envelope): void
    {
    }

    public function send(Envelope $envelope): Envelope
    {
        throw new TransportException('Transport is designed to only receive events');
    }
}
