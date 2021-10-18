<?php
/**
 * RTCKit\SIP\Exception\InvalidProtocolVersionException Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Exception;

/**
 * Exception thrown when processing SIP Messages with a version
 * different than SIP/2.0 (RFC3261 Section 7)
 */
class InvalidProtocolVersionException extends SIPException
{
}
