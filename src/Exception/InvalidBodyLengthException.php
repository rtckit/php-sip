<?php
/**
 * RTCKit\SIP\Exception\InvalidBodyLengthException Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Exception;

/**
 * Exception thrown when processing SIP Messages conflicting body length
 * definition (RFC3261 Section 22.14)
 */
class InvalidBodyLengthException extends SIPException
{
}
