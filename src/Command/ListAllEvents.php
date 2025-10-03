<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Command;

use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'eventsourcerer:list-all-events')]
final readonly class ListAllEvents
{
    public function __construct(private Client $client) {}

    public function __invoke(#[Argument('Application ID')] string $applicationId, OutputInterface $output): int
    {
        foreach ($this->client->connect()->list($applicationId) as $event) {
            $output->writeln($event);
        }

        return Command::SUCCESS;
    }
}
