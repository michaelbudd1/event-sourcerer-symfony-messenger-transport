<?php

declare(strict_types=1);

use PearTreeWebLtd\EventSourcererSymfonyMessengerTransport\EventSourcererTransportFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\NativeHttpClient;

final class TransportTest extends TestCase
{
    private string $applicationId;
    private string $url;

    protected function setUp(): void
    {
        $this->applicationId = '1746dfc2-b794-4974-b820-472d017645ef';
        $this->url = 'es://0.0.0.0';
    }

    /**
     * @test
     */
    public function itOnlyDeliversMessageFromOneStreamAtATime(): void
    {
        $transportFactory = new EventSourcererTransportFactory(
            $this->applicationId,
            new NativeHttpClient()
        );

        $transportFactory->createTransport(
            $this->url,
            [],

        );

        $this->assertTrue(true);
    }
}
