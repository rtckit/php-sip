<?php
/**
* RTCKit\SIP\Header\FromHeader Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\Exception\InvalidHeaderParameter;

/**
* From Header Class
*/
class FromHeader extends NameAddrHeader
{
    /**
     * From header value parser
     *
     * @param list<string> $hbody Header body
     * @throws InvalidHeaderParameter
     * @return NameAddrHeader
     */
    public static function parse(array $hbody): NameAddrHeader
    {
        $ret = parent::parse($hbody);

        if (!isset($ret->tag[0])) {
            throw new InvalidHeaderParameter('Missing/empty tag parameter in From header field value', Response::BAD_REQUEST);
        }

        return $ret;
    }
}
