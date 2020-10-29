<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.13
 * 3.1.2.13.  Failure to Enclose name-addr URI in <>
 * 2D! Liberal for now
 */
class S31213Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('regbadct');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        $this->assertEquals(
            'sip:user@example.com?Route=%3Csip:sip.example.com%3E',
            $msg->contact->values[0]->addr
        );
    }
}
