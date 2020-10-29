<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidBodyLengthException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.2
 * 3.1.2.2.  Content Length Larger Than Message
 */
class S31202Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('clerr');

        // Throws InvalidBodyLengthException for length mismatch
        $this->expectException(InvalidBodyLengthException::class);
        Message::parse($pdu);
    }
}
