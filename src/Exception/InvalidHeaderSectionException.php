<?php
/**
 * RTCKit\SIP\Exception\InvalidHeaderSectionException Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Exception;

/**
 * Exception thrown when processing SIP Messages with incorrect
 * Header section formatting (RFC3261 Section 7)
 */
class InvalidHeaderSectionException extends SIPException
{
}
