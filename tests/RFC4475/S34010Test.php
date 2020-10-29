<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidHeaderParameter;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.4.1
 * 3.4.1.  INVITE with RFC 2543 Syntax
 */
class S34010Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('inv2543');

        // Throws InvalidHeaderParameter for missing From header value tag parameter
        $this->expectException(InvalidHeaderParameter::class);
        Message::parse($pdu);
    }
}
