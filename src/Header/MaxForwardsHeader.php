<?php
/**
* RTCKit\SIP\MaxForwardsHeader Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

/**
* Scalar Header Class
*/
class MaxForwardsHeader extends ScalarHeader
{
    /** @var int Maximum allowed value */
    public const MAX_VALUE = 255;
}
