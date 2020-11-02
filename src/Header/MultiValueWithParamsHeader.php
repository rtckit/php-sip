<?php
/**
* RTCKit\SIP\Header\MultiValueWithParamsHeader Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameter;
use RTCKit\SIP\Exception\InvalidHeaderValue;

/**
* MultiValueWithParamsHeader Class
*/
class MultiValueWithParamsHeader
{
    /** @var list<ValueWithParams> Header value(s) */
    public array $values = [];

    final public function __construct() {}

    /**
     * Multiple value header parser, with optional parameters
     *
     * @param array<string> $hbody Header body
     * @throws InvalidHeaderLineException
     * @throws InvalidHeaderParameter
     * @return MultiValueWithParamsHeader
     */
    public static function parse(array $hbody): MultiValueWithParamsHeader
    {
        $ret = new static;

        foreach ($hbody as $hline) {
            $hvalues = explode(',', $hline);

            foreach ($hvalues as $hvalue) {
                $tok = strtok(trim($hvalue), ';');

                if ($tok === false) {
                    throw new InvalidHeaderLineException('Empty header value', Response::BAD_REQUEST);
                }

                $val = new ValueWithParams;
                $val->value = $tok;

                while (($tok = strtok(';')) !== false) {
                    $p = explode('=', $tok);
                    $p[0] = trim($p[0]);

                    if (!isset($p[0][0])) {
                        throw new InvalidHeaderParameter('Empty header parameters', Response::BAD_REQUEST);
                    }

                    $val->params[$p[0]] = isset($p[1]) ? trim($p[1]) : '';
                }

                $ret->values[] = $val;
            }
        }

        return $ret;
    }

    /**
     * Multiple value header renderer, with optional parameters
     *
     * @param string $hname Header field name
     * @throws InvalidHeaderValue
     * @return string
     */
    public function render(string $hname): string
    {
        $ret = "{$hname}: ";

        foreach ($this->values as $key => $value) {
            if (!isset($value->value)) {
                throw new InvalidHeaderValue('Malformed header, missing value');
            }

            if ($key) {
                $ret .= ', ';
            }

            $ret .= $value->value;

            foreach ($value->params as $pk => $pv) {
                if (!isset($pk[0])) {
                    throw new InvalidHeaderValue('Malformed header, invalid parameter key for header ' . $hname);
                }

                $ret .= ';' . $pk . (!isset($pv[0]) ? '' : "={$pv}");
            }
        }

        $ret .= "\r\n";

        return $ret;
    }
}
