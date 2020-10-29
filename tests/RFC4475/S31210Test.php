<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidMessageStartLineException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.10
 * 3.1.2.10.  SP Characters at End of Request-Line
 */
class S31210Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('trws');

        // Throws InvalidMessageStartLineException for malformed request URI
        $this->expectException(InvalidMessageStartLineException::class);
        Message::parse($pdu);
    }
}
