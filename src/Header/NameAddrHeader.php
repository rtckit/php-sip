<?php
/**
 * RTCKit\SIP\Header\NameAddrHeader Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\URI;
use RTCKit\SIP\Exception\InvalidDuplicateHeaderException;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;

/**
 * Name-Addr header class
 */
class NameAddrHeader
{
    /** @var URI Parsed address portion of the field value */
    public URI $uri;

    /** @var string Display name portion of the field value */
    public string $name;

    /** @var string Tag parameyer */
    public string $tag;

    /** @var array<string,string> Additional/extension parameters */
    public array $params = [];

    final public function __construct() {}

    /**
     * Name-addr header fields parser
     *
     * @param list<string> $hbody Header body
     * @throws InvalidDuplicateHeaderException
     * @throws InvalidHeaderLineException
     * @throws InvalidHeaderParameterException
     * @return NameAddrHeader
     */
    public static function parse(array $hbody): NameAddrHeader
    {
        $ret = new static;

        if (isset($hbody[1])) {
            throw new InvalidDuplicateHeaderException('Cannot have more than one name-addr header', Response::BAD_REQUEST);
        }

        $len = strlen($hbody[0]);
        $fetchParams = false;
        $lastWsp = -1;
        $quoted = false;
        $qfrom = null;
        $afrom = null;
        $addr = null;

        for ($i = 0; $i <= $len; $i++) {
            if (!$quoted) {
                if (($i === $len) || ($hbody[0][$i] === ' ') || ($hbody[0][$i] === "\t")) {
                    if (is_null($afrom)) {
                        $lastWsp = $i;

                        continue;
                    } else {
                        $addr = substr($hbody[0], $afrom, $i - $afrom);
                        $semiPos = strpos($addr, ';');

                        if ($semiPos !== false) {
                            $addr = substr($addr, 0, $semiPos);
                            $i = $semiPos + 1;
                        }

                        if (strpos($addr, '>') !== false) {
                            throw new InvalidHeaderLineException('Invalid name-addr line, unmatched <> enclosure ending', Response::BAD_REQUEST);
                        }

                        $afrom = null;
                        $fetchParams = true;
                    }
                } else if ($hbody[0][$i] === ':') {
                    $afrom = $lastWsp + 1;

                    continue;
                } else if ($hbody[0][$i] === '"') {
                    $quoted = true;
                    $qfrom = $i;

                    continue;
                } else if ($hbody[0][$i] === '<') {
                    $next = $i + 1;
                    $end = strpos($hbody[0], '>', $next);

                    if ($end === false) {
                        throw new InvalidHeaderLineException('Invalid name-addr line, unmatched <> enclosure opening', Response::BAD_REQUEST);
                    }

                    $addr = trim(substr($hbody[0], $next, $end - $next));

                    if (strpos($addr, '<') !== false) {
                        throw new InvalidHeaderLineException('Invalid name-addr line, unmatched <> enclosure opening', Response::BAD_REQUEST);
                    }

                    if (!isset($ret->name[0])) {
                        $name = trim(substr($hbody[0], 0, $i));

                        if (isset($name[0])) {
                            $ret->name = $name;
                        }
                    }

                    $i = $end + 1;
                    $fetchParams = true;
                }

                if ($fetchParams) {
                    $remainder = $len - $i;

                    if ($remainder > 0) {
                        $params = explode(';', substr($hbody[0], $i, $remainder));

                        foreach ($params as $ord => $param) {
                            $param = trim($param);

                            if (!isset($param[0])) {
                                if ($ord) {
                                    throw new InvalidHeaderParameterException('Empty header parameters', Response::BAD_REQUEST);
                                } else {
                                    continue;
                                }
                            }

                            $p = explode('=', $param);
                            $p[0] = rtrim($p[0]);

                            if (!isset($p[0][0])) {
                                throw new InvalidHeaderParameterException('Empty header parameters', Response::BAD_REQUEST);
                            }

                            if ($p[0][0] === '>') {
                                throw new InvalidHeaderLineException('Invalid name-addr line, unmatched <> enclosure ending', Response::BAD_REQUEST);
                            }

                            $pv = isset($p[1]) ? trim($p[1]) : '';

                            if ($p[0] === 'tag') {
                                if (isset($ret->tag)) {
                                    throw new InvalidHeaderParameterException('Duplicate tag parameter', Response::BAD_REQUEST);
                                }

                                $ret->tag = $pv;
                            } else {
                                if (isset($ret->params[$p[0]])) {
                                    throw new InvalidHeaderParameterException('Duplicate header value parameter: ' . $p[0], Response::BAD_REQUEST);
                                }

                                $ret->params[$p[0]] = $pv;
                            }
                        }
                    }

                    break;
                }
            } else if ($i === $len) {
                throw new InvalidHeaderLineException('Invalid name-addr line, unmatched "" quote enclosure ending', Response::BAD_REQUEST);
            } else if($hbody[0][$i] === '\\') {
                $i++;
            } else if($hbody[0][$i] === '"') {
                $quoted = false;
                /** @psalm-suppress PossiblyNullOperand qfrom is always set if quoted === true */
                $ret->name = str_replace('\\\\', '\\', substr($hbody[0], $qfrom + 1, $i - $qfrom - 1));
                $qfrom = null;
            }
        }

        if (!is_string($addr)) {
            throw new InvalidHeaderLineException('Invalid name-addr line, missing addr-spec', Response::BAD_REQUEST);
        }

        $ret->uri = URI::parse($addr);

        return $ret;
    }

    /**
     * Name-addr header values renderer
     *
     * @param string $hname Header field name
     * @throws InvalidHeaderValueException
     * @return string
     */
    public function render(string $hname): string
    {
        if (!isset($this->uri)) {
            throw new InvalidHeaderValueException('Missing address part for name-addr header field');
        }

        $ret = "{$hname}: ";
        $addr = $this->uri->render();

        if (isset($this->name[0])) {
            $ret = "{$hname}: " . '"' . addcslashes($this->name, '\\') . '" ' . "<{$addr}>";
        } else {
            $ret = "{$hname}: <{$addr}>";
        }

        if (isset($this->tag)) {
            $ret .= ";tag={$this->tag}";
        }

        foreach ($this->params as $pk => $pv) {
            $ret .= ";{$pk}" . (!isset($pv[0]) ? '' : "={$pv}");
        }

        return $ret . "\r\n";
    }
}
