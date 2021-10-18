<?php
/**
 * RTCKit\SIP\Header\MaxForwardsHeader Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

/**
 * Max-Forwards header class
 */
class MaxForwardsHeader extends ScalarHeader
{
    /** @var int Maximum allowed value */
    public const MAX_VALUE = 255;
}
