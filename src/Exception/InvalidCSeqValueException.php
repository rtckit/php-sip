<?php
/**
 * RTCKit\SIP\Exception\InvalidCSeqValueException Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Exception;

/**
 * Exception thrown when processing SIP Messages with conflicting
 * CSeq header values
 */
class InvalidCSeqValueException extends SIPException
{
}
