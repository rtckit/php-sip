<?php
/**
 * RTCKit\SIP\Header\RAckHeader Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\Exception\InvalidDuplicateHeaderException;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;
use RTCKit\SIP\Exception\InvalidScalarValueException;

/**
 * RAck header class
 *
 * https://datatracker.ietf.org/doc/html/rfc3262#section-7.2
 */
class RAckHeader
{
    /** @var int RSeq sequence number */
    public int $rSequence;

    /** @var int CSeq sequence number */
    public int $cSequence;

    /** @var string Original request method */
    public string $method;

    final public function __construct() {}

    /**
     * RAck header value parser
     *
     * @param list<string> $hbody Header body
     * @throws InvalidDuplicateHeaderException
     * @throws InvalidHeaderLineException
     * @throws InvalidScalarValueException
     * @return RAckHeader
     */
    public static function parse(array $hbody): RAckHeader
    {
        if (isset($hbody[1])) {
            throw new InvalidDuplicateHeaderException('Cannot have more than one RAck header', Response::BAD_REQUEST);
        }

        $rack = preg_split('/\s+/', trim($hbody[0]), -1, PREG_SPLIT_NO_EMPTY);

        if (!is_array($rack) || (count($rack) != 3)) {
            throw new InvalidHeaderLineException('Invalid RAck header', Response::BAD_REQUEST);
        }

        $ret = new static;
        $ret->rSequence = (int) $rack[0];

        if (($ret->rSequence < 0) || ($ret->rSequence > ScalarHeader::MAX_VALUE)) {
            throw new InvalidScalarValueException('RAck provisional sequence number out of bounds', Response::BAD_REQUEST);
        }

        $ret->cSequence = (int) $rack[1];

        if (($ret->cSequence < 0) || ($ret->cSequence > ScalarHeader::MAX_VALUE)) {
            throw new InvalidScalarValueException('RAck sequence number out of bounds', Response::BAD_REQUEST);
        }

        $ret->method = $rack[2];

        return $ret;
    }

    /**
     * RAck header value renderer
     *
     * @param string $hname Header field name
     * @throws InvalidHeaderValueException
     * @return string
     */
    public function render(string $hname): string
    {
        if (!isset($this->rSequence, $this->cSequence, $this->method[0])) {
            throw new InvalidHeaderValueException('Missing Sequence(s)/Method for RAck header field value');
        }

        return "{$hname}: {$this->rSequence} {$this->cSequence} {$this->method}\r\n";
    }
}
