<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.1.8
 * 3.1.1.8.  Extra Trailing Octets in a UDP Datagram
 */
class S31108Test extends RFC4475Case
{
    public function testShouldParseProperly()
    {
        $pdu = $this->loadFixture('dblreq');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* A SIP element receiving this datagram would handle the REGISTER
           request normally and ignore the extra bits that look like an INVITE
           request.  If the element is a proxy choosing to forward the REGISTER,
           the INVITE octets would not appear in the forwarded request. */
        $this->assertEquals('REGISTER', $msg->method);
        $this->assertEquals(0, $msg->contentLength->value);
        $this->assertEquals('', $msg->body);
    }
}
