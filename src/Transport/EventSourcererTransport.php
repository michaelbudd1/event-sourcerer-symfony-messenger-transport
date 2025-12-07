<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Transport;

use EventSourcerer\ClientBundle\Command\ListenForEvents;
use EventSourcerer\ClientBundle\NewMessage;
use EventSourcerer\ClientBundle\ProcessEvent;
use PearTreeWeb\EventSourcerer\Client\Domain\Model\WorkerId;
use PearTreeWeb\EventSourcerer\Client\Domain\Repository\WorkerMessages;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\Event;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Process\Process;

final readonly class EventSourcererTransport implements TransportInterface
{
    private const int WORKER_ID_RANDOM_BYTES_LENGTH = 5;

    /**
     * @param resource $localConnection
     */
    private function __construct(
        private Client $client,
        private SerializerInterface $serializer,
        private WorkerMessages $workerMessages,
        private mixed $localConnection,
        private WorkerId $workerId
    ) {}

    public static function create(
        Client $client,
        SerializerInterface $serializer,
        WorkerMessages $workerMessages
    ): self {
        $workerId = self::workerId();

        self::startListener($workerId);

        sleep(2);

        return new self(
            $client,
            $serializer,
            $workerMessages,
            $client->createLocalConnection(),
            $workerId
        );
    }

    private static function startListener(WorkerId $workerId): void
    {
        $process = new Process(
            command: ['bin/console', ListenForEvents::COMMAND, $workerId->toString()],
            timeout: null,
        );

        $process->setOptions(['create_new_console' => true]);
        $process->start();

        register_shutdown_function(static function () use ($process) {
            $process->stop();
        });
    }

    public function get(): iterable
    {
        foreach ($this->workerMessages->getFor($this->workerId) as $item) {
            yield $this->serializer->decode($item);
        }
    }

    public function ack(Envelope $envelope): void
    {
        /** @var ProcessEvent $message */
        $message = $envelope->getMessage();

        $event = $message->event;

        $this->removeItemFromCache($event);

        $this->client->acknowledgeEvent(
            $event->streamId,
            StreamId::allStream(),
            $event->catchupStreamCheckpoint,
            $event->allSequenceCheckpoint,
            $this->localConnection
        );
    }

    public function send(Envelope $envelope): Envelope
    {
        /** @var NewMessage $message */
        $message = $envelope->getMessage();

        $this
            ->client
            ->writeNewEvent(
                $message->streamId,
                $message->eventName,
                $message->eventVersion,
                $message->payload
            );

        return $envelope;
    }

    private static function workerId(): WorkerId
    {
        return WorkerId::fromString(
            'worker-' . random_bytes(self::WORKER_ID_RANDOM_BYTES_LENGTH)
        );
    }

    private function removeItemFromCache(Event $event): void
    {
        $this->workerMessages->removeFor($this->workerId, $event->allSequenceCheckpoint->toInt());
    }

    public function reject(Envelope $envelope): void
    {
    }
}
