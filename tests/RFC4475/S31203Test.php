<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidScalarValueException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.3
 * 3.1.2.3.  Negative Content-Length
 */
class S31203Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('ncl');

        // Throws InvalidScalarValueException for length mismatch
        $this->expectException(InvalidScalarValueException::class);
        Message::parse($pdu);
    }
}
