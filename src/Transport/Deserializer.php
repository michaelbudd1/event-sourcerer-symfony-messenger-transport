<?php

declare(strict_types=1);

namespace EventSourcerer\ClientBundle\Transport;

use EventSourcerer\ClientBundle\ProcessEvent;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\Checkpoint;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\Event;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventName;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\EventVersion;
use PearTreeWebLtd\EventSourcererMessageUtilities\Model\StreamId;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

final class Deserializer implements SerializerInterface
{
    /**
     * @param array{allSequence: int, eventVersion: int, name: string, number: int, payload: array<string, string>, stream: string, occurred: string, catchupRequestStream: string} $encodedEnvelope
     */
    public function decode(array $encodedEnvelope): Envelope
    {
        return new Envelope(
            new ProcessEvent(
                new Event(
                    Checkpoint::fromInt($encodedEnvelope['allSequence']),
                    EventVersion::fromInt($encodedEnvelope['eventVersion']),
                    EventName::fromString($encodedEnvelope['name']),
                    $encodedEnvelope['payload'],
                    StreamId::fromString($encodedEnvelope['stream']),
                    new \DateTimeImmutable($encodedEnvelope['occurred']),
                    StreamId::fromString($encodedEnvelope['catchupRequestStream'])
                )
            )
        );
    }

    public function encode(Envelope $envelope): array
    {
        // TODO: Implement encode() method.
    }
}
