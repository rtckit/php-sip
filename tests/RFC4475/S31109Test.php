<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.1.9
 * 3.1.1.9.  Semicolon-Separated Parameters in URI User Part
 */
class S31109Test extends RFC4475Case
{
    public function testShouldParseProperly()
    {
        $pdu = $this->loadFixture('semiuri');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* The Request-URI will parse so that the user part is "user;par=u@example.net" */
        $this->assertEquals('user;par=u@example.net', $msg->uri->user);
    }
}
