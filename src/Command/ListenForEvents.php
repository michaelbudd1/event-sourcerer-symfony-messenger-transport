<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Command;

use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsCommand(name: self::COMMAND)]
final readonly class ListenForEvents
{
    public const string COMMAND = 'eventsourcerer:listen-for-events';

    public function __construct(private Client $client) {}

    public function __invoke(OutputInterface $output): int
    {
        $this
            ->client
            ->connect()
            ->listenForMessages();

        $output->writeln('<info>Listening for events</info>');

        return Command::SUCCESS;
    }

    #[AsEventListener(ConsoleSignalEvent::class)]
    public function handleSignal(ConsoleSignalEvent $event): void
    {
        if (in_array($event->getHandlingSignal(), [\SIGINT, \SIGTERM], true)) {
            $event->getOutput()->writeln('<info>Stopped listening to events</info>');
        }
    }
}
