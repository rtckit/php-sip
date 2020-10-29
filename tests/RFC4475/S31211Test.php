<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.11
 * 3.1.2.11.  Escaped Headers in SIP Request-URI
 * 2D! Liberal for now as we don't further parse the request URI (yet)
 */
class S31211Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('escruri');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        $this->assertEquals('sip:user@example.com?Route=%3Csip:example.com%3E', $msg->uri);
    }
}
