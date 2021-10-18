<?php
/**
 * RTCKit\SIP\Exception\InvalidMessageStartLineException Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Exception;

/**
 * Exception thrown when processing SIP Messages with invalid
 * Start Lines (RFC3261 Section 7)
 */
class InvalidMessageStartLineException extends SIPException
{
}
