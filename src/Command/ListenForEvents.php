<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Command;

use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsCommand(name: self::COMMAND)]
final readonly class ListenForEvents
{
    public const string COMMAND = 'eventsourcerer:listen-for-events';
    public const string EVENTS = 'events';

    public function __construct(private Client $client, private CacheItemPoolInterface $appCachePool)
    {
    }

    public function __invoke(OutputInterface $output): int
    {
        $this->client->catchup($this->handleNewEvents());

        $output->writeln('<info>Listening for events</info>');

        return Command::SUCCESS;
    }

    private function handleNewEvents(): callable
    {
        return function (array $decodedEvent): void {
            echo 'Adding event with all sequence ' . $decodedEvent['allSequence'] . ' to cache' . PHP_EOL;

            $eventsCacheItem = $this->appCachePool->getItem(self::EVENTS);

            $items = $eventsCacheItem->get() ?? [];

            $items[$decodedEvent['allSequence']] = $decodedEvent;

            $eventsCacheItem->set($items);

            $this->appCachePool->save($eventsCacheItem);
        };
    }

    #[AsEventListener(ConsoleSignalEvent::class)]
    public function handleSignal(ConsoleSignalEvent $event): void
    {
        if (in_array($event->getHandlingSignal(), [\SIGINT, \SIGTERM], true)) {
            $event->getOutput()->writeln('<info>Stopped listening to events</info>');
        }
    }
}
