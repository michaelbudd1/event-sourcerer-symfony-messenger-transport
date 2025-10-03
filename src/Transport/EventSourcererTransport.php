<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Transport;

use EventSourcerer\ClientBundle\Command\AckEvent;
use EventSourcerer\ClientBundle\Command\ListenForEvents;
use EventSourcerer\ClientBundle\ProcessEvent;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Process\Process;

final readonly class EventSourcererTransport implements TransportInterface
{
    private function __construct(
        private Client $client,
        private SerializerInterface $serializer,
        private LoggerInterface $workerLogger,
        private string $workerName
    ) {}

    public static function create(
        Client $client,
        SerializerInterface $serializer,
        LoggerInterface $workerLogger,
        string $workerName
    ): self {
        $process = new Process(
            command: ['bin/console', ListenForEvents::COMMAND],
            timeout: null
        );

        $process->setOptions(['create_new_console' => true]);
        $process->start();

        return new self($client, $serializer, $workerLogger, $workerName);
    }

    public function get(): iterable
    {
        if ($message = $this->client->fetchOneMessage()) {
            $this->workerLogger->info(
                sprintf(
                    'Message with all stream checkpoint %d was handled by worker %s',
                    $message['allSequence'],
                    $this->workerName
                )
            );

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
        dd('comes here!');
    }

    public function send(Envelope $envelope): Envelope
    {
        dd('never come here!');
//        return $envelope;
//        throw new TransportException('Transport is designed to only receive events');
    }
}
