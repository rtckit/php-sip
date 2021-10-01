<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidDuplicateHeaderException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.9
 * 3.3.9.  Multiple Content-Length Values
 */
class S33090Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('mcl01');

        // Throws InvalidDuplicateHeaderException for duplicate header fields
        $this->expectException(InvalidDuplicateHeaderException::class);
        Message::parse($pdu);
    }
}
