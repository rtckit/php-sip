<?php
/**
 * RTCKit\SIP\Exception\InvalidStatusCodeException Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Exception;

/**
 * Exception thrown when processing SIP responses with
 * invalid status codes (RFC3261 Section 7.2)
 */
class InvalidStatusCodeException extends SIPException
{
}
