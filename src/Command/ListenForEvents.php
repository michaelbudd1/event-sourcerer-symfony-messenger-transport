<?php

declare(strict_types=1);

namespace EventSourcerer\SymfonyClient\Command;

use PearTreeWeb\EventSourcerer\Client\Domain\Repository\WorkerMessages;
use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use React\EventLoop\Loop;
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

    public function __construct(private Client $client, private WorkerMessages $workerMessages)
    {
    }

    public function __invoke(#[Argument] string $workerId, #[Argument] string $parentPid, OutputInterface $output): int
    {
        $workerIdObject = WorkerId::fromString($workerId);

        $this->client->catchup($workerIdObject, $this->handleNewEvents((int) $parentPid));

        $output->writeln(
            sprintf(
                '<info>Worker "%s" listening for events</info>',
                $workerId
            )
        );

        Loop::run();

        return Command::SUCCESS;
    }

    private function handleNewEvents(int $parentPid): callable
    {
        return function (array $decodedEvent) use ($parentPid): void {
            if (!posix_kill($parentPid, 0)) {
                exit(0);
            }
            /**
             * @var array{
             *     allSequence: int,
             *     eventVersion: int,
             *     name: string,
             *     number: int,
             *     payload: array,
             *     stream: string,
             *     occurred: string,
             *     workerId: string,
             *     catchupRequestStream: string,
             * } $decodedEvent
             */
            $this->workerMessages->addFor(
                WorkerId::fromString($decodedEvent['workerId']),
                $decodedEvent
            );
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
