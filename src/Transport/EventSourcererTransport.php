<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Transport;

use EventSourcerer\ClientBundle\Command\AckEvent;
use EventSourcerer\ClientBundle\Command\ListenForEvents;
use EventSourcerer\ClientBundle\ProcessEvent;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
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
        $process = new Process(
            command: ['bin/console', ListenForEvents::COMMAND],
            timeout: null
        );

        $process->setOptions(['create_new_console' => true]);

        $process->start();

        return new self($client, $serializer);
    }

    public function get(): iterable
    {
        if ($message = $this->client->fetchOneMessage()) {
            yield $this->serializer->decode($message);
        }
    }

    public function ack(Envelope $envelope): void
    {
        /** @var ProcessEvent $message */
        $message = $envelope->getMessage();

        $event = $message->event;

        $process = new Process(
            command: [
                'bin/console',
                AckEvent::COMMAND,
                $event->streamId,
                $event->allSequenceCheckpoint->toString(),
                $event->catchupStreamCheckpoint->toString(),
            ],
        );

        $process->run();
    }

    public function reject(Envelope $envelope): void
    {
    }

    public function send(Envelope $envelope): Envelope
    {
        throw new TransportException('Transport is designed to only receive events');
    }
}
