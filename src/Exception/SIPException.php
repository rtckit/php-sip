<?php
/**
* RTCKit\SIP\Exception\SIPException Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP\Exception;

use DomainException;
use Throwable;

/**
* Generic SIP exception
*/
class SIPException extends DomainException implements Throwable
{
}
