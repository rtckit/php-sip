<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.15
 * 3.1.2.15.  Non-token Characters in Display Name
 *
 * This implementation takes the liberal route and attempts
 * to infer any missing quotes around the display name.
 */
class S31215Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('baddn');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        $this->assertEquals('sip', $msg->to->uri->scheme);
        $this->assertEquals('t.watson', $msg->to->uri->user);
        $this->assertEquals('example.org', $msg->to->uri->host);
        $this->assertEquals('Watson, Thomas', $msg->to->name);
        $this->assertEquals('sip', $msg->from->uri->scheme);
        $this->assertEquals('a.g.bell', $msg->from->uri->user);
        $this->assertEquals('example.com', $msg->from->uri->host);
        $this->assertEquals('Bell, Alexander', $msg->from->name);
        $this->assertEquals('43', $msg->from->tag);
    }
}
