<?php
/**
 * RTCKit\SIP\Header\AbstractAuthHeader Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\Auth\OpaqueParams;
use RTCKit\SIP\Auth\Digest\ChallengeParams as DigestChallengeParams;
use RTCKit\SIP\Auth\Digest\ResponseParams as DigestResponseParams;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;

/**
 * Authentication/Authorization header abstract class
 */
abstract class AbstractAuthHeader
{
    /** @var list<AuthValue> Authentication/Authorization value(s) */
    public array $values = [];

    final public function __construct() {}

    /**
     * Authentication/Authorization field value parser
     *
     * @param list<string> $hbody Header body
     * @throws InvalidHeaderLineException
     * @throws InvalidHeaderParameterException
     * @return AbstractAuthHeader
     */
    public static function parse(array $hbody): AbstractAuthHeader
    {
        $ret = new static;
        $challenge = static::class === AuthenticateHeader::class;

        foreach ($hbody as $hline) {
            $hparts = explode(' ', trim($hline), 2);

            if (count($hparts) !== 2) {
                throw new InvalidHeaderLineException('Invalid Auth header, missing scheme', Response::BAD_REQUEST);
            }

            $val = new AuthValue;
            $val->scheme = strtolower($hparts[0]);

            $params = ltrim($hparts[1]);

            switch ($val->scheme) {
                case DigestChallengeParams::SCHEME_NAME:
                    if ($challenge) {
                        $val->params = DigestChallengeParams::parse($params);
                    } else {
                        $val->params = DigestResponseParams::parse($params);
                    }
                    break;

                default:
                    $val->params = OpaqueParams::parse($params);
                    break;
            }

            $ret->values[] = $val;
        }

        return $ret;
    }

    /**
     * Authentication/Authorization header field value renderer
     *
     * @param string $hname Header field name
     * @throws InvalidHeaderValueException
     * @return string
     */
    public function render(string $hname): string
    {
        $ret = '';

        foreach ($this->values as $key => $value) {
            if (!isset($value->scheme, $value->params)) {
                throw new InvalidHeaderValueException('Malformed auth header, missing scheme and/or parameters');
            }

            $ret .= "{$hname}: {$value->scheme} " . $value->params->render() . "\r\n";
        }

        return $ret;
    }
}
