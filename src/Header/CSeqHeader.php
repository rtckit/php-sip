<?php
/**
 * RTCKit\SIP\Header\CSeqHeader Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\Exception\InvalidDuplicateHeaderException;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;
use RTCKit\SIP\Exception\InvalidScalarValueException;

/**
 * CSeq header class
 */
class CSeqHeader
{
    /** @var int Sequence number */
    public int $sequence;

    /** @var string Original request method */
    public string $method;

    final public function __construct() {}

    /**
     * Sequence header value parser
     *
     * @param list<string> $hbody Header body
     * @throws InvalidDuplicateHeaderException
     * @throws InvalidHeaderLineException
     * @throws InvalidScalarValueException
     * @return CSeqHeader
     */
    public static function parse(array $hbody): CSeqHeader
    {
        if (isset($hbody[1])) {
            throw new InvalidDuplicateHeaderException('Cannot have more than one CSeq header', Response::BAD_REQUEST);
        }

        $cseq = trim($hbody[0]);
        $delimPos = strpos($cseq, ' ');

        if ($delimPos === false) {
            throw new InvalidHeaderLineException('Invalid CSeq header', Response::BAD_REQUEST);
        }

        $ret = new static;
        $ret->sequence = (int) substr($cseq, 0, $delimPos);

        if (($ret->sequence < 0) || ($ret->sequence > ScalarHeader::MAX_VALUE)) {
            throw new InvalidScalarValueException('CSeq sequence number out of bounds', Response::BAD_REQUEST);
        }

        $ret->method = ltrim(substr($cseq, $delimPos));

        return $ret;
    }

    /**
     * CSeq header value renderer
     *
     * @param string $hname Header field name
     * @throws InvalidHeaderValueException
     * @return string
     */
    public function render(string $hname): string
    {
        if (!isset($this->sequence, $this->method[0])) {
            throw new InvalidHeaderValueException('Missing Sequence/Method for CSeq header field value');
        }

        return "{$hname}: {$this->sequence} {$this->method}\r\n";
    }
}
