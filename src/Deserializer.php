<?php

declare(strict_types=1);

namespace PearTreeWebLtd\EventSourcererSymfonyMessengerTransport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

final class Deserializer implements SerializerInterface
{
    public function decode(array $encodedEnvelope): Envelope
    {
        return new Envelope(
            new ProcessEvent($encodedEnvelope)
        );
    }

    public function encode(Envelope $envelope): array
    {
        // TODO: Implement encode() method.
    }
}
