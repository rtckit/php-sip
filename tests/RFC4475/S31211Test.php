<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.11
 * 3.1.2.11.  Escaped Headers in SIP Request-URI
 *
 * As an UAS, this implementation will take the liberal route and silently drop
 * any headers found in the Request-URI.
 */
class S31211Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('escruri');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);
        $this->assertEquals([], $msg->uri->headers);
    }
}
