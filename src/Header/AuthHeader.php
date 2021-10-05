<?php
/**
* RTCKit\SIP\AuthHeader Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;

/**
* Authentication/Authorization Header Class
*/
class AuthHeader
{
    public const DIGEST_SCHEME = 'digest';

    /** @var list<AuthValue> Authentication/Authorization value(s) */
    public array $values = [];

    final public function __construct() {}

    /**
     * Authentication/Authorization field value parser
     *
     * @param list<string> $hbody Header body
     * @throws InvalidHeaderLineException
     * @throws InvalidHeaderParameterException
     * @return AuthHeader
     */
    public static function parse(array $hbody): AuthHeader
    {
        $ret = new static;

        foreach ($hbody as $hline) {
            $hparts = explode(' ', trim($hline), 2);

            if (count($hparts) !== 2) {
                throw new InvalidHeaderLineException('Invalid Auth header, missing scheme', Response::BAD_REQUEST);
            }

            $val = new AuthValue;
            $val->scheme = $hparts[0];

            $params = ltrim($hparts[1]);

            if (strtolower($val->scheme) === self::DIGEST_SCHEME) {
                while (strlen($params)) {
                    $pos = strpos($params, '=');

                    if ($pos === false) {
                        throw new InvalidHeaderLineException('Invalid Auth header, valueless parameter', Response::BAD_REQUEST);
                    }

                    $pk = rtrim(substr($params, 0, $pos));
                    $pv = '';
                    $params = ltrim(substr($params, $pos + 1));

                    if (!isset($params[0])) {
                        throw new InvalidHeaderLineException('Invalid Auth header, valueless parameter', Response::BAD_REQUEST);
                    }

                    if ($params[0] === '"') {
                        $offset = 1;
                        $escQuotes = false;

                        while (true) {
                            $pos = strpos($params, '"', $offset);

                            if ($pos === false) {
                                throw new InvalidHeaderLineException('Invalid Auth header, unmatched parameter value enclosing', Response::BAD_REQUEST);
                            }

                            if ($params[$pos - 1] !== '\\') {
                                break;
                            }

                            $escQuotes = true;
                            $offset = $pos + 1;
                        }

                        $pv = substr($params, 1, $pos - 1);

                        if ($escQuotes) {
                            $pv = str_replace('\"', '"', $pv);
                        }

                        $params = ltrim(substr($params, $pos + 1));

                        if (isset($params[0])) {
                            if ($params[0] !== ',') {
                                throw new InvalidHeaderLineException('Invalid Auth header, invalid parameter value enclosing', Response::BAD_REQUEST);
                            }

                            $params = ltrim(substr($params, 1));
                        }
                    } else {
                        $pos = strpos($params, ',');

                        if ($pos !== false) {
                            $pv = rtrim(substr($params, 0, $pos));
                            $params = ltrim(substr($params, $pos + 1));
                        } else {
                            $pv = rtrim($params);
                            $params = '';
                        }
                    }

                    switch ($pk) {
                        case 'username':
                            $val->username = $pv;
                            break;

                        case 'realm':
                            $val->realm = $pv;
                            break;

                        case 'domain':
                            $val->domain = $pv;
                            break;

                        case 'nonce':
                            $val->nonce = $pv;
                            break;

                        case 'uri':
                            $val->uri = $pv;
                            break;

                        case 'response':
                            $val->response = $pv;
                            break;

                        case 'stale':
                            $pv = strtolower($pv);

                            if ($pv === 'true') {
                                $val->stale = true;
                            } else if ($pv === 'false') {
                                $val->stale = false;
                            } else {
                                throw new InvalidHeaderParameterException('Invalid Auth header, non-boolean stale parameter', Response::BAD_REQUEST);
                            }
                            break;

                        case 'algorithm':
                            $val->algorithm = $pv;
                            break;

                        case 'cnonce':
                            $val->cnonce = $pv;
                            break;

                        case 'qop':
                            $val->qop = $pv;
                            break;

                        case 'nc':
                            if (!ctype_xdigit($pv)) {
                                throw new InvalidHeaderParameterException('Invalid Auth header, non-hexadecimal nc parameter', Response::BAD_REQUEST);
                            }

                            $val->nc = $pv;
                            break;

                        case 'opaque':
                            $val->opaque = $pv;
                            break;

                        default:
                            $val->params[$pk] = $pv;
                            break;
                    }
                }
            } else {
                $val->credentials = $params;
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
            if (!isset($value->scheme)) {
                throw new InvalidHeaderValueException('Malformed auth header, missing scheme');
            }

            $params = [];

            if (strtolower($value->scheme) === self::DIGEST_SCHEME) {
                if (isset($value->username)) {
                    $params[] = "username=\"{$value->username}\"";
                }

                if (isset($value->realm)) {
                    $params[] = "realm=\"{$value->realm}\"";
                }

                if (isset($value->domain)) {
                    $params[] = "domain=\"{$value->domain}\"";
                }

                if (isset($value->nonce)) {
                    $params[] = "nonce=\"{$value->nonce}\"";
                }

                if (isset($value->uri)) {
                    $params[] = "uri=\"{$value->uri}\"";
                }

                if (isset($value->response)) {
                    $params[] = "response=\"{$value->response}\"";
                }

                if (isset($value->stale)) {
                    $params[] = 'stale=' . ($value->stale ? 'TRUE' : 'FALSE');
                }

                if (isset($value->algorithm)) {
                    $params[] = "algorithm={$value->algorithm}";
                }

                if (isset($value->cnonce)) {
                    $params[] = "cnonce=\"{$value->cnonce}\"";
                }

                if (isset($value->qop)) {
                    $params[] = "qop=\"{$value->qop}\"";
                }

                if (isset($value->nc)) {
                    $params[] = "nc={$value->nc}";
                }

                if (isset($value->opaque)) {
                    $params[] = "opaque=\"{$value->opaque}\"";
                }
            } else {
                if (!isset($value->credentials)) {
                    throw new InvalidHeaderValueException('Malformed auth header, missing credentials');
                }

                $params[] = $value->credentials;
            }

            foreach ($value->params as $pk => $pv) {
                $params[] = "{$pk}={$pv}";
            }

            $paramStr = implode(',', $params);

            $ret .= "{$hname}: {$value->scheme} {$paramStr}\r\n";
        }

        return $ret;
    }
}
