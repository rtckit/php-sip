<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidProtocolVersionException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.16
 * 3.1.2.16.  Unknown Protocol Version
 */
class S31216Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('badvers');

        // Throws InvalidProtocolVersionException for bogus version
        $this->expectException(InvalidProtocolVersionException::class);
        Message::parse($pdu);
    }
}
