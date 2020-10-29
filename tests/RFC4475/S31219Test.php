<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidStatusCodeException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.19
 * 3.1.2.19.  Overlarge Response Code
 */
class S31219Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('bigcode');

        // Throws InvalidStatusCodeException for bogus response codes
        $this->expectException(InvalidStatusCodeException::class);
        Message::parse($pdu);
    }
}
