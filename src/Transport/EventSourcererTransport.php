<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Transport;

use EventSourcerer\ClientBundle\Command\ListenForEvents;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final readonly class EventSourcererTransport implements TransportInterface
{
    private function __construct(
        private Client $client,
        private SerializerInterface $serializer
    ) {}

    public static function create(
        Client $client,
        SerializerInterface $serializer
    ): self {
        $process = new Process([
            'bin/console',
            ListenForEvents::COMMAND,
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return new self($client, $serializer);
    }

    public function get(): iterable
    {
        yield $this->serializer->decode($this->client->fetchOneMessage());
    }

    public function ack(Envelope $envelope): void
    {
//        /** @var ProcessEvent $message */
//        $message = $envelope->getMessage();
//
//        $this->httpClient->request(
//            'POST',
//            sprintf(
//                'https://%s/stream_events/%s/ack',
//                $this->eventSourcererUrl,
//                '*'
//            ),
//            [
//                'body' => [
//                    'applicationId' => $this->eventSourcererApplicationId,
//                    'checkpoint'    => $message->event['allSequence'],
//                ],
//            ]
//        );
    }

    public function reject(Envelope $envelope): void
    {
    }

    public function send(Envelope $envelope): Envelope
    {
        throw new TransportException('Transport is designed to only receive events');
    }
}
