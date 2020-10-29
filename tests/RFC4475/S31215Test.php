<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.15
 * 3.1.2.15.  Non-token Characters in Display Name
 * 2D! Liberal for now
 */
class S31215Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('baddn');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        $this->assertEquals('sip:t.watson@example.org', $msg->to->addr);
        $this->assertEquals('Watson, Thomas', $msg->to->name);
        $this->assertEquals('sip:a.g.bell@example.com', $msg->from->addr);
        $this->assertEquals('Bell, Alexander', $msg->from->name);
        $this->assertEquals('43', $msg->from->tag);
    }
}
