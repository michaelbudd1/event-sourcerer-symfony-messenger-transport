<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Command;

use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: self::COMMAND)]
final class ListenForEvents
{
    public const string COMMAND = 'eventsourcerer:listen-for-events';

    public function __construct(private readonly Client $client) {}

    public function __invoke(): int
    {
        $this->client->connect()->listenForMessages();

        return Command::SUCCESS;
    }
}
