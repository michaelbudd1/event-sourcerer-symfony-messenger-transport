<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Command;

use PearTreeWeb\EventSourcerer\Client\Infrastructure\Client;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\ApplicationId;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'eventsourcerer:summary')]
final readonly class GetSummary
{
    public function __construct(private Client $client) {}

    public function __invoke(#[Argument('Application ID')] string $applicationId, OutputInterface $output): int
    {
        $summary = $this->client->summary(ApplicationId::fromString($applicationId));

        $output->writeln(
            sprintf(
                '<info>There are %d events ready to consume</info>',
                $summary['numberOfEventsToProcess']
            )
        );

        if (empty($summary['workerBucketDistribution'])) {
            $output->writeln(
                '<info>The worker bucket distribution is empty</info>'
            );
        }

        foreach ($summary['workerBucketDistribution'] as $worker => $buckets) {
            $output->writeln(
                sprintf(
                    '<info>%s is mapped to buckets %s</info>',
                    $worker,
                    implode(', ', $buckets)
                )
            );
        }

        return Command::SUCCESS;
    }
}
