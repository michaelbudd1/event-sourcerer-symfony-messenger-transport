<?php

declare(strict_types=1);

namespace EventSourcerer\SymfonyClient\Infrastructure;

use PearTreeWeb\EventSourcerer\Client\Infrastructure\Config;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationType;

final readonly class ConfigFactory
{
    public static function create(
        string $host,
        string $url,
        int $port,
        string $applicationId,
        bool $createSecure,
        ?string $localCertificateDirectory = null,
        ?bool $verifyPeer = null,
        ?bool $verifyPeerName = null,
        ?bool $allowSelfSigned = null,
        ?string $cafile = null
    ): Config {
        return new Config(
            ApplicationType::Symfony,
            $host,
            $url,
            $port,
            $applicationId,
            $createSecure,
            $localCertificateDirectory,
            $verifyPeer,
            $verifyPeerName,
            $allowSelfSigned,
            $cafile
        );
    }
}
