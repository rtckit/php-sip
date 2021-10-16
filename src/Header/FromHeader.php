<?php
/**
 * RTCKit\SIP\Header\FromHeader Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;

/**
 * From header class
 */
class FromHeader extends NameAddrHeader
{
    /**
     * From header value parser
     *
     * @param list<string> $hbody Header body
     * @throws InvalidHeaderParameterException
     * @return NameAddrHeader
     */
    public static function parse(array $hbody): NameAddrHeader
    {
        $ret = parent::parse($hbody);

        if (!isset($ret->tag[0])) {
            throw new InvalidHeaderParameterException('Missing/empty tag parameter in From header field value', Response::BAD_REQUEST);
        }

        return $ret;
    }
}
