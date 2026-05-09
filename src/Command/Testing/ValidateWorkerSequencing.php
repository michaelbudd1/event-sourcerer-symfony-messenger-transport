<?php

namespace EventSourcerer\ClientBundle\Command\Testing;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'eventsourcerer:testing:check-worker-sequencing',
    description: 'Validates that workers process streams in correct sequence',
)]
final readonly class ValidateWorkerSequencing
{
    public function __invoke(string $eventsourcererProjectDir, SymfonyStyle $style): int
    {
        $processed = [];

        $errorsFound = 0;

        foreach (self::logFiles($eventsourcererProjectDir) as $logFile) {
            $fh = fopen($eventsourcererProjectDir . '/var/log/' . $logFile, 'rb');

            while (!feof($fh)) {
                $line = fgets($fh);
                $parts = explode(' ', $line);

                if (!isset($parts[1])) {
                    continue;
                }

                $stream = $parts[1];
                $sequence = (int) $parts[3];

                if (isset($processed[$stream][$sequence])) {
                    $errorsFound++;

                    $style->warning(
                        sprintf(
                            'Stream %s sequence %d was processed several times',
                            $stream,
                            $sequence
                        )
                    );
                }

                $processed[$stream][$sequence] = isset($processed[$stream][$sequence])
                    ? $processed[$stream][$sequence] + 1
                    : 1;

                $maxSequence = max(array_keys($processed[$stream]));

                if ($sequence < $maxSequence) {
                    $errorsFound++;

                    $style->warning(
                        sprintf(
                            'Stream %s sequence %d is not in correct order',
                            $stream,
                            $sequence
                        )
                    );
                }
            }
        }

        if (0 === $errorsFound) {
            $style->success('No errors found');
        } else {
            $style->error(sprintf('%d errors found', $errorsFound));
        }

        return Command::SUCCESS;
    }

    private static function logFiles(string $eventsourcererProjectDir): iterable
    {
        $logsDir = $eventsourcererProjectDir . '/var/log';

        foreach (scandir($logsDir) as $logFile) {
            if (preg_match('/worker-\d+\.log$/', $logFile)) {
                yield $logFile;
            }
        }
    }
}