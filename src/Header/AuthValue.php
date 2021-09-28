<?php
/**
* RTCKit\SIP\AuthValue Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

/**
* Authentication/Authorization Header Field Value Class
*/
class AuthValue
{
    /** @var string Authentication scheme */
    public string $scheme;

    /** @var string Authentication user name */
    public string $username;

    /** @var string Authentication realm */
    public string $realm;

    /** @var string SIP domain */
    public string $domain;

    /** @var string Server's number once */
    public string $nonce;

    /** @var string SIP URI */
    public string $uri;

    /** @var string Response hash */
    public string $response;

    /** @var bool Stale response flag */
    public bool $stale;

    /** @var string Digest algorithm */
    public string $algorithm;

    /** @var string Client's number once */
    public string $cnonce;

    /** @var string Quality of protection */
    public string $qop;

    /** @var int Number once count */
    public int $nc;

    /** @var string Server's opaque data blob */
    public string $opaque;

    /** @var string Generic credentials when attributes aren't used (e.g. Basic) */
    public string $credentials;

    /** @var array<string, string> Additional parameters */
    public array $params = [];
}
