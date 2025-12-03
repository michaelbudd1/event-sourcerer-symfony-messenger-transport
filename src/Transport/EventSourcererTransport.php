<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Transport;

use EventSourcerer\ClientBundle\Command\ListenForEvents;
use EventSourcerer\ClientBundle\NewMessage;
use EventSourcerer\ClientBundle\ProcessEvent;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\Event;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;
use PearTreeWebLtd\EventSourcererMessageUtilities\Service\CreateMessage;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Process\Process;

final readonly class EventSourcererTransport implements TransportInterface
{
    private function __construct(
        private Client $client,
        private SerializerInterface $serializer,
        private CacheItemPoolInterface $appCachePool
    ) {}

    public static function create(
        Client $client,
        SerializerInterface $serializer,
        CacheItemPoolInterface $appCachePool
    ): self {
        self::startListener();

        return new self($client, $serializer, $appCachePool);
    }

    private static function startListener(): void
    {
        $process = new Process(
            command: ['bin/console', ListenForEvents::COMMAND],
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
        $availableItems = $this->appCachePool->getItem(ListenForEvents::EVENTS)->get() ?? [];

        $workerId = self::workerId();

        foreach ($availableItems as $item) {
            $logText = sprintf(
                'Message with all stream checkpoint %d was handled by worker %s',
                $item['allSequence'],
                $workerId
            );

            echo $logText . PHP_EOL;

            yield $this->serializer->decode($item);
        }
    }

    public function ack(Envelope $envelope): void
    {
        /** @var ProcessEvent $message */
        $message = $envelope->getMessage();

        $event = $message->event;

        $this->removeItemFromCache($event);

        $ackMessage = CreateMessage::forAcknowledgement(
            $event->streamId,
            StreamId::allStream(),
            $this->client->applicationId(),
            $event->catchupStreamCheckpoint,
            $event->allSequenceCheckpoint
        );

        $sock = stream_socket_client('unix://' . Client::IPC_URI, $errno, $errst);
        fwrite($sock, $ackMessage->toString());
        fclose($sock);
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

    private static function workerId(): string
    {
        return 'worker-' . getmypid();
    }

    private function removeItemFromCache(Event $event): void
    {
        $availableItemsCacheItem = $this->appCachePool->getItem(ListenForEvents::EVENTS);

        $availableItems = $availableItemsCacheItem->get() ?? [];

        unset($availableItems[$event->allSequenceCheckpoint->toInt()]);

        $availableItemsCacheItem->set($availableItems);

        $this->appCachePool->save($availableItemsCacheItem);
    }

    public function reject(Envelope $envelope): void
    {
        // TODO: Implement reject() method.
    }
}
