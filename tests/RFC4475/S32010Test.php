<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.2.1
 * 3.2.1.  Missing Transaction Identifier
 * Not in direct scope
 */
class S32010Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('badbranch');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* Make sure via branch tag is readable; application must be able to tell
           only the prefix was sent over, no identifier. */
        $this->assertEquals('z9hG4bK', $msg->via->values[0]->branch);
    }
}
