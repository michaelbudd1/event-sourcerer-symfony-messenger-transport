<?php

declare(strict_types=1);

namespace EventSourcerer\SymfonyClient\Transport;

use EventSourcerer\SymfonyClient\Command\ListenForEvents;
use EventSourcerer\SymfonyClient\NewMessage;
use EventSourcerer\SymfonyClient\ProcessEvent;
use PearTreeWeb\EventSourcerer\Client\Domain\Repository\WorkerMessages;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWeb\EventSourcerer\Common\Model\Event;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Process\Process;

final class EventSourcererTransport implements TransportInterface
{
    private const int WORKER_ID_RANDOM_BYTES_LENGTH = 5;

    /**
     * @param resource $localConnection
     */
    private function __construct(
        private readonly Client $client,
        private readonly SerializerInterface $serializer,
        private readonly WorkerMessages $workerMessages,
        private readonly mixed $localConnection,
        private readonly WorkerId $workerId,
        private readonly Process $listenerProcess,
        private array $processed,
    ) {}

    public static function create(
        Client $client,
        SerializerInterface $serializer,
        WorkerMessages $workerMessages
    ): self {
        $workerId = self::workerId();

        $process = self::startListener($workerId);

        sleep(2);

        return new self(
            $client,
            $serializer,
            $workerMessages,
            $client->createLocalConnection(),
            $workerId,
            $process,
            []
        );
    }

    private static function startListener(WorkerId $workerId): Process
    {
        $process = new Process(
            command: ['bin/console', ListenForEvents::COMMAND, $workerId->toString(), (string) getmypid()],
            timeout: null,
        );

        $process->start();

        register_shutdown_function(static fn () => $process->stop());

        return $process;
    }

    public function get(): iterable
    {
        if (!$this->listenerProcess->isRunning()) {
            exit(1);
        }

        foreach ($this->workerMessages->getFor($this->workerId) as $item) {
            if ($this->processed[$item['allSequence']] ?? false) {
                continue;
            }

            $this->processed[$item['allSequence']] = true;

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
            $this->workerId,
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
            'worker-' . bin2hex(random_bytes(self::WORKER_ID_RANDOM_BYTES_LENGTH))
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
