<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Command;

use PearTreeWeb\EventSourcerer\Client\Domain\Model\WorkerId;
use PearTreeWeb\EventSourcerer\Client\Domain\Repository\WorkerMessages;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use Symfony\Component\Console\Attribute\Argument;
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

    public function __construct(private Client $client, private WorkerMessages $workerMessages)
    {
    }

    public function __invoke(#[Argument] string $workerId, OutputInterface $output): int
    {
        $this->client->catchup($this->handleNewEvents(WorkerId::fromString($workerId)));

        $output->writeln('<info>Listening for events</info>');

        return Command::SUCCESS;
    }

    private function handleNewEvents(WorkerId $workerId): callable
    {
        return function (array $decodedEvent) use ($workerId): void {
            $this->workerMessages->addFor($workerId, $decodedEvent);
        };
    }

    #[AsEventListener(ConsoleSignalEvent::class)]
    public function handleSignal(ConsoleSignalEvent $event): void
    {
        if (in_array($event->getHandlingSignal(), [\SIGINT, \SIGTERM], true)) {
            $this->workerMessages->clearFor(
                WorkerId::fromString($event->getInput()->getArgument('worker-id'))
            );

            $event->getOutput()->writeln('<info>Stopped listening to events</info>');
        }
    }
}
