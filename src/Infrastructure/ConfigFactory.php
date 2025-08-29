<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Infrastructure;

use PearTreeWeb\EventSourcerer\Client\Infrastructure\Config;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\ApplicationType;

final readonly class ConfigFactory
{
    public static function create(
        string $host,
        string $url,
        int $port,
        string $applicationId
    ): Config {
        return new Config(
            ApplicationType::Symfony,
            $host,
            $url,
            $port,
            $applicationId
        );
    }
}
