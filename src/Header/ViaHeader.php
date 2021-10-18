<?php
/**
 * RTCKit\SIP\Header\ViaHeader Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Message;
use RTCKit\SIP\Response;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;
use RTCKit\SIP\Exception\InvalidProtocolVersionException;

/**
 * Via header class
 */
class ViaHeader
{
    /** @var list<ViaValue> Via value(s) */
    public array $values = [];

    final public function __construct() {}

    /**
     * Via header field value parser
     *
     * @param list<string> $hbody Header body
     * @throws InvalidHeaderLineException
     * @throws InvalidHeaderParameterException
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
                    $p[0] = trim($p[0]);

                    if (!isset($p[0][0])) {
                        throw new InvalidHeaderParameterException('Empty header parameters', Response::BAD_REQUEST);
                    }

                    $pv = isset($p[1]) ? trim($p[1]) : '';

                    switch ($p[0]) {
                        case 'branch':
                            $val->branch = $pv;
                            break;

                        case 'received':
                            if (!filter_var($pv, FILTER_VALIDATE_IP)) {
                                throw new InvalidHeaderParameterException('Invalid Via header received parameter', Response::BAD_REQUEST);
                            }

                            $val->received = $pv;
                            break;

                        case 'rport':
                            if (!strlen($pv)) {
                                $val->rport = 0;
                            } else if (!ctype_digit($pv)) {
                                throw new InvalidHeaderParameterException('Invalid Via header rport parameter', Response::BAD_REQUEST);
                            }

                            $rport = (int)$pv;

                            if ($rport > 65535) {
                                throw new InvalidHeaderParameterException('Invalid Via header rport parameter', Response::BAD_REQUEST);
                            }

                            $val->rport = $rport;
                            break;

                        default:
                            $val->params[$p[0]] = $pv;
                            break;
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
     * @throws InvalidHeaderValueException
     * @return string
     */
    public function render(string $hname): string
    {
        $ret = "{$hname}: ";
        $delim = '';

        foreach ($this->values as $key => $value) {
            if (!isset($value->protocol, $value->version, $value->transport, $value->host)) {
                throw new InvalidHeaderValueException('Malformed Via header');
            }

            $ret .= "{$delim}{$value->protocol}/{$value->version}/{$value->transport} {$value->host}";

            if (isset($value->branch)) {
                $ret .= ";branch={$value->branch}";
            }

            if (isset($value->received)) {
                $ret .= ";received={$value->received}";
            }

            if (isset($value->rport)) {
                if (!$value->rport) {
                    $ret .= ";rport";
                } else {
                    $ret .= ";rport={$value->rport}";
                }
            }

            foreach ($value->params as $pk => $pv) {
                $ret .= ';' . $pk . (!isset($pv[0]) ? '' : "={$pv}");
            }

            $delim = ', ';
        }

        return $ret . "\r\n";
    }
}
