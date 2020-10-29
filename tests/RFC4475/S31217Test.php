<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidCSeqValue;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.17
 * 3.1.2.17.  Start Line and CSeq Method Mismatch
 */
class S31217Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('mismatch01');

        // Throws InvalidCSeqValue for bogus mismatched request methods
        $this->expectException(InvalidCSeqValue::class);
        Message::parse($pdu);
    }
}
