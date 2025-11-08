<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Command;

use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: self::COMMAND)]
final readonly class CreateExternalSocketConnection
{
    public const string COMMAND = 'eventsourcerer:create-external-connection';

    public function __construct(private Client $client) {}

    public function __invoke(OutputInterface $output): int
    {
        $output->writeln('Starting IPC server');

        $this
            ->client
            ->runIPCServer();

        return Command::SUCCESS;
    }
}
