<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidCSeqValueException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.17
 * 3.1.2.17.  Start Line and CSeq Method Mismatch
 */
class S31217Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('mismatch01');

        // Throws InvalidCSeqValueException for bogus mismatched request methods
        $this->expectException(InvalidCSeqValueException::class);
        Message::parse($pdu);
    }
}
