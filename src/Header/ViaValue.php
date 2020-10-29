<?php
/**
* RTCKit\SIP\ViaValue Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

/**
* Via Header Field Value Class
*/
class ViaValue
{
    /** @var string Via protocol name (i.e. SIP) */
    public string $protocol;

    /** @var string Via protocol version (i.e. 2.0) */
    public string $version;

    /** @var string Via transport (e.g. UDP, TCP, WSS etc.) */
    public string $transport;

    /** @var string Via host */
    public string $host;

    /** @var string Via branch parameters */
    public string $branch;

    /** @var array<string, string> Additional parameters */
    public array $params = [];
}
