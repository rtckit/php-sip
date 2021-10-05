<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidDuplicateHeaderException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.8
 * 3.3.8.  Multiple Values in Single Value Required Fields
 */
class S33080Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('multi01');

        // Throws InvalidDuplicateHeaderException for duplicate header fields
        $this->expectException(InvalidDuplicateHeaderException::class);
        Message::parse($pdu);
    }
}
