<?php
/**
 * RTCKit\SIP\Auth\Digest\ResponseParams Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Auth\Digest;

use RTCKit\SIP\Exception\AuthException;

/**
 * Digest response parameters class
 */
class ResponseParams extends AbstractParams
{
    /** @var string Session digest suffix */
    public const ALGORITHM_SESS_SUFFIX = '-SESS';

    /** @var array<string,string> Alternative algorithm map */
    public const ALGORITHM_MAP = [
        'SHA-256' => 'sha256',
        'SHA-512-256' => 'sha512/256',
    ];

    /** @var string Authentication user name */
    public string $username;

    /** @var string Effective request URI */
    public string $uri;

    /** @var string Client's number once */
    public string $cnonce;

    /** @var string Number once count */
    public string $nc;

    /** @var string Response hash */
    public string $response;

    /** @var string Quality of protection */
    public string $qop;

    /**
     * Computes the response hash for the given parameters; missing key fields will throw exceptions
     *
     * @param string $method SIP request method
     * @param ?string $secret Authentication secret
     * @param ?string $hash Precalculated A1 hash
     * @param ?string $body Request's body for auth-int Quality of Protection
     * @throws AuthException
     * @return string
     */
    public function hash(string $method, ?string $secret, ?string $hash = null, ?string $body = null): string
    {
        if (!isset($this->algorithm)) {
            $algo = self::DEFAULT_ALGORITHM;
        } else {
            $algo = $this->algorithm;
        }

        $algo = strtoupper($algo);
        $sPos = strpos($algo, self::ALGORITHM_SESS_SUFFIX);
        $sess = $sPos !== false;

        if ($sess) {
            $algo = substr($algo, 0, $sPos);
        }

        if (isset($hash)) {
            $a1 = $hash;
        } else {
            $a1 = $this->hashString($algo, "{$this->username}:{$this->realm}:{$secret}");
        }

        if ($sess) {
            $a1 = $this->hashString($algo, "{$a1}:{$this->nonce}:{$this->cnonce}");
        }

        if (!isset($this->qop) || ($this->qop === self::QOP_AUTH)) {
            $a2 = $this->hashString($algo, "{$method}:{$this->uri}");
        } else if ($this->qop === self::QOP_AUTH_INT) {
            if (!isset($body)) {
                $body = '';
            }

            $bodyHash = $this->hashString($algo, $body);
            $a2 = $this->hashString($algo, "{$method}:{$this->uri}:{$bodyHash}");
        } else {
            throw new AuthException('Unknown digest quality-of-protection: ' . $this->qop);
        }

        if (!isset($this->qop)) {
            return $this->hashString($algo, "{$a1}:{$this->nonce}:{$a2}");
        }

        return $this->hashString($algo, "{$a1}:{$this->nonce}:{$this->nc}:{$this->cnonce}:{$this->qop}:{$a2}");
    }

    /**
     * Calculates the hash of a given string for a specific algorithm
     *
     * @param string $algo Algorithm
     * @param string $input String to hash
     * @throws AuthException
     * @return string
     */
    private function hashString(string $algo, string $input): string
    {
        if ($algo === self::DEFAULT_ALGORITHM) {
            return md5($input);
        }

        if (!extension_loaded('hash') || !isset(self::ALGORITHM_MAP[$algo])) {
            throw new AuthException('Unsupported digest algorithm: ' . $algo);
        }

        return hash(self::ALGORITHM_MAP[$algo], $input);
    }
}
