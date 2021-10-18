<?php
/**
 * RTCKit\SIP\Auth\Digest\ChallengeParams Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Auth\Digest;

/**
 * Digest challenge parameters class
 */
class ChallengeParams extends AbstractParams
{
    /** @var string SIP domain */
    public string $domain;

    /** @var bool Stale response flag */
    public bool $stale;

    /** @var array<string> Quality of protection options */
    public array $qop = [];
}
