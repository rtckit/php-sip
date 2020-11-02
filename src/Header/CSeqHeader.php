<?php
/**
* RTCKit\SIP\CSeqHeader Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Response;
use RTCKit\SIP\Exception\InvalidDuplicateHeader;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderValue;
use RTCKit\SIP\Exception\InvalidScalarValue;

/**
* CSeq Header Class
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
     * @param array<string> $hbody Header body
     * @throws InvalidDuplicateHeader
     * @throws InvalidHeaderLineException
     * @throws InvalidScalarValue
     * @return CSeqHeader
     */
    public static function parse(array $hbody): CSeqHeader
    {
        if (isset($hbody[1])) {
            throw new InvalidDuplicateHeader('Cannot have more than one CSeq header', Response::BAD_REQUEST);
        }

        $cseq = trim($hbody[0]);
        $delimPos = strpos($cseq, ' ');

        if ($delimPos === false) {
            throw new InvalidHeaderLineException('Invalid CSeq header', Response::BAD_REQUEST);
        }

        $ret = new static;
        $ret->sequence = (int) substr($cseq, 0, $delimPos);

        if (($ret->sequence < 0) || ($ret->sequence > ScalarHeader::MAX_VALUE)) {
            throw new InvalidScalarValue('CSeq sequence number out of bounds', Response::BAD_REQUEST);
        }

        $ret->method = ltrim(substr($cseq, $delimPos));

        return $ret;
    }

    /**
     * CSeq header value renderer
     *
     * @param string $hname Header field name
     * @throws InvalidHeaderValue
     * @return string
     */
    public function render(string $hname): string
    {
        if (!isset($this->sequence, $this->method[0])) {
            throw new InvalidHeaderValue('Missing Sequence/Method for CSeq header field value');
        }

        return "{$hname}: {$this->sequence} {$this->method}\r\n";
    }
}
