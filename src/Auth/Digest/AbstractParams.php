<?php
/**
 * RTCKit\SIP\Auth\Digest\AbstractParams Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Auth\Digest;

use RTCKit\SIP\Response;
use RTCKit\SIP\Auth\ParamsInterface;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;

/**
 * Digest authentication scheme parameters abstract class
 */
abstract class AbstractParams implements ParamsInterface
{
    /** @var string Lowercase scheme name */
    public const SCHEME_NAME = 'digest';

    /** @var string Scheme name using preferred/common case */
    public const PREFERRED_CASE = 'Digest';

    /** @var string "auth" Quality of protection */
    public const QOP_AUTH = 'auth';

    /** @var string "auth-int" Quality of protection */
    public const QOP_AUTH_INT = 'auth-int';

    /** @var string Default hashing algorithm */
    public const DEFAULT_ALGORITHM = 'MD5';

    /** @var string Authentication realm */
    public string $realm;

    /** @var string Digest algorithm */
    public string $algorithm;

    /** @var string Server's number once */
    public string $nonce;

    /** @var string Server's opaque data blob */
    public string $opaque;

    /** @var array<string,string> Additional parameters */
    public array $extra = [];

    final public function __construct() {}

    /**
     * Parses digest parameters out of a string input
     *
     * @param string $input Unparsed digest parameters
     * @throws InvalidHeaderLineException
     * @throws InvalidHeaderParameterException
     * @return ParamsInterface Parsed Challenge or Response parameters
     */
    public static function parse(string $input): ParamsInterface
    {
        $orig = $input;
        $params = new static;
        $challenge = static::class === ChallengeParams::class;

        while (strlen($input)) {
            $pos = strpos($input, '=');

            if ($pos === false) {
                throw new InvalidHeaderLineException('Invalid Auth header, valueless parameter', Response::BAD_REQUEST);
            }

            $pk = rtrim(substr($input, 0, $pos));
            $pv = '';
            $input = ltrim(substr($input, $pos + 1));

            if (!isset($input[0])) {
                throw new InvalidHeaderLineException('Invalid Auth header, valueless parameter', Response::BAD_REQUEST);
            }

            if ($input[0] === '"') {
                $offset = 1;
                $escQuotes = false;

                while (true) {
                    $pos = strpos($input, '"', $offset);

                    if ($pos === false) {
                        throw new InvalidHeaderLineException('Invalid Auth header, unmatched parameter value enclosing', Response::BAD_REQUEST);
                    }

                    if ($input[$pos - 1] !== '\\') {
                        break;
                    }

                    $escQuotes = true;
                    $offset = $pos + 1;
                }

                $pv = substr($input, 1, $pos - 1);

                if ($escQuotes) {
                    $pv = str_replace('\"', '"', $pv);
                }

                $input = ltrim(substr($input, $pos + 1));

                if (isset($input[0])) {
                    if ($input[0] !== ',') {
                        throw new InvalidHeaderLineException('Invalid Auth header, invalid parameter value enclosing', Response::BAD_REQUEST);
                    }

                    $input = ltrim(substr($input, 1));
                }
            } else {
                $pos = strpos($input, ',');

                if ($pos !== false) {
                    $pv = rtrim(substr($input, 0, $pos));
                    $input = ltrim(substr($input, $pos + 1));
                } else {
                    $pv = rtrim($input);
                    $input = '';
                }
            }

            switch ($pk) {
                case 'realm':
                    $params->realm = $pv;
                    break;

                case 'algorithm':
                    $params->algorithm = $pv;
                    break;

                case 'nonce':
                    $params->nonce = $pv;
                    break;

                case 'opaque':
                    $params->opaque = $pv;
                    break;

                default:
                    if ($challenge) {
                        assert($params instanceof ChallengeParams);

                        switch ($pk) {
                            case 'domain':
                                $params->domain = $pv;
                                break 2;

                            case 'stale':
                                $pv = strtolower($pv);

                                if ($pv === 'true') {
                                    $params->stale = true;
                                } else if ($pv === 'false') {
                                    $params->stale = false;
                                } else {
                                    throw new InvalidHeaderParameterException('Invalid Auth header, non-boolean stale parameter', Response::BAD_REQUEST);
                                }
                                break 2;

                            case 'qop':
                                $params->qop = explode(',', $pv);
                                break 2;
                        }
                    } else {
                        assert($params instanceof ResponseParams);

                        switch ($pk) {
                            case 'username':
                                $params->username = $pv;
                                break 2;

                            case 'uri':
                                $params->uri = $pv;
                                break 2;

                            case 'cnonce':
                                $params->cnonce = $pv;
                                break 2;

                            case 'nc':
                                if (!ctype_xdigit($pv)) {
                                    throw new InvalidHeaderParameterException('Invalid Auth header, non-hexadecimal nc parameter', Response::BAD_REQUEST);
                                }

                                $params->nc = $pv;
                                break 2;

                            case 'response':
                                if (!ctype_xdigit($pv)) {
                                    throw new InvalidHeaderParameterException('Invalid Auth header, non-hexadecimal response parameter', Response::BAD_REQUEST);
                                }

                                $params->response = $pv;
                                break 2;

                            case 'qop':
                                $params->qop = $pv;
                                break 2;
                        }
                    }

                    $params->extra[$pk] = $pv;
                    break;
            }
        }

        return $params;
    }

    /**
     * Renders digest authentication scheme parameters as string
     *
     * @return string Digest authentication parameters
     */
    public function render(): string
    {
        $params = [];

        if (isset($this->realm)) {
            $params[] = "realm=\"{$this->realm}\"";
        }

        if (isset($this->algorithm)) {
            $params[] = "algorithm={$this->algorithm}";
        }

        if (isset($this->nonce)) {
            $params[] = "nonce=\"{$this->nonce}\"";
        }

        if (isset($this->opaque)) {
            $params[] = "opaque=\"{$this->opaque}\"";
        }

        if (static::class === ChallengeParams::class) {
            if (isset($this->domain)) {
                $params[] = "domain=\"{$this->domain}\"";
            }

            if (isset($this->stale)) {
                $params[] = 'stale=' . ($this->stale ? 'TRUE' : 'FALSE');
            }

            if (isset($this->qop) && count($this->qop)) {
                $params[] = 'qop="' . implode(',', $this->qop) . '"';
            }
        } else {
            if (isset($this->username)) {
                $params[] = "username=\"{$this->username}\"";
            }

            if (isset($this->uri)) {
                $params[] = "uri=\"{$this->uri}\"";
            }

            if (isset($this->response)) {
                $params[] = "response=\"{$this->response}\"";
            }

            if (isset($this->cnonce)) {
                $params[] = "cnonce=\"{$this->cnonce}\"";
            }

            if (isset($this->qop)) {
                $params[] = "qop={$this->qop}";
            }

            if (isset($this->nc)) {
                $params[] = "nc={$this->nc}";
            }
        }

        foreach ($this->extra as $pk => $pv) {
            $params[] = "{$pk}={$pv}";
        }

        return implode(',', $params);
    }
}
