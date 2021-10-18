<?php
/**
 * RTCKit\SIP\Header\AuthValue Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Auth\ParamsInterface;

/**
 * Authentication/Authorization header field value class
 */
class AuthValue
{
    /** @var string Authentication scheme */
    public string $scheme;

    /** @var ParamsInterface Scheme-specific parameters */
    public ParamsInterface $params;
}
