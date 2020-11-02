<?php
/**
* RTCKit\SIP\ViaHeader Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Message;
use RTCKit\SIP\Response;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameter;
use RTCKit\SIP\Exception\InvalidHeaderValue;
use RTCKit\SIP\Exception\InvalidProtocolVersionException;

/**
* Via Header Class
*/
class ViaHeader
{
    /** @var list<ViaValue> Via value(s) */
    public array $values = [];

    final public function __construct() {}

    /**
     * Single value header field parser, with parameters
     *
     * @param list<string> $hbody Header body
     * @throws InvalidHeaderLineException
     * @throws InvalidHeaderParameter
     * @throws InvalidProtocolVersionException
     * @return ViaHeader
     */
    public static function parse(array $hbody): ViaHeader
    {
        $ret = new static;

        foreach ($hbody as $hline) {
            $hvalues = explode(',', $hline);

            foreach ($hvalues as $hvalue) {
                $via = trim($hvalue);
                $psplit = explode('/', $via);

                if (count($psplit) !== 3) {
                    throw new InvalidHeaderLineException('Invalid Via header', Response::BAD_REQUEST);
                }

                $val = new ViaValue;
                $val->protocol = rtrim($psplit[0]);

                if ($val->protocol !== Message::SIP_PROTOCOL_NAME) {
                    throw new InvalidProtocolVersionException('Unsupported SIP protocol in Via header: ' . $val->protocol, Response::VERSION_NOT_SUPPORTED);
                }

                $val->version = trim($psplit[1]);

                if ($val->version !== Message::SIP_VERSION_NUMBER) {
                    throw new InvalidProtocolVersionException('Unsupported SIP version number in Via header: ' . $val->version, Response::VERSION_NOT_SUPPORTED);
                }

                $vsplit = explode(' ', trim($psplit[2]), 2);

                if (count($vsplit) !== 2) {
                    throw new InvalidHeaderLineException('Invalid Via header', Response::BAD_REQUEST);
                }

                $val->transport = $vsplit[0];
                $vparams = explode(';', $vsplit[1]);
                $val->host = trim(array_shift($vparams));

                foreach ($vparams as $param) {
                    $p = explode('=', $param);
                    $p[0] = rtrim($p[0]);

                    if (!isset($p[0][0])) {
                        throw new InvalidHeaderParameter('Empty header parameters', Response::BAD_REQUEST);
                    }

                    $pv = isset($p[1]) ? trim($p[1]) : '';

                    if ($p[0] === 'branch') {
                        $val->branch = $pv;
                    } else {
                        $val->params[$p[0]] = $pv;
                    }
                }

                $ret->values[] = $val;
            }
        }

        return $ret;
    }

    /**
     * Via header field value renderer
     *
     * @param string $hname Header field name
     * @throws InvalidHeaderValue
     * @return string
     */
    public function render(string $hname): string
    {
        $ret = "{$hname}: ";
        $delim = '';

        foreach ($this->values as $key => $value) {
            if (!isset($value->protocol, $value->version, $value->transport, $value->host)) {
                throw new InvalidHeaderValue('Malformed Via header');
            }

            $ret .= "{$delim}{$value->protocol}/{$value->version}/{$value->transport} {$value->host}";

            if (isset($value->branch)) {
                $ret .= ";branch={$value->branch}";
            }

            foreach ($value->params as $pk => $pv) {
                $ret .= ';' . $pk . (!isset($pv[0]) ? '' : "={$pv}");
            }

            $delim = ', ';
        }

        return $ret . "\r\n";
    }
}
