<?php
/**
 * RTCKit\SIP\Exception\SIPException Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Exception;

use RTCKit\SIP\Message;
use DomainException;
use Throwable;

/**
 * Generic SIP exception
 */
class SIPException extends DomainException implements Throwable
{
    /** @var ?Message Partial parsed message, before the exception was encountered */
    private ?Message $stub = null;

    /**
     * Assigns a message stub to the current exception
     *
     * @param Message $stub Partial message
     */
    public function setStub(Message $stub): void
    {
        $this->stub = $stub;
    }

    /**
     * Retrieve's exception's stub, if any
     *
     * @return ?Message Partial message
     */
    public function getStub(): ?Message
    {
        return $this->stub;
    }
}
