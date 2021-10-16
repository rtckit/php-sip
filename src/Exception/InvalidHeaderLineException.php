<?php
/**
 * RTCKit\SIP\Exception\InvalidHeaderLineException Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Exception;

/**
 * Exception thrown when processing SIP Messages with invalid
 * Header Lines (RFC3261 Section 7.3)
 */
class InvalidHeaderLineException extends SIPException
{
}
