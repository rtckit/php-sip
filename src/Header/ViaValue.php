<?php
/**
 * RTCKit\SIP\Header\ViaValue Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

/**
 * Via header field Value class
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

    /** @var string Source IP address, different than the Via host */
    public string $received;

    /** @var int Response port */
    public int $rport;

    /** @var array<string,string> Additional parameters */
    public array $params = [];
}
