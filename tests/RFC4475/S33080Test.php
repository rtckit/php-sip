<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidDuplicateHeader;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.8
 * 3.3.8.  Multiple Values in Single Value Required Fields
 */
class S33080Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('multi01');

        // Throws InvalidDuplicateHeader for duplicate header fields
        $this->expectException(InvalidDuplicateHeader::class);
        Message::parse($pdu);
    }
}
