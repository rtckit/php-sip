<?php
/**
 * RTCKit\SIP\URI Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP;

use RTCKit\SIP\Exception\InvalidURIException;

/**
 * SIP URI class
 */
class URI
{
    /** @var int Maximum allowed value for port URI component */
    public const MAX_PORT_NUMBER = 65535;

    /** @var string URI scheme (e.g. sip, sips, tel etc.) */
    public string $scheme;

    /** @var string URI username */
    public string $user;

    /** @var string URI password */
    public string $password;

    /** @var string URI host name (i.e. IP or FQDN) */
    public string $host;

    /** @var bool Whether or not the host name is an IPv6 address */
    public bool $ipv6 = false;

    /** @var int URI port number */
    public int $port;

    /** @var string Transport URI parameter */
    public string $transport;

    /** @var string MAddr URI parameter */
    public string $maddr;

    /** @var int TTL URI parameter */
    public int $ttl;

    /** @var string User URI parameter */
    public string $userParam;

    /** @var string Method URI parameter */
    public string $method;

    /** @var string LR URI parameter */
    public string $lr;

    /** @var array<string,string> URI parameters */
    public array $params = [];

    /** @var array<string,string> URI headers */
    public array $headers = [];

    /**
     * SIP URI parser
     *
     * @param string $text URI text
     * @throws InvalidURIException
     * @return URI
     */
    public static function parse(string $text): URI
    {
        $pos = strpos($text, ':');

        if ($pos === false) {
            throw new InvalidURIException('Malformed URI: ' . $text, Response::BAD_REQUEST);
        }

        $uri = new self;
        $uri->scheme = strtolower(substr($text, 0, $pos));

        $text = substr($text, $pos + 1);
        $pos = strpos($text, '@');

        if ($pos !== false) {
            $userInfo = substr($text, 0, $pos);
            $text = substr($text, $pos + 1);
            $pos = strpos($userInfo, ':');

            if ($pos !== false) {
                $uri->user = self::unescape(substr($userInfo, 0, $pos));
                $uri->password = self::unescape(substr($userInfo, $pos + 1));
            } else {
                $uri->user = self::unescape($userInfo);
            }
        }

        $pos = strpos($text, '?');

        if ($pos !== false) {
            $query = substr($text, $pos + 1);
            $text = substr($text, 0, $pos);

            parse_str($query, $uri->headers);
        }

        $pos = strpos($text, ';');

        if ($pos !== false) {
            $params = substr($text, $pos + 1);
            $text = substr($text, 0, $pos);
            $pairs = explode(';', $params);

            foreach ($pairs as $pair) {
                $p = explode('=', $pair);

                $p[0] = strtolower(self::unescape(trim($p[0])));

                if (!isset($p[0][0])) {
                    throw new InvalidURIException('Empty parameter name in URI', Response::BAD_REQUEST);
                }

                $pv = isset($p[1]) ? strtolower(self::unescape(trim($p[1]))) : '';

                switch ($p[0]) {
                    case 'transport':
                        $uri->transport = $pv;
                        break;

                    case 'maddr':
                        if (!filter_var($pv, FILTER_VALIDATE_IP)) {
                            throw new InvalidURIException('Invalid maddr URI parameter: ' . $pv, Response::BAD_REQUEST);
                        }

                        $uri->maddr = $pv;
                        break;

                    case 'ttl':
                        if (!ctype_digit($pv)) {
                            throw new InvalidURIException('Invalid ttl URI parameter: ' . $pv, Response::BAD_REQUEST);
                        }

                        $uri->ttl = (int)$pv;
                        break;

                    case 'user':
                        $uri->userParam = $pv;
                        break;

                    case 'method':
                        $uri->method = $pv;
                        break;

                    case 'lr':
                        $uri->lr = $pv;
                        break;

                    default:
                        if (isset($uri->params[$p[0]])) {
                            throw new InvalidURIException('Duplicate URI parameter: ' . $p[0], Response::BAD_REQUEST);
                        }

                        $uri->params[$p[0]] = $pv;
                        break;
                }
            }
        }

        $pos = 0;

        if ($text[0] === '[') {
            $pos = strpos($text, ']');

            if ($pos === false) {
                throw new InvalidURIException('Improperly escaped IPv6 host: ' . $text, Response::BAD_REQUEST);
            }

            $uri->ipv6 = true;
            $pos++;
        }

        $pos = strpos($text, ':', $pos);

        if ($pos !== false) {
            $port = substr($text, $pos + 1);
            $text = substr($text, 0, $pos);

            if (!ctype_digit($port)) {
                throw new InvalidURIException('Invalid port number: ' . $port, Response::BAD_REQUEST);
            }

            $uri->port = (int)$port;

            if ($uri->port > self::MAX_PORT_NUMBER) {
                throw new InvalidURIException('Port number out of bounds: ' . $port, Response::BAD_REQUEST);
            }
        }

        $text = strtolower($text);

        if ($uri->ipv6) {
            $text = trim($text, '[]');
        }

        if (($uri->scheme == 'sip') || ($uri->scheme == 'sips')) {
            if (!filter_var($text, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) && !filter_var($text, FILTER_VALIDATE_IP)) {
                throw new InvalidURIException('Invalid host: ' . $text, Response::BAD_REQUEST);
            }
        } else {
            $text = ltrim($text, '/');
        }

        $uri->host = $text;

        return $uri;
    }

    /**
     * SIP URI renderer
     *
     * @throws InvalidURIException
     * @return string
     */
    public function render(): string
    {
        if (!isset($this->scheme)) {
            throw new InvalidURIException('Cannot render URI without scheme', Response::INTERNAL_SERVER_ERROR);
        }

        if (!isset($this->host)) {
            throw new InvalidURIException('Cannot render URI without host', Response::INTERNAL_SERVER_ERROR);
        }

        $ret = $this->scheme . ':';

        if (isset($this->user)) {
            $ret .= $this->user;

            if (isset($this->password)) {
                $ret .= ':' . $this->password;
            }

            $ret .= '@';
        }

        if ($this->ipv6) {
            $ret .= "[{$this->host}]";
        } else {
            $ret .= $this->host;
        }

        if (isset($this->port)) {
            $ret .= ':' . $this->port;
        }

        if (isset($this->transport)) {
            $ret .= ';transport';

            if (isset($this->transport[0])) {
                $ret .= '=' . self::escape($this->transport);
            }
        }

        if (isset($this->maddr)) {
            $ret .= ';maddr';

            if (isset($this->maddr[0])) {
                $ret .= '=' . self::escape($this->maddr);
            }
        }

        if (isset($this->ttl)) {
            $ret .= ';ttl=' . $this->ttl;
        }

        if (isset($this->userParam)) {
            $ret .= ';user';

            if (isset($this->userParam[0])) {
                $ret .= '=' . self::escape($this->userParam);
            }
        }

        if (isset($this->method)) {
            $ret .= ';method';

            if (isset($this->method[0])) {
                $ret .= '=' . self::escape($this->method);
            }
        }

        if (isset($this->lr)) {
            $ret .= ';lr';

            if (isset($this->lr[0])) {
                $ret .= '=' . self::escape($this->lr);
            }
        }

        foreach ($this->params as $pk => $pv) {
            $ret .= ';' . self::escape($pk);

            if (isset($pv[0])) {
                $ret .= '=' . self::escape($pv);
            }
        }

        if (count($this->headers)) {
            $ret .= '?' . http_build_query($this->headers);
        }

        return $ret;
    }

    /**
     * SIP URI equivalence assessor
     *
     * For performance reasons, it is assumed that all properties but $user and $password
     * are already converted to lowercase, matching the parser's behaviour.
     *
     * https://datatracker.ietf.org/doc/html/rfc3261#section-19.1.4
     *
     * @param URI $uri URI to compare against
     * @throws InvalidURIException
     * @return bool
     */
    public function isEquivalent(URI $uri): bool
    {
        if (!isset($this->scheme, $this->host, $uri->scheme, $uri->host)) {
            throw new InvalidURIException('Cannot compare invalid URIs', Response::INTERNAL_SERVER_ERROR);
        }

        if ($this->scheme !== $uri->scheme) {
            return false;
        }

        if ((isset($this->user) xor isset($uri->user)) || (isset($this->user) && ($this->user !== $uri->user))) {
            return false;
        }

        if ((isset($this->password) xor isset($uri->password)) || (isset($this->password) && ($this->password !== $uri->password))) {
            return false;
        }

        if ($this->host !== $uri->host) {
            return false;
        }

        if ((isset($this->port) xor isset($uri->port)) || (isset($this->port) && ($this->port !== $uri->port))) {
            return false;
        }

        if ((isset($this->transport) xor isset($uri->transport)) || (isset($this->transport) && ($this->transport !== $uri->transport))) {
            return false;
        }

        if ((isset($this->maddr) xor isset($uri->maddr)) || (isset($this->maddr) && ($this->maddr !== $uri->maddr))) {
            return false;
        }

        if ((isset($this->ttl) xor isset($uri->ttl)) || (isset($this->ttl) && ($this->ttl !== $uri->ttl))) {
            return false;
        }

        if ((isset($this->userParam) xor isset($uri->userParam)) || (isset($this->userParam) && ($this->userParam !== $uri->userParam))) {
            return false;
        }

        if ((isset($this->method) xor isset($uri->method)) || (isset($this->method) && ($this->method !== $uri->method))) {
            return false;
        }

        $params = array_intersect(array_keys($this->params), array_keys($uri->params));

        foreach ($params as $param) {
            if ($this->params[$param] !== $uri->params[$param]) {
                return false;
            }
        }

        if ($this->headers != $uri->headers) {
            return false;
        }

        return true;
    }

    /**
     * Unescapes a URI component
     *
     * @param string $text component
     * @return string
     */
    public static function unescape(string $text): string
    {
        return urldecode(str_replace('+', '%2B', $text));
    }

    /**
     * Escapes a URI component
     *
     * @param string $text component
     * @return string
     */
    public static function escape(string $text): string
    {
        return str_replace('%2B', '+', urlencode($text));
    }
}
