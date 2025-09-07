<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Command;

use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\Checkpoint;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: self::COMMAND)]
final class AckEvent extends Command
{
    public const string COMMAND = 'eventsourcerer:ack-event';

    public function __construct(private readonly Client $client)
    {
        parent::__construct();
    }

    public function __invoke(
        #[Argument('Stream ID')] string $streamId,
        #[Argument('The All Stream checkpoint')] string $allStreamCheckpoint,
        #[Argument('The Catchup Stream checkpoint')] string $catchupStreamCheckpoint,
        OutputInterface $output
    ): int {
        $this
            ->client
            ->connect()
            ->acknowledgeEvent(
                StreamId::fromString($streamId),
                Checkpoint::fromString($catchupStreamCheckpoint),
                Checkpoint::fromString($allStreamCheckpoint)
            );

        return Command::SUCCESS;
    }
}
