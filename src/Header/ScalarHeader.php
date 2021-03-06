<?php
/**
* RTCKit\SIP\ScalarHeader Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\Exception\InvalidDuplicateHeader;
use RTCKit\SIP\Exception\InvalidScalarValue;
use RTCKit\SIP\Exception\InvalidHeaderValue;

/**
* Scalar Header Class
*/
class ScalarHeader
{
    /** @var int Maximum allowed value (2^32 - 1) */
    public const MAX_VALUE = 4294967295;

    /** @var int Header field value */
    public int $value;

    final public function __construct() {}

    /**
     * Scalar header value parser
     *
     * @param list<string> $hbody Header body
     * @throws InvalidDuplicateHeader
     * @throws InvalidScalarValue
     * @return ScalarHeader
     */
    public static function parse(array $hbody): ScalarHeader
    {
        if (isset($hbody[1])) {
            throw new InvalidDuplicateHeader('Scalar header fields can only have one value', Response::BAD_REQUEST);
        }

        $val = (int) $hbody[0];

        if ($val > static::MAX_VALUE) {
            throw new InvalidScalarValue('Scalar header field value out of bounds, > ' . static::MAX_VALUE, Response::BAD_REQUEST);
        }

        if ($val < 0) {
            throw new InvalidScalarValue('Scalar header field value out of bounds (negative)', Response::BAD_REQUEST);
        }

        $ret = new static;
        $ret->value = $val;

        return $ret;
    }

    /**
     * Scalar header value renderer
     *
     * @param string $hname Header field name
     * @throws InvalidHeaderValue
     * @return string
     */
    public function render(string $hname): string
    {
        if (!isset($this->value)) {
            throw new InvalidHeaderValue('Missing scalar header value');
        }

        return "{$hname}: {$this->value}\r\n";
    }
}
