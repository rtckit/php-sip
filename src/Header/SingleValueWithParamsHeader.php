<?php
/**
* RTCKit\SIP\Header\SingleValueWithParamsHeader Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\Exception\InvalidDuplicateHeader;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameter;
use RTCKit\SIP\Exception\InvalidHeaderValue;

/**
* SingleValueWithParamsHeader Class
*/
class SingleValueWithParamsHeader
{
    /** @var string Header field value */
    public string $value;

    /** @var array<string, string> Parameters */
    public array $params = [];

    final public function __construct() {}

    /**
     * Single value header field parser, with parameters
     *
     * @param array<string> $hbody Header body
     * @throws InvalidDuplicateHeader
     * @throws InvalidHeaderLineException
     * @throws InvalidHeaderParameter
     * @return SingleValueWithParamsHeader
     */
    public static function parse(array $hbody): SingleValueWithParamsHeader
    {
        if (isset($hbody[1])) {
            throw new InvalidDuplicateHeader('Cannot have multiple single value headers', Response::BAD_REQUEST);
        }

        $tok = strtok(trim($hbody[0]), ';');

        if ($tok === false) {
            throw new InvalidHeaderLineException('Empty header value', Response::BAD_REQUEST);
        }

        $ret = new static;
        $ret->value = $tok;

        while (($tok = strtok(';')) !== false) {
            $p = explode('=', $tok);
            $p[0] = trim($p[0]);

            if (!isset($p[0][0])) {
                throw new InvalidHeaderParameter('Empty header parameters', Response::BAD_REQUEST);
            }

            $ret->params[$p[0]] = isset($p[1]) ? trim($p[1]) : '';
        }

        return $ret;
    }

    /**
     * Single value header renderer, with optional parameters
     *
     * @param string $hname Header field name
     * @throws InvalidHeaderValue
     * @return string
     */
    public function render(string $hname): string
    {
        if (!isset($this->value[0])) {
            throw new InvalidHeaderValue('Missing header field value for header: ' . $hname);
        }

        $ret = "{$hname}: {$this->value}";

        foreach ($this->params as $pk => $pv) {
            if (!isset($pk[0]) && !is_numeric($pk)) {
                throw new InvalidHeaderValue('Malformed header, invalid parameter key for header ' . $hname);
            }

            $ret .= ';' . $pk . (!isset($pv[0]) ? '' : "={$pv}");
        }

        $ret .= "\r\n";

        return $ret;
    }
}
