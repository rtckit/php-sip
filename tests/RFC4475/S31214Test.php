<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.14
 * 3.1.2.14.  Spaces within addr-spec
 * 2D! Liberal for now
 */
class S31214Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('badaspec');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        $this->assertEquals(
            'sip:t.watson@example.org',
            $msg->to->addr
        );
    }
}
