<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidMessageStartLineException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.8
 * 3.1.2.8.  Malformed SIP Request-URI (embedded LWS)
 */
class S31208Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('lwsruri');

        // Throws InvalidMessageStartLineException for malformed request URI
        $this->expectException(InvalidMessageStartLineException::class);
        Message::parse($pdu);
    }
}
