<?php
/**
 * RTCKit\SIP\Header\ScalarHeader Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\Exception\InvalidDuplicateHeaderException;
use RTCKit\SIP\Exception\InvalidScalarValueException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;

/**
 * Scalar header class
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
     * @throws InvalidDuplicateHeaderException
     * @throws InvalidScalarValueException
     * @return ScalarHeader
     */
    public static function parse(array $hbody): ScalarHeader
    {
        if (isset($hbody[1])) {
            throw new InvalidDuplicateHeaderException('Scalar header fields can only have one value', Response::BAD_REQUEST);
        }

        $val = (int) $hbody[0];

        if ($val > static::MAX_VALUE) {
            throw new InvalidScalarValueException('Scalar header field value out of bounds, > ' . static::MAX_VALUE, Response::BAD_REQUEST);
        }

        if ($val < 0) {
            throw new InvalidScalarValueException('Scalar header field value out of bounds (negative)', Response::BAD_REQUEST);
        }

        $ret = new static;
        $ret->value = $val;

        return $ret;
    }

    /**
     * Scalar header value renderer
     *
     * @param string $hname Header field name
     * @throws InvalidHeaderValueException
     * @return string
     */
    public function render(string $hname): string
    {
        if (!isset($this->value)) {
            throw new InvalidHeaderValueException('Missing scalar header value');
        }

        return "{$hname}: {$this->value}\r\n";
    }
}
