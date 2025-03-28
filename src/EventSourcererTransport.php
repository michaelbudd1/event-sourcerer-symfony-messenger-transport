<?php

declare(strict_types=1);

namespace PearTreeWebLtd\EventSourcererSymfonyMessengerTransport;

use App\Infrastructure\Messenger\Command\ProcessNewEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function App\Infrastructure\Messenger\dd;

final readonly class EventSourcererTransport implements TransportInterface
{
    private int $checkpoint;

    public function __construct(
        private string $eventSourcererUrl,
        private string $eventSourcererApplicationId,
        private HttpClientInterface $httpClient
    ) {
        $checkpoint = $this->httpClient->request(
            Request::METHOD_GET,
            sprintf(
                '%s/api/application_checkpoints?applicationId=%s&streamId=%s',
                $this->eventSourcererUrl,
                $this->eventSourcererApplicationId,
                '*'
            )
        );

        $this->checkpoint = $checkpoint->toArray()[0]['checkpoint'] ?? 0;
    }

    public function get(): iterable
    {
        $results = $this->httpClient->request(
            Request::METHOD_GET,
            sprintf(
                '%s/api/stream_events?page=%d&itemsPerPage=1',
                $this->eventSourcererUrl,
                $this->checkpoint +1
            )
        );

        foreach ($results->toArray() as $event) {
            if (is_array($event)) {
                dd($event);
                yield new Envelope(
                    new ProcessNewEvent($event)
                );
            }
        }
    }

    public function ack(Envelope $envelope): void
    {
        dd($envelope->getMessage());

        $this->httpClient->request(
            Request::METHOD_POST,
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
