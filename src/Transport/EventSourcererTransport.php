<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Transport;

use EventSourcerer\ClientBundle\Command\CreateExternalSocketConnection;
use EventSourcerer\ClientBundle\NewMessage;
use EventSourcerer\ClientBundle\ProcessEvent;
use PearTreeWeb\EventSourcerer\Client\Domain\Model\WorkerId;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\ApplicationId;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\Checkpoint;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;
use PearTreeWebLtd\EventSourcererMessageUtilities\Service\CreateMessage;
use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;
use React\Socket\FixedUriConnector;
use React\Socket\UnixConnector;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Process\Process;

final readonly class EventSourcererTransport implements TransportInterface
{
    private const string LISTENER_COMMAND_LOCK = 'listenerCommandLock';

    private function __construct(
        private Client $client,
        private SerializerInterface $serializer,
        private LoggerInterface $workerLogger,
        private WorkerId $workerId
    ) {}

    public static function create(
        Client $client,
        SerializerInterface $serializer,
        LoggerInterface $workerLogger,
        WorkerId $workerId,
        LockFactory $lockFactory
    ): self {
        self::startListener($client, $lockFactory, $workerId);

        $client->attachWorker($workerId);

        return new self($client, $serializer, $workerLogger, $workerId);
    }

    private static function startListener(Client $client, LockFactory $lockFactory, WorkerId $workerId): void
    {
//        $lock = $lockFactory->createLock(self::LISTENER_COMMAND_LOCK);
//
//        if ($lock->acquire()) {
//            $process = new Process(
//                command: ['bin/console', CreateExternalSocketConnection::COMMAND],
//                timeout: null,
//            );
//
//            $process->setOptions(['create_new_console' => true]);
//            $process->start();
//
//            register_shutdown_function(static function () use ($process, $lock, $client, $workerId) {
//                $client->flagCatchupComplete();
//                $client->detachWorker($workerId);
//                $process->stop();
//                $lock->release();
//            });
//        }
    }

    public function get(): iterable
    {
        echo 'And PID here is ' . getmypid() . PHP_EOL;

        dump('client connected ...', null !== $this->client->connected());

        if ($message = $this->client->fetchOneMessage($this->workerId)) {
            $this->workerLogger->info(
                sprintf(
                    'Message with all stream checkpoint %d was handled by worker %s',
                    $message['allSequence'],
                    $this->workerId
                )
            );
            dump('found message!');
            yield $this->serializer->decode($message);
        } else {
            dump('no message found :-(');
        }
    }

    public function ack(Envelope $envelope): void
    {
        /** @var ProcessEvent $message */
        $message = $envelope->getMessage();

        $event = $message->event;

        $this
            ->client
            ->acknowledgeEvent(
                $event->streamId,
                $event->catchupStreamCheckpoint,
                $event->allSequenceCheckpoint
            );
    }

    public function reject(Envelope $envelope): void
    {
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
}
