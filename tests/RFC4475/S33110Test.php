<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.11
 * 3.3.11.  Max-Forwards of Zero
 * Not in direct scope
 */
class S33110Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('zeromf');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* Make sure the application has enough visibility to read the Max-Forwards header value */
        $this->assertNotNull($msg->maxForwards->value);
        $this->assertEquals(0, $msg->maxForwards->value);
    }
}
