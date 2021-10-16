<?php
/**
 * RTCKit\SIP\Header\ContactValue Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\URI;

/**
 * Contact header field value class
 */
class ContactValue
{
    /** @var URI Parsed address portion of the Contact value */
    public URI $uri;

    /** @var string Display name portion of the Contact value */
    public string $name;

    /** @var float Q parameter, if provided */
    public float $q;

    /** @var int Expires parameter, if provided */
    public int $expires;

    /** @var array<string,string> Additional/extension parameters */
    public array $params = [];
}
