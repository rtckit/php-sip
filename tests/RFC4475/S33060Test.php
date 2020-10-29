<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.6
 * 3.3.6.  Unknown Content-Type
 * Not in direct scope
 */
class S33060Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('invut');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* Make sure the application has enough visibility read the content's type */
        $this->assertEquals('application/unknownformat', $msg->contentType->value);
    }
}
