<?php
/**
 * RTCKit\SIP\Header\ContactHeader Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\URI;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;

/**
 * Contact header class
 */
class ContactHeader
{
    /** @var list<ContactValue> Contact value(s) */
    public array $values = [];

    /** @var bool Whether value was '*' https://tools.ietf.org/html/rfc3261#section-7.3 */
    public bool $wildcard = false;

    final public function __construct() {}

    /**
     * Contact header value parser
     *
     * @param list<string> $hbody Header body
     * @throws InvalidHeaderLineException
     * @throws InvalidHeaderParameterException
     * @return ContactHeader
     */
    public static function parse(array $hbody): ContactHeader
    {
        $ret = new static;

        $input = $hbody;
        $hbody = [];

        foreach ($input as $hline) {
            $segments = explode(',', $hline);
            $buffer = $comma = '';

            foreach ($segments as $segment) {
                $buffer .= $comma . $segment;

                if (!(substr_count($segment, '"') % 2) && (substr_count($segment, '<') === substr_count($segment, '>'))) {
                    $hbody[] = $buffer;
                    $buffer = $comma = '';

                    continue;
                }

                $comma = ',';
            }

            if (isset($buffer[0])) {
                $hbody[] = $buffer;
            }
        }

        foreach ($hbody as $hline) {
            $val = new ContactValue;

            $len = strlen($hline);
            $fetchParams = false;
            $lastWsp = -1;
            $quoted = false;
            $qfrom = null;
            $afrom = null;
            $base = 0;
            $addr = false;

            for ($i = 0; $i <= $len; $i++) {
                if (!$quoted) {
                    if (($i === $len) || ($hline[$i] === ' ') || ($hline[$i] === "\t")) {
                        if (is_null($afrom)) {
                            $lastWsp = $i;

                            continue;
                        } else {
                            $addr = substr($hline, $afrom, $i - $afrom);
                            $semiPos = strpos($addr, ';');

                            if ($semiPos !== false) {
                                $addr = substr($addr, 0, $semiPos);
                                $i = $semiPos + 1;
                                $fetchParams = true;
                            }

                            if (strpos($addr, '>') !== false) {
                                throw new InvalidHeaderLineException('Invalid contact line, unmatched <> enclosure ending', Response::BAD_REQUEST);
                            }

                            $afrom = null;
                        }
                    } else if ($hline[$i] === '*') {
                        if (($lastWsp === $i - 1) && !isset($ret->values[0]) && !isset($hbody[1]) && !isset($hline[$i + 1])) {
                            $ret->wildcard = true;

                            return $ret;
                        } else {
                            throw new InvalidHeaderLineException('Improper use of * wildcard in Contact header field value', Response::BAD_REQUEST);
                        }
                    } else if ($hline[$i] === ':') {
                        $afrom = $lastWsp + 1;

                        continue;
                    } else if ($hline[$i] === '"') {
                        $quoted = true;
                        $qfrom = $i;

                        continue;
                    } else if ($hline[$i] === '<') {
                        $next = $i + 1;
                        $end = strpos($hline, '>', $next);

                        if ($end === false) {
                            throw new InvalidHeaderLineException('Invalid contact line, unmatched <> enclosure opening', Response::BAD_REQUEST);
                        }

                        $addr = trim(substr($hline, $next, $end - $next));

                        if (strpos($addr, '<') !== false) {
                            throw new InvalidHeaderLineException('Invalid contact line, unmatched <> enclosure opening', Response::BAD_REQUEST);
                        }

                        if (!isset($val->name[0])) {
                            $name = trim(substr($hline, $base, $i - $base));

                            if (isset($name[0])) {
                                $val->name = $name;
                            }
                        }

                        $i = $end + 1;
                        $fetchParams = true;
                    } /* else if ($hline[$i] === '>') {
                        throw new InvalidHeaderLineException('Invalid contact line, unmatched <> enclosure ending', Response::BAD_REQUEST);
                    } */

                    if ($fetchParams) {
                        $commaPos = ($i >= $len) ? false : strpos($hline, ',', $i);
                        $remainder = (($commaPos === false) ? $len : $commaPos) - $i;

                        if ($commaPos !== false) {
                            $base = $commaPos + 1;
                        }

                        if ($remainder > 0) {
                            $params = explode(';', substr($hline, $i, $remainder));

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
                                    throw new InvalidHeaderLineException('Invalid contact line, unmatched <> enclosure ending', Response::BAD_REQUEST);
                                }

                                $pv = isset($p[1]) ? trim($p[1]) : '';

                                if ($p[0] === 'q') {
                                    if (isset($val->q)) {
                                        throw new InvalidHeaderParameterException('Duplicate q Contact header value parameter', Response::BAD_REQUEST);
                                    }

                                    $val->q = (float) $pv;
                                } else if ($p[0] === 'expires') {
                                    if (isset($val->expires)) {
                                        throw new InvalidHeaderParameterException('Duplicate expires Contact header value parameter', Response::BAD_REQUEST);
                                    }

                                    $val->expires = (int) $pv;
                                } else {
                                    if (isset($val->params[$p[0]])) {
                                        throw new InvalidHeaderParameterException('Duplicate header value parameter: ' . $p[0], Response::BAD_REQUEST);
                                    }

                                    $val->params[$p[0]] = $pv;
                                }
                            }
                        }

                        if (is_string($addr)) {
                            $val->uri = URI::parse($addr);
                            $ret->values[] = $val;
                            $addr = null;
                            $val = new ContactValue;
                        }

                        if (($commaPos === false) || ($remainder <= 0)) {
                            break;
                        } else {
                            $fetchParams = false;
                            $i = $commaPos + 1;
                        }
                    }
                } else if ($i === $len) {
                    throw new InvalidHeaderLineException('Invalid contact line, unmatched "" quote enclosure ending', Response::BAD_REQUEST);
                } else if($hline[$i] === '\\') {
                    $i++;
                } else if($hline[$i] === '"') {
                    $quoted = false;
                    /** @psalm-suppress PossiblyNullOperand qfrom is always set if fetchParams === true */
                    $val->name = str_replace('\\\\', '\\', substr($hline, $qfrom + 1, $i - $qfrom - 1));
                    $qfrom = null;
                }
            }

            if (is_string($addr)) {
                $val->uri = URI::parse($addr);
                $ret->values[] = $val;
            }
        }

        return $ret;
    }

    /**
     * Contact header values renderer
     *
     * @param string $hname Header field name
     * @throws InvalidHeaderValueException
     * @return string
     */
    public function render(string $hname): string
    {
        if ($this->wildcard) {
            return "{$hname}: *\r\n";
        }

        if (!isset($this->values[0])) {
            throw new InvalidHeaderValueException('Missing Contact header values');
        }

        $ret = "{$hname}: ";
        $delim = '';

        foreach ($this->values as $value) {
            if (!isset($value->uri)) {
                throw new InvalidHeaderValueException('Missing address part for contact header field value');
            }

            $addr = $value->uri->render();

            if (isset($value->name[0])) {
                $ret .= $delim . '"' . addcslashes($value->name, "\x5c") . '" ' . "<{$addr}>";
            } else {
                $ret .= "{$delim}<{$addr}>";
            }

            if (isset($value->q)) {
                $ret .= ";q={$value->q}";
            }

            if (isset($value->expires)) {
                $ret .= ";expires={$value->expires}";
            }

            foreach ($value->params as $pk => $pv) {
                $ret .= ";{$pk}" . (!isset($pv[0]) ? '' : "={$pv}");
            }

            $delim = ', ';
        }

        return $ret . "\r\n";
    }
}
